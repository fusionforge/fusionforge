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
import java.util.List;

/**
 * Partie du rapport html pour les informations systèmes sur le serveur.
 * @author Emeric Vernat
 */
class JavaInformationsHtmlReport {
	private final List<JavaInformations> javaInformationsList;
	private final Writer writer;

	JavaInformationsHtmlReport(List<JavaInformations> javaInformationsList, Writer writer) {
		super();
		assert javaInformationsList != null;
		assert writer != null;

		this.javaInformationsList = javaInformationsList;
		this.writer = writer;
	}

	void toHtml() throws IOException {
		final String columnEnd = "</td></tr>";
		for (final JavaInformations javaInformations : javaInformationsList) {
			writeln("<table align='left' border='0' cellspacing='0' cellpadding='2' summary='Informations système'>");
			writeln("<tr><td>Mémoire java utilisée: </td><td>" + javaInformations.getUsedMemory()
					/ 1024 / 1024 + " Mo / " + javaInformations.getMaxMemory() / 1024 / 1024
					+ " Mo" + columnEnd);
			writeln("<tr><td>Nb de sessions http: </td><td>" + javaInformations.getSessionCount()
					+ columnEnd);
			writeln("<tr><td>Nb de threads actifs<br/>(Requêtes http en cours): </td><td>"
					+ javaInformations.getActiveThreadCount() + columnEnd);
			writeln("<tr><td>Nb de connexions jdbc actives: </td><td>"
					+ javaInformations.getActiveConnectionCount() + columnEnd);
			writeln("<tr><td>Nb de connexions jdbc utilisées<br/>(ouvertes si pas de datasource): </td><td>"
					+ javaInformations.getUsedConnectionCount() + columnEnd);
			writeln("</table>");
		}
		writeln("<br/><br/><br/><br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		writeln("<a href=\"javascript:{showHide('detailsJava');window.scrollTo(0,document.body.scrollHeight);}\" class='noPrint'>+/- Détails</a>");
		writeln("<br/><br/>");
		writeln("<div id='detailsJava' style='display: none;'>");
		final DateFormat dateFormat = DateFormat.getDateTimeInstance(DateFormat.SHORT,
				DateFormat.SHORT);
		for (final JavaInformations javaInformations : javaInformationsList) {
			writeln("<table align='left' border='0' cellspacing='0' cellpadding='2' summary='Détails système'>");
			writeln("<tr><td>Host: </td><td>" + javaInformations.getHost() + columnEnd);
			writeln("<tr><td>OS: </td><td>" + javaInformations.getOS() + " ("
					+ javaInformations.getAvailableProcessors() + " coeurs)" + columnEnd);
			writeln("<tr><td>Java: </td><td>" + javaInformations.getJavaVersion() + columnEnd);
			writeln("<tr><td>JVM: </td><td>" + javaInformations.getJvmVersion() + columnEnd);
			writeln("<tr><td>PID du process: </td><td>" + javaInformations.getPID() + columnEnd);
			if (javaInformations.getServerInfo() != null) {
				writeln("<tr><td>Serveur: </td><td>" + javaInformations.getServerInfo() + columnEnd);
				writeln("<tr><td>Contexte webapp: </td><td>" + javaInformations.getContextPath()
						+ columnEnd);
			}
			writeln("<tr><td>Démarrage: </td><td>"
					+ dateFormat.format(javaInformations.getStartDate()) + columnEnd);

			writeln("<tr><td valign='top'>Arguments JVM: </td><td>"
					+ javaInformations.getJvmArguments().replaceAll("\n", "<br/>") + columnEnd);

			writeln("<tr><td valign='top'>Gestion mémoire: </td><td>"
					+ javaInformations.getMemoryDetails().replaceAll("\n", "<br/>") + columnEnd);

			writeln("<tr><td valign='top'>Base de données: </td><td>"
					+ javaInformations.getDataBaseVersion().replaceAll("[\n]", "<br/>").replaceAll(
							"[&]", "&amp;") + columnEnd);
			writeln("</table>");
		}
		writeln("</div>");
	}

	private void writeln(String html) throws IOException {
		writer.write(html);
		writer.write('\n');
	}
}
