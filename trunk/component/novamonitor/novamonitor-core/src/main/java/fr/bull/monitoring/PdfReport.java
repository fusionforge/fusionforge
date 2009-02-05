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
import java.io.OutputStream;
import java.text.DateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import com.lowagie.text.Chunk;
import com.lowagie.text.Document;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Element;
import com.lowagie.text.Font;
import com.lowagie.text.FontFactory;
import com.lowagie.text.Image;
import com.lowagie.text.Paragraph;
import com.lowagie.text.Phrase;
import com.lowagie.text.pdf.PdfPTable;

/**
 * Rapport pdf (avec iText).
 * @author Emeric Vernat
 */
class PdfReport {
	private final Collector collector;
	private final List<JavaInformations> javaInformationsList;
	private final String period;
	private final Document document;

	PdfReport(Collector collector, List<JavaInformations> javaInformationsList, String period,
			OutputStream output) throws IOException {
		super();
		assert collector != null;
		assert javaInformationsList != null;
		assert period != null;
		assert output != null;

		this.collector = collector;
		this.javaInformationsList = javaInformationsList;
		this.period = period;

		try {
			this.document = new PdfDocumentFactory(collector.getApplication(), output)
					.createDocument();
		} catch (final DocumentException e) {
			throw createIOException(e);
		}
	}

	static String getFileName(String application) {
		final DateFormat dateFormat = DateFormat.getDateInstance(DateFormat.SHORT);
		return "Monitoring_" + application.replace(' ', '_').replace("/", "") + '_'
				+ dateFormat.format(new Date()).replace('/', '_') + ".pdf";
	}

	void toPdf() throws IOException {
		try {
			document.open();

			// il serait possible d'ouvrir la boîte de dialogue Imprimer de Adobe Reader
			//		      if (writer instanceof PdfWriter) {
			//		        ((PdfWriter) writer).addJavaScript("this.print(true);", false);
			//		      }

			writeContent();
		} catch (final DocumentException e) {
			throw createIOException(e);
		}

		document.close();
	}

	private IOException createIOException(DocumentException e) {
		// Rq: le constructeur de IOException avec message et cause n'existe qu'en jdk 1.6
		final IOException ex = new IOException(e.getMessage());
		ex.initCause(e);
		return ex;
	}

	private void writeContent() throws IOException, DocumentException {
		final long start = System.currentTimeMillis();

		final DateFormat dateFormat = DateFormat.getDateTimeInstance(DateFormat.SHORT,
				DateFormat.SHORT);
		addParagraph("Statistiques de monitoring mesurées le " + dateFormat.format(new Date())
				+ " depuis le " + dateFormat.format(collector.getCounters().get(0).getStartDate())
				+ " sur " + collector.getApplication(), "systemmonitor.png");

		writeGraphs();

		final List<CounterPdfReport> counterPdfReports = new ArrayList<CounterPdfReport>();
		for (final Counter counter : collector.getPeriodCounters(Period.valueOfIgnoreCase(period))) {
			if (counter.isDisplayed()) {
				addParagraph("Statistiques " + counter.getName(), counter.getIconName());
				final CounterPdfReport counterPdfReport = new CounterPdfReport(collector, counter,
						period, document);
				counterPdfReport.toPdf();
				counterPdfReports.add(counterPdfReport);
			}
		}

		final Font normalFont = getFont(8, Font.NORMAL);
		add(new Phrase("\n", normalFont));
		addParagraph("Informations système", "systeminfo.png");
		final JavaInformationsPdfReport javaInformationsPdfReport = new JavaInformationsPdfReport(
				javaInformationsList, document);
		javaInformationsPdfReport.toPdf();

		add(new Phrase("\n", normalFont));
		addParagraph("Threads", "threads.png");
		writeThreads();

		document.newPage();
		addParagraph("Statistiques détaillées", "systemmonitor.png");
		final PdfPTable jrobinTable = new PdfPTable(1);
		jrobinTable.setHorizontalAlignment(Element.ALIGN_CENTER);
		jrobinTable.setWidthPercentage(100);
		jrobinTable.getDefaultCell().setBorder(0);
		for (final JRobin jrobin : collector.getCounterJRobins()) {
			// les jrobin de compteurs (qui commencent par le jrobin xxxHitsRate)
			// doivent être sur une même ligne donc on met un <br/> si c'est le premier
			final String jrobinName = jrobin.getName();
			if (isJRobinDisplayed(jrobinName)) {
				final Image image = Image.getInstance(jrobin.graph(period, 960, 390));
				jrobinTable.addCell(image);
			}
		}
		document.add(jrobinTable);
		document.newPage();

		for (final CounterPdfReport counterPdfReport : counterPdfReports) {
			add(new Phrase("\n", normalFont));
			addParagraph("Statistiques " + counterPdfReport.getCounterName() + " détaillées",
					counterPdfReport.getCounterIconName());
			counterPdfReport.writeRequestDetails();
		}

		add(new Phrase("\n", normalFont));
		addParagraph("Informations système détaillées", "systeminfo.png");
		javaInformationsPdfReport.writeInformationsDetails();

		add(new Phrase("\n", normalFont));
		addParagraph("Threads détaillés", "threads.png");
		writeThreadsDetails();

		final long displayDuration = System.currentTimeMillis() - start;
		add(new Phrase("\nTemps de la dernière collecte: " + collector.getLastCollectDuration()
				+ " ms\nTemps d'affichage: " + displayDuration + " ms", getFont(6, Font.NORMAL)));
	}

