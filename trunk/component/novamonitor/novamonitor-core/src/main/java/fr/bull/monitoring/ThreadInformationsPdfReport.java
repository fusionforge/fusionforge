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

import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

import com.lowagie.text.Document;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Element;
import com.lowagie.text.Font;
import com.lowagie.text.FontFactory;
import com.lowagie.text.Phrase;
import com.lowagie.text.pdf.PdfPCell;
import com.lowagie.text.pdf.PdfPTable;

/**
 * Partie du rapport pdf pour les threads sur le serveur.
 * @author Emeric Vernat
 */
class ThreadInformationsPdfReport {
	private final List<ThreadInformations> threadInformationsList;
	private final Document document;
	private final DecimalFormat integerFormat = new DecimalFormat("#,##0");
	private final boolean stackTraceEnabled;
	private final boolean cpuTimeEnabled;
	private final Font cellFont;
	private PdfPTable currentTable;

	ThreadInformationsPdfReport(List<ThreadInformations> threadInformationsList, Document document) {
		super();
		assert threadInformationsList != null;
		assert document != null;

		this.threadInformationsList = threadInformationsList;
		this.document = document;
		this.stackTraceEnabled = computeStackTraceEnabled();
		this.cpuTimeEnabled = !threadInformationsList.isEmpty()
				&& threadInformationsList.get(0).getCpuTimeMillis() != -1;
		this.cellFont = getFont(6, Font.NORMAL);
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

	void toPdf() throws DocumentException {
		final List<String> headers = new ArrayList<String>();
		headers.add("Thread");
		headers.add("Démon ?");
		headers.add("Priorité");
		headers.add("Etat");
		if (stackTraceEnabled) {
			headers.add("Méthode exécutée");
		}
		if (cpuTimeEnabled) {
			headers.add("Temps cpu (ms)");
			headers.add("Temps user (ms)");
		}

		currentTable = new PdfPTable(headers.size());
		currentTable.setWidthPercentage(100);
		final int[] relativeWidths = new int[headers.size()];
		Arrays.fill(relativeWidths, 0, headers.size(), 1);
		relativeWidths[0] = 3; // thread
		relativeWidths[3] = 2; // état
		if (stackTraceEnabled) {
			relativeWidths[4] = 6; // méthode exécutée
		}
		currentTable.setWidths(relativeWidths);
		getDefaultCell().setGrayFill(0.9f);
		getDefaultCell().setHorizontalAlignment(Element.ALIGN_CENTER);
		getDefaultCell().setPaddingLeft(0);
		getDefaultCell().setPaddingRight(0);
		final Font headerFont = getFont(6, Font.BOLD);
		for (final String header : headers) {
			currentTable.addCell(new Phrase(header, headerFont));
		}
		getDefaultCell().setPaddingLeft(2);
		getDefaultCell().setPaddingRight(2);

		boolean odd = false;
		for (final ThreadInformations threadInformations : threadInformationsList) {
			if (odd) {
				getDefaultCell().setGrayFill(0.97f);
			} else {
				getDefaultCell().setGrayFill(1);
			}
			odd = !odd; // NOPMD
			writeThreadInformations(threadInformations);
		}
		document.add(currentTable);

		// rq stack-trace: on n'inclue pas dans le pdf les stack-traces des threads
		// car c'est très verbeux et cela remplirait des pages pour pas grand chose
		// d'autant que si le pdf est généré de nuit pour être envoyé par mail
		// alors ces stack-traces n'ont pas beaucoup d'intérêt
		//		if (stackTrace != null && !stackTrace.isEmpty()) {
		//			// même si stackTraceEnabled, ce thread n'a pas forcément de stack-trace
		//			writeln(threadInformations.getName());
		//			for (final StackTraceElement stackTraceElement : stackTrace) {
		//				writeln(stackTraceElement.toString());
		//			}
		//		}
	}

	private void writeThreadInformations(ThreadInformations threadInformations) {
		getDefaultCell().setHorizontalAlignment(Element.ALIGN_LEFT);
		addCell(threadInformations.getName());
		getDefaultCell().setHorizontalAlignment(Element.ALIGN_CENTER);
		if (threadInformations.isDaemon()) {
			addCell("oui");
		} else {
			addCell("non");
		}
		getDefaultCell().setHorizontalAlignment(Element.ALIGN_RIGHT);
		addCell(integerFormat.format(threadInformations.getPriority()));
		getDefaultCell().setHorizontalAlignment(Element.ALIGN_LEFT);
		addCell(String.valueOf(threadInformations.getState()));
		if (stackTraceEnabled) {
			final List<StackTraceElement> stackTrace = threadInformations.getStackTrace();
			if (stackTrace != null && !stackTrace.isEmpty()) {
				addCell(stackTrace.get(0).toString());
			} else {
				addCell("");
			}
		}
		if (cpuTimeEnabled) {
			getDefaultCell().setHorizontalAlignment(Element.ALIGN_RIGHT);
			addCell(integerFormat.format(threadInformations.getCpuTimeMillis()));
			addCell(integerFormat.format(threadInformations.getUserTimeMillis()));
		}
	}

	private Font getFont(int size, int style) {
		return FontFactory.getFont(FontFactory.HELVETICA, size, style);
	}

	private PdfPCell getDefaultCell() {
		return currentTable.getDefaultCell();
	}

	private void addCell(String string) {
		currentTable.addCell(new Phrase(string, cellFont));
	}
}
