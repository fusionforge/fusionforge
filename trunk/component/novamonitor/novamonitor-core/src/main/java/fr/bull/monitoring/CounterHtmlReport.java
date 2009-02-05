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
import java.util.Arrays;
import java.util.List;

/**
 * Partie du rapport html pour un compteur.
 * @author Emeric Vernat
 */
class CounterHtmlReport {
	private final Counter counter;
	private final String period;
	private final Writer writer;
	private final CounterRequestAggregation counterRequestAggregation;
	private final DecimalFormat systemErrorFormat = new DecimalFormat("0.00");
	private final DecimalFormat integerFormat = new DecimalFormat("#,##0");

	CounterHtmlReport(Counter counter, String period, Writer writer) {
		super();
		assert counter != null;
		assert period != null;
		assert writer != null;
		this.counter = counter;
		this.period = period;
		this.writer = writer;
		this.counterRequestAggregation = new CounterRequestAggregation(counter);
	}

	void toHtml() throws IOException {
		final List<CounterRequest> requests = counterRequestAggregation.getRequests();
		if (requests.isEmpty()) {
			writeln("Aucune requête");
			return;
		}
		final CounterRequest globalRequest = counterRequestAggregation.getGlobalRequest();
		final List<CounterRequest> summaryRequests = Arrays.asList(new CounterRequest[] {
				globalRequest, counterRequestAggregation.getWarningRequest(),
				counterRequestAggregation.getSevereRequest(), });
		writeRequests(globalRequest.getName(), counter.getChildCounterName(), summaryRequests,
				false);

		// 2. débit et liens
		final String counterName = counter.getName();
		final long hitsParMinute = 60 * 1000 * globalRequest.getHits()
				/ (System.currentTimeMillis() - counter.getStartDate().getTime());
		writeln("<div align='right'>");
		writeln(integerFormat.format(hitsParMinute) + " hits/min sur "
				+ integerFormat.format(requests.size()) + " requêtes");
		writeln("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		writeln("<a href=\"javascript:showHide('details" + counterName
				+ "');\" class='noPrint'>+/- Détails</a>");
		writeln("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		if (Period.valueOfIgnoreCase(period) == Period.TOUT) {
			writeln("<a href='?period=tout&amp;action=clear_counter&amp;counter="
					+ counterName
					+ "' class='noPrint' onclick=\"javascript:return confirm('Confirmez-vous la réinitialisation des statistiques "
					+ counterName + " ?');\">Réinitialiser</a>");
		}
		writeln("</div>");

		// 3. détails par requêtes (non visible par défaut)
		writeln("<div id='details" + counterName + "' style='display: none;'>");
		writeRequests(counterName, counter.getChildCounterName(), requests, true);
		writeln("</div>");
	}

	private void writeRequests(String tableName, String childCounterName,
			List<CounterRequest> requestList, boolean includeGraph) throws IOException {
		assert requestList != null;
		writeln("<table class='sortable' width='100%' border='1' cellspacing='0' cellpadding='2' summary='"
				+ tableName + "'>");
		write("<thead><tr><th>Requête</th>");
		write("<th class='sorttable_numeric'>% du temps cumulé</th><th class='sorttable_numeric'>Hits</th><th class='sorttable_numeric'>Temps moyen (ms)</th><th class='sorttable_numeric'>Temps max (ms)</th><th class='sorttable_numeric'>Ecart-type</th><th class='sorttable_numeric'>% d'erreur système</th>");
		if (counterRequestAggregation.isResponseSizeDisplayed()) {
			write("<th class='sorttable_numeric'>Taille moyenne (Ko)</th>");
		}
		if (counterRequestAggregation.isChildHitsDisplayed()) {
			write("<th class='sorttable_numeric'>Hits " + childCounterName
					+ " moyens</th><th class='sorttable_numeric'>Temps " + childCounterName
					+ " moyen (ms)</th>");
		}
		writeln("</tr></thead><tbody>");
		boolean odd = false;
		for (final CounterRequest request : requestList) {
			if (odd) {
				write("<tr class='odd'>");
			} else {
				write("<tr>");
			}
			odd = !odd; // NOPMD
			writeRequest(request, includeGraph);
			writeln("</tr>");
		}
		writeln("</tbody></table>");
	}

