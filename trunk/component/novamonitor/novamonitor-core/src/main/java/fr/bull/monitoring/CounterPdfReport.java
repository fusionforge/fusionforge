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
import java.io.IOException;
import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

import com.lowagie.text.BadElementException;
import com.lowagie.text.Document;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Element;
import com.lowagie.text.Font;
import com.lowagie.text.FontFactory;
import com.lowagie.text.Image;
import com.lowagie.text.Paragraph;
import com.lowagie.text.Phrase;
import com.lowagie.text.pdf.PdfPCell;
import com.lowagie.text.pdf.PdfPTable;

/**
 * Partie du rapport pdf pour un compteur.
 * @author Emeric Vernat
 */
class CounterPdfReport {
	private final Collector collector;
	private final Counter counter;
	private final String period;
	private final Document document;
	private final CounterRequestAggregation counterRequestAggregation;
	private final DecimalFormat systemErrorFormat = new DecimalFormat("0.00");
	private final DecimalFormat integerFormat = new DecimalFormat("#,##0");
	private final Font cellFont;
	private final Font infoCellFont;
	private final Font warningCellFont;
	private final Font severeCellFont;
	private PdfPTable currentTable;

	CounterPdfReport(Collector collector, Counter counter, String period, Document document) {
		super();
		assert collector != null;
		assert counter != null;
		assert period != null;
		assert document != null;
		this.collector = collector;
		this.counter = counter;
		this.period = period;
		this.document = document;
		this.counterRequestAggregation = new CounterRequestAggregation(counter);
		this.cellFont = getFont(6, Font.NORMAL);
		this.infoCellFont = FontFactory.getFont(FontFactory.HELVETICA, 6, Font.NORMAL, Color.GREEN);
		this.warningCellFont = FontFactory.getFont(FontFactory.HELVETICA, 6, Font.BOLD,
				Color.ORANGE);
		this.severeCellFont = FontFactory.getFont(FontFactory.HELVETICA, 6, Font.BOLD, Color.RED);
	}

	void toPdf() throws DocumentException, IOException {
		final Font font = getFont(8, Font.NORMAL);
		final List<CounterRequest> requests = counterRequestAggregation.getRequests();
		if (requests.isEmpty()) {
			document.add(new Phrase("\nAucune requête\n", font));
		} else {
			final CounterRequest globalRequest = counterRequestAggregation.getGlobalRequest();
			final List<CounterRequest> summaryRequests = Arrays.asList(new CounterRequest[] {
					globalRequest, counterRequestAggregation.getWarningRequest(),
					counterRequestAggregation.getSevereRequest(), });
			writeRequests(counter.getChildCounterName(), summaryRequests, false);

			// 2. débit et liens
			final long hitsParMinute = 60 * 1000 * globalRequest.getHits()
					/ (System.currentTimeMillis() - counter.getStartDate().getTime());
			final Paragraph footer = new Paragraph(integerFormat.format(hitsParMinute)
					+ " hits/min sur " + integerFormat.format(requests.size()) + " requêtes", font);
			footer.setAlignment(Element.ALIGN_RIGHT);
			document.add(footer);
		}
	}

	String getCounterName() {
		return counter.getName();
	}

	String getCounterIconName() {
		return counter.getIconName();
	}

	void writeRequestDetails() throws DocumentException, IOException {
		// détails par requêtes
		final List<CounterRequest> requests = counterRequestAggregation.getRequests();
		if (requests.isEmpty()) {
			final Font font = getFont(8, Font.NORMAL);
			document.add(new Phrase("\nAucune requête\n", font));
		} else {
			// on n'inclue pas pour l'instant les graphs d'évolution des requêtes
			// pour des raisons de place et de volume
			writeRequests(counter.getChildCounterName(), requests, false);
		}
	}

