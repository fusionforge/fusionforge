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

import java.io.File;
import java.io.IOException;
import java.lang.management.ManagementFactory;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

import javax.management.JMException;
import javax.management.MBeanServer;
import javax.management.ObjectInstance;
import javax.management.ObjectName;

/**
 * Enumération des actions possibles dans l'IHM.
 * @author Emeric Vernat
 */
enum Action {
	/**
	 * Réinitialisation d'un compteur non périodique.
	 */
	CLEAR_COUNTER,
	/**
	 * Garbage Collect.
	 */
	GC,
	/**
	 * Invalidations des sessions http.
	 */
	INVALIDATE_SESSIONS,
	/**
	 * Heap dump.
	 */
	HEAP_DUMP;

	static final boolean GC_ENABLED = !ManagementFactory.getRuntimeMXBean().getInputArguments()
			.contains("-XX:+DisableExplicitGC");
	static final boolean HEAP_DUMP_ENABLED = "1.6".compareTo(System.getProperty("java.version")) < 0
			&& System.getProperty("java.vendor").contains("Sun");

	static Action valueOfIgnoreCase(String action) {
		return valueOf(action.toUpperCase(Locale.getDefault()).trim());
	}

	String execute(Collector collector, String counterName) {
		String messageForReport;
		switch (this) {
		case CLEAR_COUNTER:
			// l'action Réinitialiser a été appelée pour un compteur
			collector.clearCounter(counterName);
			messageForReport = "Statistiques " + counterName + " réinitialisées.";
			break;
		case GC:
			if (GC_ENABLED) {
				// garbage collector
				final long before = Runtime.getRuntime().totalMemory()
						- Runtime.getRuntime().freeMemory();
				gc();
				final long after = Runtime.getRuntime().totalMemory()
						- Runtime.getRuntime().freeMemory();
				messageForReport = "Ramasse miette exécuté (" + (before - after) / 1024
						+ " Ko libérés)";
			} else {
				messageForReport = "Le ramasse-miette est explicitement désactivé et ne peut être exécuté.";
			}
			break;
		case HEAP_DUMP:
			if (HEAP_DUMP_ENABLED) {
				// heap dump à générer dans le répertoire temporaire sur le serveur
				// avec un suffixe contenant le host, la date et l'heure et avec une extension hprof
				// (utiliser jvisualvm du jdk ou MAT d'eclipse en standalone ou en plugin)
				final String heapDumpPath = heapDump();
				messageForReport = "Heap dump généré dans\\n"
						+ heapDumpPath.replace('\\', '/')
						+ "\\n\\nIl peut être ouvert avec jVisualVM ou jhat du JDK ou avec MAT d'Eclipse.";
			} else {
				messageForReport = "Cette version de java ne permet de pas de générer un heap-dump.";
			}
			break;
		case INVALIDATE_SESSIONS:
			// invalidation des sessions http
			SessionListener.invalidateAllSessions();
			messageForReport = "Sessions http invalidées";
			break;
		default:
			throw new IllegalStateException(toString());
		}
		return messageForReport;
	}

	private String heapDump() {
		final boolean gcBeforeHeapDump = true;
		final DateFormat dateFormat = new SimpleDateFormat("yyyyMMdd_HHmmss", Locale.getDefault());
		final String heapDumpPath = Parameters.TEMPORARY_DIRECTORY + "/heapdump-"
				+ Parameters.getHostName() + '-' + dateFormat.format(new Date()) + ".hprof";
		if (new File(heapDumpPath).exists()) {
			try {
				// si le fichier existe déjà, un heap dump a déjà été généré dans la même seconde
				// donc on attends 1 seconde pour créer le fichier avec un nom différent
				Thread.sleep(1000);
			} catch (final InterruptedException e) {
				throw new IllegalStateException(e);
			}
			return heapDump();
		}
		try {
			final MBeanServer platformMBeanServer = ManagementFactory.getPlatformMBeanServer();
			final ObjectInstance instance = platformMBeanServer.getObjectInstance(new ObjectName(
					"com.sun.management:type=HotSpotDiagnostic"));
			((com.sun.management.HotSpotDiagnosticMXBean) platformMBeanServer.instantiate(instance
					.getClassName())).dumpHeap(heapDumpPath, gcBeforeHeapDump);
		} catch (final IOException e) { // NOPMD
			throw new IllegalStateException(e);
		} catch (final JMException e) {
			throw new IllegalStateException(e);
		}
		return heapDumpPath;
	}

	@SuppressWarnings("all")
	private void gc() {
		Runtime.getRuntime().gc();
	}

}
