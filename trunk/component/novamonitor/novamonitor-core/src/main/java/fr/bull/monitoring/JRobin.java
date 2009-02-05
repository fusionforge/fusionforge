/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 *
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
package fr.bull.monitoring;

import java.awt.Color;
import java.io.File;
import java.io.IOException;
import java.lang.reflect.Field;
import java.security.AccessController;
import java.security.PrivilegedAction;
import java.text.DateFormat;
import java.util.Date;
import java.util.Timer;

import org.jrobin.core.RrdDb;
import org.jrobin.core.RrdDbPool;
import org.jrobin.core.RrdDef;
import org.jrobin.core.RrdException;
import org.jrobin.core.RrdNioBackend;
import org.jrobin.core.Sample;
import org.jrobin.core.Util;
import org.jrobin.graph.RrdGraph;
import org.jrobin.graph.RrdGraphDef;

/**
 * Stockage RRD et graphiques statistiques.
 * Cette classe utilise JRobin (http://www.jrobin.org/index.php/Main_Page)
 * qui est une librairie Java opensource (LGPL) similaire à RRDTool (http://oss.oetiker.ch/rrdtool/).
 * L'API et le tutorial JRobin sont à http://oldwww.jrobin.org/api/index.html
 * @author evernat
 */
final class JRobin {
	private static final int HOUR = 60 * 60;
	private static final int DAY = 24 * HOUR;

	// pool of open RRD files
	private final RrdDbPool rrdPool = RrdDbPool.getInstance();
	private final String application;
	private final String name;
	private final String rrdFileName;
	private final String label;

	private JRobin(String application, String name, File rrdFile, int step, String label)
			throws RrdException, IOException {
		super();
		assert application != null;
		assert name != null;
		assert rrdFile != null;
		assert step > 0;
		assert label != null;

		this.application = application;
		this.name = name;
		this.rrdFileName = rrdFile.getPath();
		this.label = label;

		init(step);
	}

	static void stop() throws IOException {
		// cette méthode doit être appelée pour arrêter jrobin et en particulier le timer static dans RrdNioBackend
		// (note : dans jrobin 1.5.9, il n'y a plus de shutdown hook comme il y avait avant dans RrdNioBackend)
		try {
			// on accède à ce timer par réflexion pour l'arrêter faute d'autre moyen
			final Field field = RrdNioBackend.class.getDeclaredField("fileSyncTimer");
			setFieldAccessible(field);
			// null car ce field est static
			final Timer fileSyncTimer = (Timer) field.get(null);
			fileSyncTimer.cancel();
		} catch (final NoSuchFieldException e) {
			throw createIOException(e);
		} catch (final IllegalAccessException e) {
			throw createIOException(e);
		}
	}

	@SuppressWarnings("all")
	private static void setFieldAccessible(final Field field) {
		AccessController.doPrivileged(new PrivilegedAction() { // pour findbugs
					/** {@inheritDoc} */
					public Object run() {
						field.setAccessible(true);
						return null;
					}
				});
	}

	static JRobin createInstance(String application, String name, String label) throws IOException {
		final String dir = Parameters.getStorageDirectory(application);
		final File rrdFile = new File(dir + '/' + name + ".rrd");
		final int step = Parameters.getResolutionSeconds();
		try {
			return new JRobin(application, name, rrdFile, step, label);
		} catch (final RrdException e) {
			throw createIOException(e);
		}
	}

	private void init(int step) throws IOException, RrdException {
		final File rrdFile = new File(rrdFileName);
		final File rrdDirectory = rrdFile.getParentFile();
		if (!rrdDirectory.mkdirs() && !rrdDirectory.exists()) {
			throw new IOException("Monitoring directory can't be created: "
					+ rrdDirectory.getPath());
		}
		if (!rrdFile.exists()) {
			// create RRD file since it does not exist
			final RrdDef rrdDef = new RrdDef(rrdFileName, step);
			// "startTime" décalé de "step" pour éviter que addValue appelée juste
			// après ne lance l'exception suivante la première fois
			// "Bad sample timestamp x. Last update time was x, at least one second step is required"
			rrdDef.setStartTime(Util.getTime() - step);
			// single gauge datasource
			final String dsType = "GAUGE";
			// max time before "unknown value"
			final int heartbeat = step * 2;
			rrdDef.addDatasource(getDataSourceName(), dsType, heartbeat, 0, Double.NaN);
			// several archives
			final String average = "AVERAGE";
			final String max = "MAX";
			// 1 jour
			rrdDef.addArchive(average, 0.25, 1, DAY / step);
			rrdDef.addArchive(max, 0.25, 1, DAY / step);
			// 1 semaine
			rrdDef.addArchive(average, 0.25, HOUR / step, 7 * 24);
			rrdDef.addArchive(max, 0.25, HOUR / step, 7 * 24);
			// 1 mois
			rrdDef.addArchive(average, 0.25, 6 * HOUR / step, 31 * 4);
			rrdDef.addArchive(max, 0.25, 6 * HOUR / step, 31 * 4);
			// 2 ans (1 an pour période "1 an" et 2 ans pour période "tout")
			rrdDef.addArchive(average, 0.25, 8 * 6 * HOUR / step, 2 * 12 * 15);
			rrdDef.addArchive(max, 0.25, 8 * 6 * HOUR / step, 2 * 12 * 15);
			// create RRD file in the pool
			final RrdDb rrdDb = rrdPool.requestRrdDb(rrdDef);
			rrdPool.release(rrdDb);
		}
	}

	byte[] graph(String period, int width, int height) throws IOException {
		try {
			return graph(Period.valueOfIgnoreCase(period), width, height);
		} catch (final RrdException e) {
			throw createIOException(e);
		}
	}

