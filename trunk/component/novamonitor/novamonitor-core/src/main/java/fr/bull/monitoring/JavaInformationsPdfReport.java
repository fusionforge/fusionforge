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

import java.text.DateFormat;
import java.util.List;

import com.lowagie.text.Document;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Element;
import com.lowagie.text.Font;
import com.lowagie.text.FontFactory;
import com.lowagie.text.Phrase;
import com.lowagie.text.pdf.PdfPTable;

/**
 * Partie du rapport pdf pour les informations systèmes sur le serveur.
 * @author Emeric Vernat
 */
class JavaInformationsPdfReport {
	private final List<JavaInformations> javaInformationsList;
	private final Document document;
	private final Font cellFont;
	private PdfPTable currentTable;

	JavaInformationsPdfReport(List<JavaInformations> javaInformationsList, Document document) {
		super();
		assert javaInformationsList != null;
		assert document != null;

		this.javaInformationsList = javaInformationsList;
		this.document = document;
		this.cellFont = FontFactory.getFont(FontFactory.HELVETICA, 6, Font.NORMAL);
	}

	void toPdf() throws DocumentException {
		for (final JavaInformations javaInformations : javaInformationsList) {
			currentTable = new PdfPTable(2);
			currentTable.setHorizontalAlignment(Element.ALIGN_LEFT);
			currentTable.setWidthPercentage(100);
			currentTable.setWidths(new int[] { 2, 8 });
			currentTable.getDefaultCell().setBorder(0);
			writeInformationsSummary(javaInformations);
			addCell("");
			addCell("");
			document.add(currentTable);
		}
	}

	private void writeInformationsSummary(final JavaInformations javaInformations) {
		addCell("Mémoire java utilisée:");
		addCell(javaInformations.getUsedMemory() / 1024 / 1024 + " Mo / "
				+ javaInformations.getMaxMemory() / 1024 / 1024 + " Mo");
		addCell("Nb de sessions http:");
		addCell(String.valueOf(javaInformations.getSessionCount()));
		addCell("Nb de threads actifs\n(Requêtes http en cours):");
		addCell(String.valueOf(javaInformations.getActiveThreadCount()));
		addCell("Nb de connexions jdbc actives:");
		addCell(String.valueOf(javaInformations.getActiveConnectionCount()));
		addCell("Nb de connexions jdbc utilisées\n(ouvertes si pas de datasource):");
		addCell(String.valueOf(javaInformations.getUsedConnectionCount()));
	}

	void writeInformationsDetails() throws DocumentException {
		final DateFormat dateFormat = DateFormat.getDateTimeInstance(DateFormat.SHORT,
				DateFormat.SHORT);
		for (final JavaInformations javaInformations : javaInformationsList) {
			currentTable = new PdfPTable(2);
			currentTable.setHorizontalAlignment(Element.ALIGN_LEFT);
			currentTable.setWidthPercentage(100);
			currentTable.setWidths(new int[] { 2, 8 });
			currentTable.getDefaultCell().setBorder(0);

			writeInformationsSummary(javaInformations);

			addCell("Host:");
			addCell(javaInformations.getHost());
			addCell("OS:");
			addCell(javaInformations.getOS() + " (" + javaInformations.getAvailableProcessors()
					+ " coeurs)");
			addCell("Java:");
			addCell(javaInformations.getJavaVersion());
			addCell("JVM:");
			addCell(javaInformations.getJvmVersion());
			addCell("PID du process:");
			addCell(javaInformations.getPID());
			if (javaInformations.getServerInfo() != null) {
				addCell("Serveur:");
				addCell(javaInformations.getServerInfo());
				addCell("Contexte webapp:");
				addCell(javaInformations.getContextPath());
			}
			addCell("Démarrage:");
			addCell(dateFormat.format(javaInformations.getStartDate()));
			addCell("Arguments JVM:");
			addCell(javaInformations.getJvmArguments());
			addCell("Gestion mémoire:");
			addCell(javaInformations.getMemoryDetails());
			addCell("Base de données:");
			addCell(javaInformations.getDataBaseVersion());
			addCell("");
			addCell("");
			document.add(currentTable);
		}
	}

	private void addCell(String string) {
		currentTable.addCell(new Phrase(string, cellFont));
	}
}