	private void writeGraphs() throws IOException, DocumentException {
		final Paragraph jrobinParagraph = new Paragraph("", getFont(9, Font.NORMAL));
		jrobinParagraph.setAlignment(Element.ALIGN_CENTER);
		jrobinParagraph.add(new Phrase("\n\n\n\n\n"));
		for (final JRobin jrobin : collector.getCounterJRobins()) {
			// les jrobin de compteurs (qui commencent par le jrobin xxxHitsRate)
			// doivent être sur une même ligne donc on met un <br/> si c'est le premier
			final String jrobinName = jrobin.getName();
			if (isJRobinDisplayed(jrobinName)) {
				if (jrobinName.endsWith("HitsRate")) {
					jrobinParagraph.add(new Phrase("\n\n\n\n\n"));
				}
				final Image image = Image.getInstance(jrobin.graph(period, 200, 50));
				image.scalePercent(50);
				jrobinParagraph.add(new Phrase(new Chunk(image, 0, 0)));
				jrobinParagraph.add(new Phrase(" "));
			}
			if ("httpSessions".equals(jrobinName)) {
				// un <br/> après httpSessions et avant activeThreads pour l'alignement
				jrobinParagraph.add(new Phrase("\n\n\n\n\n"));
			}
		}
		jrobinParagraph.add(new Phrase("\n"));
		add(jrobinParagraph);
	}

	private boolean isJRobinDisplayed(String jrobinName) {
		for (final Counter counter : collector.getCounters()) {
			if (jrobinName.startsWith(counter.getName())) {
				return counter.isDisplayed();
			}
		}
		return true;
	}

	private void writeThreads() throws DocumentException {
		final Font fontBold = getFont(8, Font.BOLD);
		final Font fontNormal = getFont(8, Font.NORMAL);
		add(new Phrase("\n", fontNormal));
		for (final JavaInformations javaInformations : javaInformationsList) {
			add(new Phrase("\nThreads sur " + javaInformations.getHost() + ": ", fontBold));
			add(new Phrase(javaInformations.getThreadsDetails().replace('\n', ' '), fontNormal));
		}
	}

	private void writeThreadsDetails() throws DocumentException {
		final Font fontBold = getFont(8, Font.BOLD);
		final Font fontNormal = getFont(8, Font.NORMAL);
		add(new Phrase("\n", fontNormal));
		for (final JavaInformations javaInformations : javaInformationsList) {
			add(new Phrase("\nThreads sur " + javaInformations.getHost() + ": ", fontBold));
			add(new Phrase(javaInformations.getThreadsDetails().replace('\n', ' '), fontNormal));

			new ThreadInformationsPdfReport(javaInformations.getThreadInformationsList(), document)
					.toPdf();
		}
	}

	private void addParagraph(String paragraphTitle, String iconName) throws DocumentException,
			IOException {
		add(new Phrase("\n", getFont(6, Font.NORMAL)));
		add(new Chunk(getImage(iconName), 0, -5));
		add(new Chunk(' ' + paragraphTitle, getFont(10, Font.BOLD)));
	}

	private Font getFont(int size, int style) {
		return FontFactory.getFont(FontFactory.HELVETICA, size, style);
	}

	private void add(Element element) throws DocumentException {
		document.add(element);
	}

	private Image getImage(String resourceFileName) throws DocumentException, IOException {
		final Image image = Image.getInstance(getClass().getResource(
				Parameters.getResourcePath(resourceFileName)));
		// toutes les icônes ont la même taille
		image.scaleAbsolute(16, 16);
		return image;
	}
}