	private byte[] graph(Period period, int width, int height) throws IOException, RrdException {
		// Rq : il pourrait être envisagé de récupérer les données dans les fichiers rrd ou autre stockage
		// puis de faire des courbes en sparklines html (sauvegardées dans la page html)
		// ou avec http://code.google.com/apis/chart/types.html#sparkline ou jfreechart

		// create common part of graph definition
		final RrdGraphDef graphDef = new RrdGraphDef();
		initGraphSource(graphDef);

		initGraphPeriodAndSize(period, width, height, graphDef);

		graphDef.setImageFormat("png");
		graphDef.setFilename("-");
		// il faut utiliser le pool pour les performances
		// et pour éviter des erreurs d'accès concurrents sur les fichiers
		// entre différentes générations de graphs et aussi avec l'écriture des données
		graphDef.setPoolUsed(true);
		return new RrdGraph(graphDef).getRrdGraphInfo().getBytes();
	}

	private void initGraphPeriodAndSize(Period period, int width, int height, RrdGraphDef graphDef) {
		// ending timestamp is the current timestamp
		// starting timestamp will be adjusted for each graph
		final long endTime = Util.getTime();
		final long startTime = endTime - period.getDurationSeconds();
		final String titleStart = label + " - " + period.getLabel();
		final String titleEnd;
		if (width > 400) {
			titleEnd = " - " + DateFormat.getDateInstance(DateFormat.SHORT).format(new Date())
					+ " sur " + getApplication();
		} else {
			titleEnd = "";
		}
		graphDef.setStartTime(startTime);
		graphDef.setEndTime(endTime);
		graphDef.setTitle(titleStart + titleEnd);
		// rq : la largeur et la hauteur de l'image sont plus grandes que celles fournies
		// car jrobin ajoute la largeur et la hauteur des textes et autres
		graphDef.setWidth(width);
		graphDef.setHeight(height);
		if (width <= 100) {
			graphDef.setNoLegend(true);
			graphDef.setUnitsLength(0);
			graphDef.setShowSignature(false);
			graphDef.setTitle(null);
		}
	}

	private void initGraphSource(RrdGraphDef graphDef) {
		final String average = "average";
		final String max = "max";
		final String dataSourceName = getDataSourceName();
		graphDef.datasource(average, rrdFileName, dataSourceName, "AVERAGE");
		graphDef.datasource(max, rrdFileName, dataSourceName, "MAX");
		graphDef.setMinValue(0);
		// si on avait la moyenne globale/glissante des valeurs et l'écart type
		// on pourrait mettre vert si < moyenne + 1 écart type puis orange puis rouge si > moyenne + 2 écarts types
		// ou bien selon paramètres de plages de couleurs par graphe
		graphDef.area(average, Color.GREEN, "Moyenne");
		graphDef.line(max, Color.BLUE, "Maximum");
		graphDef.gprint(average, "AVERAGE", "Moyenne: %9.0f %S\\r");
		//graphDef.gprint(average, "MIN", "Minimum: %9.0f %S\\r");
		graphDef.gprint(max, "MAX", "Maximum: %9.0f %S\\r");
		// graphDef.comment("JRobin :: RRDTool Choice for the Java World");
	}

	void addValue(double value) throws IOException {
		try {
			// request RRD database reference from the pool
			final RrdDb rrdDb = rrdPool.requestRrdDb(rrdFileName);
			try {
				// create sample with the current timestamp
				final Sample sample = rrdDb.createSample();
				// test pour éviter l'erreur suivante au redéploiement par exemple:
				// org.jrobin.core.RrdException: Bad sample timestamp x. Last update time was x, at least one second step is required
				if (sample.getTime() > rrdDb.getLastUpdateTime()) {
					// set value for load datasource
					sample.setValue(getDataSourceName(), value);
					// update database
					sample.update();
				}
			} finally {
				// release RRD database reference
				rrdPool.release(rrdDb);
			}
		} catch (final RrdException e) {
			throw createIOException(e);
		}
	}

	boolean deleteFile() {
		return new File(rrdFileName).delete();
	}

	private String getApplication() {
		return application;
	}

	String getName() {
		return name;
	}

	private String getDataSourceName() {
		// RrdDef.addDatasource n'accepte pas un nom de datasource supérieur à 20 caractères
		return name.substring(0, Math.min(20, name.length()));
	}

	String getLabel() {
		return label;
	}

	private static IOException createIOException(Exception e) {
		// Rq: le constructeur de IOException avec message et cause n'existe qu'en jdk 1.6
		final IOException ex = new IOException(e.getMessage());
		ex.initCause(e);
		return ex;
	}

	//  public void test() throws RrdException, IOException {
	//    for (int i = 1000; i > 0; i--) {
	//      // request RRD database reference from the pool
	//      RrdDb rrdDb = rrdPool.requestRrdDb(rrdFileName);
	//      // create sample with the current timestamp
	//      Sample sample = rrdDb.createSample(Util.getTime() - 120 * i);
	//      // set value for load datasource
	//      // println(i + " " + new byte[5000]);
	//      sample.setValue(name, Runtime.getRuntime().totalMemory() - Runtime.getRuntime().freeMemory());
	//      // update database
	//      sample.update();
	//      // release RRD database reference
	//      rrdPool.release(rrdDb);
	//    }
	//
	//    graph(Period.JOUR);
	//    graph(Period.SEMAINE);
	//    graph(Period.MOIS);
	//    graph(Period.ANNEE);
	//  }
	//
	//  public static void main(String[] args) throws IOException, RrdException {
	//    new JRobin("Mémoire", "jrobin", 120).test();
	//  }
}