	private void writeRequests(String childCounterName, List<CounterRequest> requestList,
			boolean includeGraph) throws DocumentException, IOException {
		assert requestList != null;
		final List<String> headers = new ArrayList<String>();
		headers.add("Requête");
		if (includeGraph) {
			headers.add("Evolution");
		}
		headers.add("% du temps cumulé");
		headers.add("Hits");
		headers.add("Temps moyen (ms)");
		headers.add("Temps max (ms)");
		headers.add("Ecart-type");
		headers.add("% d'erreur système");
		if (counterRequestAggregation.isResponseSizeDisplayed()) {
			headers.add("Taille moyenne (Ko)");
		}
		if (counterRequestAggregation.isChildHitsDisplayed()) {
			headers.add("Hits " + childCounterName + " moyens");
			headers.add("Temps " + childCounterName + " moyen (ms)");
		}
		currentTable = new PdfPTable(headers.size());
		currentTable.setWidthPercentage(100);
		final int[] relativeWidths = new int[headers.size()];
		Arrays.fill(relativeWidths, 0, headers.size(), 1);
		relativeWidths[0] = 10; // requête
		if (includeGraph) {
			relativeWidths[0] = 8;
			relativeWidths[1] = 2; // graph d'évolution
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
		for (final CounterRequest request : requestList) {
			if (odd) {
				getDefaultCell().setGrayFill(0.97f);
			} else {
				getDefaultCell().setGrayFill(1);
			}
			odd = !odd; // NOPMD
			writeRequest(request, includeGraph);
		}
		document.add(currentTable);
	}

	private void writeRequest(CounterRequest request, boolean includeGraph)
			throws BadElementException, IOException {
		getDefaultCell().setHorizontalAlignment(Element.ALIGN_LEFT);
		final String name = request.getName();
		if (name.length() > 1000) {
			// si la requête fait plus de 1000 caractères, on la coupe pour y voir quelque chose
			addCell(name.substring(0, 1000) + "...");
		} else {
			addCell(name);
		}
		if (includeGraph) {
			writeRequestGraph(request);
		}
		getDefaultCell().setHorizontalAlignment(Element.ALIGN_RIGHT);
		final CounterRequest globalRequest = counterRequestAggregation.getGlobalRequest();
		if (globalRequest.getDurationsSum() == 0) {
			addCell("0");
		} else {
			addCell(integerFormat.format((int) (100 * request.getDurationsSum() / globalRequest
					.getDurationsSum())));
		}
		addCell(integerFormat.format(request.getHits()));
		final int mean = request.getMean();
		currentTable.addCell(new Phrase(integerFormat.format(mean), getSlaFont(mean)));
		addCell(integerFormat.format(request.getMaximum()));
		addCell(integerFormat.format(request.getStandardDeviation()));
		addCell(systemErrorFormat.format(request.getSystemErrorPercentage()));
		if (counterRequestAggregation.isResponseSizeDisplayed()) {
			addCell(integerFormat.format(request.getResponseSizeMean() / 1024));
		}
		if (counterRequestAggregation.isChildHitsDisplayed()) {
			addCell(integerFormat.format(request.getChildHitsMean()));
			addCell(integerFormat.format(request.getChildDurationsMean()));
		}
	}

	private void writeRequestGraph(CounterRequest request) throws BadElementException, IOException {
		final byte[] graph = collector.graph(request.getId(), period, 100, 50);
		if (graph == null) {
			addCell("");
		} else {
			final Image image = Image.getInstance(graph);
			image.scalePercent(50);
			currentTable.addCell(image);
		}
	}

	private Font getSlaFont(int mean) {
		final Font font;
		if (mean < counterRequestAggregation.getWarningThreshold()) {
			// si cette moyenne est < à la moyenne globale + 1 écart-type (paramétrable), c'est bien
			font = infoCellFont;
		} else if (mean < counterRequestAggregation.getSevereThreshold()) {
			// sinon, si cette moyenne est < à la moyenne globale + 2 écart-types (paramétrable),
			// attention à cette requête qui est plus longue que les autres
			font = warningCellFont;
		} else {
			// sinon, (cette moyenne est > à la moyenne globale + 2 écart-types),
			// cette requête est très longue par rapport aux autres ;
			// il peut être opportun de l'optimiser si possible
			font = severeCellFont;
		}
		return font;
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
