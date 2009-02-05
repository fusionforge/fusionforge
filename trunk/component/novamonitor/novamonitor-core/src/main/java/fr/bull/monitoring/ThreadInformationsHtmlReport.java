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
import java.text.DecimalFormat;
import java.util.List;

/**
 * Partie du rapport html pour les threads sur le serveur.
 * @author Emeric Vernat
 */
class ThreadInformationsHtmlReport {
	private final List<ThreadInformations> threadInformationsList;
	private final Writer writer;
	private final DecimalFormat integerFormat = new DecimalFormat("#,##0");
	private final boolean stackTraceEnabled;
	private final boolean cpuTimeEnabled;

	ThreadInformationsHtmlReport(List<ThreadInformations> threadInformationsList, Writer writer) {
		super();
		assert threadInformationsList != null;
		assert writer != null;

		this.threadInformationsList = threadInformationsList;
		this.writer = writer;
		this.stackTraceEnabled = computeStackTraceEnabled();
		this.cpuTimeEnabled = !threadInformationsList.isEmpty()
				&& threadInformationsList.get(0).getCpuTimeMillis() != -1;
	}

	private boolean computeStackTraceEnabled() {
		for (final ThreadInformations threadInformations : threadInformationsList) {
			final List<StackTraceElement> stackTrace = threadInformations.getStackTrace();
			if (stackTrace != null && !stackTrace.isEmpty()) {
				return true;
			}
		}
		return false;
	}

	void toHtml() throws IOException {
		writeln("<table class='sortable' width='100%' border='1' cellspacing='0' cellpadding='2' summary='threads'>");
		write("<thead><tr><th>Thread</th>");
		write("<th>Démon ?</th><th class='sorttable_numeric'>Priorité</th><th>Etat</th>");
		if (stackTraceEnabled) {
			write("<th>Méthode exécutée</th>");
		}
		if (cpuTimeEnabled) {
			write("<th class='sorttable_numeric'>Temps cpu (ms)</th><th class='sorttable_numeric'>Temps user (ms)</th>");
		}
		writeln("</tr></thead><tbody>");
		boolean odd = false;
		for (final ThreadInformations threadInformations : threadInformationsList) {
			if (odd) {
				write("<tr class='odd'>");
			} else {
				write("<tr>");
			}
			odd = !odd; // NOPMD
			writeThreadInformations(threadInformations);
			writeln("</tr>");
		}
		writeln("</tbody></table>");
	}

	private void writeThreadInformations(ThreadInformations threadInformations) throws IOException {
		write("<td>");
		final List<StackTraceElement> stackTrace = threadInformations.getStackTrace();
		if (stackTrace != null && !stackTrace.isEmpty()) {
			// même si stackTraceEnabled, ce thread n'a pas forcément de stack-trace
			writeln("<a class='tooltip'>");
			writeln("<em>");
			writeln(threadInformations.getName());
			writeln("<br/>");
			for (final StackTraceElement stackTraceElement : stackTrace) {
				writeln(htmlEncode(stackTraceElement.toString()));
				writeln("<br/>");
			}
			writeln("</em>");
			writeln(threadInformations.getName());
			writeln("</a>");
		} else {
			write(threadInformations.getName());
		}
		write("</td> <td align='center'>");
		if (threadInformations.isDaemon()) {
			write("oui");
		} else {
			write("non");
		}
		write("</td> <td align='right'>");
		write(integerFormat.format(threadInformations.getPriority()));
		write("</td> <td>");
		write(String.valueOf(threadInformations.getState()));
		if (stackTraceEnabled) {
			write("</td> <td>");
			if (stackTrace != null && !stackTrace.isEmpty()) {
				write(htmlEncode(stackTrace.get(0).toString()));
			} else {
				write("&nbsp;");
			}
		}
		if (cpuTimeEnabled) {
			write("</td> <td align='right'>");
			write(integerFormat.format(threadInformations.getCpuTimeMillis()));
			write("</td> <td align='right'>");
			write(integerFormat.format(threadInformations.getUserTimeMillis()));
		}
		write("</td>");
	}

	private String htmlEncode(String string) {
		return string.replaceAll("[<]", "&lt;").replaceAll("[>]", "&gt;").replaceAll(" ", "&nbsp;");
	}

	private void write(String html) throws IOException {
		writer.write(html);
	}

	private void writeln(String html) throws IOException {
		writer.write(html);
		writer.write('\n');
	}
}