	private void writeRequest(CounterRequest request, boolean includeGraph) throws IOException {
		final String nextColumn = "</td> <td align='right'>";
		write("<td>");
		if (includeGraph) {
			writeRequestGraph(request);
		} else {
			write(htmlEncode(request.getName()));
		}
		write(nextColumn);
		final CounterRequest globalRequest = counterRequestAggregation.getGlobalRequest();
		if (globalRequest.getDurationsSum() == 0) {
			write("0");
		} else {
			write(integerFormat.format((int) (100 * request.getDurationsSum() / globalRequest
					.getDurationsSum())));
		}
		write(nextColumn);
		write(integerFormat.format(request.getHits()));
		write(nextColumn);
		final int mean = request.getMean();
		write("<span class='");
		write(getSlaHtmlClass(mean));
		write("'>");
		write(integerFormat.format(mean));
		write("</span>");
		write(nextColumn);
		write(integerFormat.format(request.getMaximum()));
		write(nextColumn);
		write(integerFormat.format(request.getStandardDeviation()));
		write(nextColumn);
		write(systemErrorFormat.format(request.getSystemErrorPercentage()));
		if (counterRequestAggregation.isResponseSizeDisplayed()) {
			write(nextColumn);
			write(integerFormat.format(request.getResponseSizeMean() / 1024));
		}
		if (counterRequestAggregation.isChildHitsDisplayed()) {
			write(nextColumn);
			write(integerFormat.format(request.getChildHitsMean()));
			write(nextColumn);
			write(integerFormat.format(request.getChildDurationsMean()));
		}
		write("</td>");
	}

	private void writeRequestGraph(CounterRequest request) throws IOException {
		final String requestId = request.getId();
		// la classe tooltip est configurée dans la css de HtmlReport
		write("<a class='tooltip' href='?graphDetail=");
		write(requestId);
		write("&amp;period=");
		write(period);
		write("'");
		// ce onmouseover sert à charger les graphs par requête un par un et à la demande
		// sans les charger tous au chargement de la page.
		// le onmouseover se désactive après chargement pour ne pas recharger une image déjà chargée
		write(" onmouseover=\"document.getElementById('");
		write(requestId);
		write("').src='?graph=");
		write(requestId);
		write("&amp;period=");
		write(period);
		write("&amp;width=100&amp;height=50'; this.onmouseover=null;\" >");
		// avant mouseover on prend une image qui sera mise en cache
		write("<em><img src='?resource=db.png' id='");
		write(requestId);
		write("' alt='graph'/></em>");
		write(htmlEncode(request.getName()));
		write("</a>");
	}

	private String getSlaHtmlClass(int mean) {
		final String color;
		if (mean < counterRequestAggregation.getWarningThreshold()) {
			// si cette moyenne est < à la moyenne globale + 1 écart-type (paramétrable), c'est bien
			color = "info";
		} else if (mean < counterRequestAggregation.getSevereThreshold()) {
			// sinon, si cette moyenne est < à la moyenne globale + 2 écart-types (paramétrable),
			// attention à cette requête qui est plus longue que les autres
			color = "warning";
		} else {
			// sinon, (cette moyenne est > à la moyenne globale + 2 écart-types),
			// cette requête est très longue par rapport aux autres ;
			// il peut être opportun de l'optimiser si possible
			color = "severe";
		}
		return color;
	}

	private String htmlEncode(String text) {
		return text.replaceAll("[\n]", "<br/>").replaceAll("[&]", "&amp;")
				.replaceAll("[<]", "&lt;").replaceAll("[>]", "&gt;");
	}

	private void write(String html) throws IOException {
		writer.write(html);
	}

	private void writeln(String html) throws IOException {
		writer.write(html);
		writer.write('\n');
	}
}
