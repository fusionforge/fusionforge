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

import java.io.IOException;
import java.io.Writer;
import java.text.DateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import java.util.Set;

/**
 * Rapport html.
 * @author Emeric Vernat
 */
class HtmlReport {
	private static final boolean PDF_ENABLED = isPdfEnabled();

	private final Collector collector;
	private final List<JavaInformations> javaInformationsList;
	private final String period;
	private final Writer writer;
	private final boolean collectorServer;

	HtmlReport(Collector collector, boolean collectorServer,
			List<JavaInformations> javaInformationsList, String period, Writer writer) {
		super();
		assert collector != null;
		assert javaInformationsList != null;
		assert period != null;
		assert writer != null;

		this.collector = collector;
		this.collectorServer = collectorServer;
		this.javaInformationsList = javaInformationsList;
		this.period = period;
		this.writer = writer;
	}

	private static boolean isPdfEnabled() {
		try {
			Class.forName("com.lowagie.text.Document");
			return true;
		} catch (final ClassNotFoundException e) {
			return false;
		}
	}

	void toHtml(String message) throws IOException {
		final long start = System.currentTimeMillis();
		writeHtmlHeader(false);
		if (collectorServer) {
			writeApplicationsLinks(buildPeriodParameter());
		}

		final DateFormat dateFormat = DateFormat.getDateTimeInstance(DateFormat.SHORT,
				DateFormat.SHORT);
		writeln("<h3><img width='24' height='24' src='?resource=systemmonitor.png' alt='Stats'/>");
		writeln("Statistiques de monitoring mesurées le " + dateFormat.format(new Date())
				+ " depuis le " + dateFormat.format(collector.getCounters().get(0).getStartDate())
				+ " sur " + collector.getApplication() + "</h3>");
		writeln("<div align='center'>");
		writeRefreshAndPeriodLinks(null);
		writeGraphs();
		writeln("</div>");

		for (final Counter counter : collector.getPeriodCounters(Period.valueOfIgnoreCase(period))) {
			if (counter.isDisplayed()) {
				writeln("<h3><img width='24' height='24' src='?resource=" + counter.getIconName()
						+ "' alt='" + counter.getName() + "'/>Statistiques " + counter.getName()
						+ "</h3>");
				new CounterHtmlReport(counter, period, writer).toHtml();
			}
		}

		writeln("<h3><img width='24' height='24' src='?resource=systeminfo.png' alt='Informations systèmes'/>");
		writeln("Informations système</h3>");
		writeSystemActionsLinks();

		new JavaInformationsHtmlReport(javaInformationsList, writer).toHtml();

		writeln("<h3 style='clear:both;'><img width='24' height='24' src='?resource=threads.png' alt='Threads'/>");
		writeln("Threads</h3>");
		writeThreads();

		if (message != null) {
			writeln("<script type='text/javascript'>");
			writeln("alert(\"" + message + "\");");
			writeln("</script>");
		}
		final long displayDuration = System.currentTimeMillis() - start;
		writeln("<div style='font-size:10pt;'>Temps de la dernière collecte: "
				+ collector.getLastCollectDuration() + " ms<br/>Temps d'affichage: "
				+ displayDuration + " ms</div>");

		writeHtmlFooter();
	}

	private String buildPeriodParameter() {
		return "&amp;period=" + period;
	}

	private void writeGraphs() throws IOException {
		final String periodParameter = buildPeriodParameter();
		for (final JRobin jrobin : collector.getCounterJRobins()) {
			// les jrobin de compteurs (qui commencent par le jrobin xxxHitsRate)
			// doivent être sur une même ligne donc on met un <br/> si c'est le premier
			final String jrobinName = jrobin.getName();
			if (jrobinName.endsWith("HitsRate")) {
				writeln("<br/>");
			}
			if (isJRobinDisplayed(jrobinName)) {
				writeln("<a href='?graphDetail=" + jrobinName + periodParameter
						+ "'><img class='synthese' src='?width=200&amp;height=50&amp;graph="
						+ jrobinName + periodParameter + "' alt=\"" + jrobin.getLabel()
						+ "\"/></a>");
			}
			if ("httpSessions".equals(jrobinName)) {
				// un <br/> après httpSessions et avant activeThreads pour l'alignement
				writeln("<br/>");
			}
		}
	}

	private boolean isJRobinDisplayed(String jrobinName) {
		for (final Counter counter : collector.getCounters()) {
			if (jrobinName.startsWith(counter.getName())) {
				return counter.isDisplayed();
			}
		}
		return true;
	}

	private void writeThreads() throws IOException {
		int i = 0;
		for (final JavaInformations javaInformations : javaInformationsList) {
			writeln("<b>Threads sur " + javaInformations.getHost() + ": </b>");
			writeln(javaInformations.getThreadsDetails().replace('\n', ' '));
			writeln("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			final String id = "threads_" + i;
			writeln("<a href=\"javascript:{scrollHeight=document.body.scrollHeight-40;showHide('"
					+ id + "');window.scrollTo(0,scrollHeight);}\" class='noPrint'>+/- Détails</a>");
			writeln("<br/><br/>");
			writeln("<div id='" + id + "' style='display: none;'>");
			new ThreadInformationsHtmlReport(javaInformations.getThreadInformationsList(), writer)
					.toHtml();
			if (JavaInformations.STACK_TRACES_ENABLED) {
				// pour que les tooltips des stack traces s'affichent dans le scroll
				writeln("<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>");
			}
			writeln("</div><br/>");
			i++;
		}
	}

	private void writeSystemActionsLinks() throws IOException {
		if (Boolean.parseBoolean(Parameters.getParameter(Parameter.SYSTEM_ACTIONS_ENABLED))) {
			final String periodParameter = buildPeriodParameter();
			writeln("<div align='center' class='noPrint'>");
			if (Action.GC_ENABLED || collectorServer) {
				writeln("<a href='?action=gc"
						+ periodParameter
						+ "' onclick=\"javascript:return confirm('Confirmez-vous l\\'exécution du ramasse miette (GC) ?\\n(cela peut nécessiter quelques secondes)');\">"
						+ "Exécuter le ramasse miette</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			} else {
				writeln("<a href='#' onclick=\"javascript:alert('Le ramasse-miette est explicitement désactivé et ne peut être exécuté.');return false;\">"
						+ "Exécuter le ramasse miette</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			}
			writeln("<a href='?action=invalidate_sessions"
					+ periodParameter
					+ "' onclick=\"javascript:return confirm('Confirmez-vous l\\'invalidation des sessions http ?\\n(les utilisateurs devront se reconnecter)');\">"
					+ "Invalider les sessions http</a>");
			if (Action.HEAP_DUMP_ENABLED || collectorServer) {
				// si serveur de collecte, on suppose que si la version de java est la bonne
				// sur le serveur de collecte, ce sera la bonne aussi sur les serveurs
				// des webapps monitorées
				writeln("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='?action=heap_dump"
						+ periodParameter
						+ "' onclick=\"javascript:return confirm('Confirmez-vous la génération d\\'un heap dump dans le répertoire temporaire du serveur ?\\n(cela peut nécessiter quelques minutes)');\">"
						+ "Générer un heap dump</a>");
			}
			writeln("<br/></div>");
		}
	}

	private void writeApplicationsLinks(String periodParameter) throws IOException {
		final Set<String> applications = Parameters.getCollectorUrlsByApplications().keySet();
		if (applications.size() > 1) {
			writeln("<div align='center'>");
			writeln("&nbsp;&nbsp;&nbsp;Choix de l'application :&nbsp;&nbsp;&nbsp;");
			for (final String application : applications) {
				writeln("<a href='?application=" + application + periodParameter + "'>"
						+ application + "</a>&nbsp;&nbsp;&nbsp;");
			}
			writeln("</div>");
		}
	}

	private void writeRefreshAndPeriodLinks(String graphDetail) throws IOException {
		writeln("<div class='noPrint'>");
		final String start;
		if (graphDetail == null) {
			start = "<a href='?period=";
		} else {
			writer.write("<a href='?period=" + period
					+ "'>Retour</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			start = "<a href='?graphDetail=" + graphDetail + "&amp;period=";
		}
		writeln(start + period + "'>Actualiser</a>");
		if (graphDetail == null && PDF_ENABLED) {
			writeln("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + start + period
					+ "&amp;format=pdf'>PDF</a>");
		}
		writeln("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Choix de graphiques :&nbsp;&nbsp;&nbsp;");
		// On affiche des liens vers les périodes.
		// Rq : il n'y a pas de période ni de graph sur la dernière heure puisque
		// si la résolution des données est de 5 min, on ne verra alors presque rien
		for (final Period myPeriod : Period.values()) {
			writeln(start + myPeriod.toString().toLowerCase(Locale.getDefault()) + "'>"
					+ myPeriod.getLinkLabel() + "</a>&nbsp;&nbsp;&nbsp;");
		}
		writeln("</div>");
	}

	private void writeHtmlHeader(boolean includeSlider) throws IOException {
		writeln("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">");
		writeln("<html><head><title>Monitoring sur " + collector.getApplication() + "</title>");
		writeln("<link rel='stylesheet' href='?resource=monitoring.css' type='text/css'/>");
		writeln("<script type='text/javascript' src='?resource=sorttable.js'></script>");
		if (includeSlider) {
			writeln("<script type='text/javascript' src='?resource=prototype.js'></script>");
			writeln("<script type='text/javascript' src='?resource=slider.js'></script>");
		}
		writeJavaScript();
		writeln("</head><body>");
	}

	private void writeHtmlFooter() throws IOException {
		writeln("</body></html>");
	}

	private void writeJavaScript() throws IOException {
		writeln("<script type='text/javascript'>");
		writeln("function showHide(id){");
		writeln("  if (document.getElementById(id).style.display=='none') {");
		writeln("    document.getElementById(id).style.display='inline';");
		writeln("  } else {");
		writeln("    document.getElementById(id).style.display='none';");
		writeln("  }");
		writeln("}");
		writeln("</script>");
	}

	void writeGraphDetail(String graphDetail) throws IOException {
		writeHtmlHeader(true);

		final String divAlignCenter = "<div align='center'>";
		writeln(divAlignCenter);
		writeRefreshAndPeriodLinks(graphDetail);
		writeln("</div>\n");

		writeln("<div id='track'>");
		writeln("<div class='selected' id='handle'>");
		writeln("<img src='?resource=scaler_slider.gif' alt=''/>");
		writeln("</div></div>");

		writeln(divAlignCenter);
		writeln("<img class='synthèse' id='img' src='" + "?width=960&amp;height=400&amp;graph="
				+ graphDetail + "&amp;" + "period=" + period + "' alt='zoom'/>");

		writeln("<script type='text/javascript'>");
		writeln("function scaleImage(v, min, max) {");
		writeln("    var images = document.getElementsByClassName('synthèse');");
		writeln("    w = (max - min) * v + min;");
		writeln("    for (i = 0; i < images.length; i++) {");
		writeln("        images[i].style.width = w + 'px';");
		writeln("    }");
		writeln("}");

		// 'animate' our slider
		writeln("var slider = new Control.Slider('handle', 'track', {axis:'horizontal', alignX: 0, increment: 2});");

		// resize the image as the slider moves. The image quality would deteriorate, but it
		// would not be final anyway. Once slider is released the image is re-requested from the server, where
		// it is rebuilt from vector format
		writeln("slider.options.onSlide = function(value) {");
		writeln("  scaleImage(value, initialWidth, initialWidth / 2 * 3);");
		writeln("}");

		// this is where the slider is released and the image is reloaded
		// we use current style settings to work the required image dimensions
		writeln("slider.options.onChange = function(value) {");
		// chop off "px" and round up float values
		writeln("  width = Math.round(Element.getStyle('img','width').replace('px','')) - 80;");
		writeln("  height = Math.round(width * initialHeight / initialWidth) - 48;");
		// reload the images
		// rq : on utilise des caractères unicode pour éviter des warnings
		writeln("  document.getElementById('img').src = '?graph=" + graphDetail + "\\u0026period="
				+ period + "\\u0026width=' + width + '\\u0026height=' + height;");
		writeln("  document.getElementById('img').style.width = '';");
		writeln("}");
		writeln("window.onload = function() {");
		writeln("  if (navigator.appName == 'Microsoft Internet Explorer') {");
		writeln("    initialWidth = document.getElementById('img').width;");
		writeln("    initialHeight = document.getElementById('img').height;");
		writeln("  } else {");
		writeln("    initialWidth = Math.round(Element.getStyle('img','width').replace('px',''));");
		writeln("    initialHeight = Math.round(Element.getStyle('img','height').replace('px',''));");
		writeln("  }");
		writeln("}");
		writeln("</script>");

		writeln("</div><br/>");

		writeHtmlFooter();
	}

	private void writeln(String html) throws IOException {
		writer.write(html);
		writer.write('\n');
	}
}
