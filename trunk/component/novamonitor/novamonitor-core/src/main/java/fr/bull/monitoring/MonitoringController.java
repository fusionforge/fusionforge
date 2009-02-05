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

import java.io.BufferedInputStream;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Contrôleur au sens MVC de l'ihm de monitoring.
 * @author Emeric Vernat
 */
class MonitoringController {
	static final String ACTION_PARAMETER = "action";
	private static final String COUNTER_PARAMETER = "counter";
	private static final String PERIOD_PARAMETER = "period";
	private static final String GRAPH_PARAMETER = "graph";
	private static final String GRAPH_DETAIL_PARAMETER = "graphDetail";
	private static final String RESOURCE_PARAMETER = "resource";
	private static final String FORMAT_PARAMETER = "format";
	private static final String WIDTH_PARAMETER = "width";
	private static final String HEIGHT_PARAMETER = "height";
	private final Collector collector;
	private final boolean collectorServer;
	private String messageForReport;

	MonitoringController(Collector collector, boolean collectorServer) {
		super();
		assert collector != null;
		this.collector = collector;
		this.collectorServer = collectorServer;
	}

	boolean isJavaInformationsNeeded(HttpServletRequest httpRequest) {
		return httpRequest.getParameter(RESOURCE_PARAMETER) == null
				&& httpRequest.getParameter(GRAPH_PARAMETER) == null
				&& httpRequest.getParameter(GRAPH_DETAIL_PARAMETER) == null;
	}

	void executeActionIfNeeded(HttpServletRequest httpRequest) {
		assert httpRequest != null;
		final String actionParameter = httpRequest.getParameter(ACTION_PARAMETER);
		if (actionParameter != null) {
			if (!Boolean.parseBoolean(Parameters.getParameter(Parameter.SYSTEM_ACTIONS_ENABLED))) {
				// par sécurité
				throw new IllegalStateException("Actions systèmes non activées");
			}
			final Action action = Action.valueOfIgnoreCase(actionParameter);
			final String counterName = httpRequest.getParameter(COUNTER_PARAMETER);
			messageForReport = action.execute(collector, counterName);
		}
	}

	void doReport(HttpServletRequest httpRequest, HttpServletResponse httpResponse,
			List<JavaInformations> javaInformationsList) throws IOException {
		assert httpRequest != null;
		assert httpResponse != null;
		assert javaInformationsList != null;

		final String resource = httpRequest.getParameter(RESOURCE_PARAMETER);
		if (resource != null) {
			doResource(httpResponse, resource);
			return;
		}

		// dans tous les cas sauf resource,
		// il n'y a pas de cache navigateur (sur la page html, les courbes ou le flux sérialisé)
		httpResponse.addHeader("Cache-Control", "no-cache");
		httpResponse.addHeader("Pragma", "no-cache");
		httpResponse.addHeader("Expires", "-1");

		final String period;
		if (httpRequest.getParameter(PERIOD_PARAMETER) == null) {
			period = "jour";
		} else {
			period = httpRequest.getParameter(PERIOD_PARAMETER);
		}
		final String graph = httpRequest.getParameter(GRAPH_PARAMETER);
		if (graph != null) {
			doGraph(httpRequest, httpResponse, period, graph);
			return;
		}
		final String graphDetail = httpRequest.getParameter(GRAPH_DETAIL_PARAMETER);
		if (graphDetail != null) {
			doGraphDetail(httpResponse, period, graphDetail);
			return;
		}
		final String format = httpRequest.getParameter(FORMAT_PARAMETER);
		if (format == null || "html".equalsIgnoreCase(format)) {
			doCompressedHtml(httpRequest, httpResponse, javaInformationsList, period);
		} else if ("pdf".equalsIgnoreCase(format)) {
			doPdf(httpRequest, httpResponse, period, javaInformationsList);
		} else {
			// l'appelant (un serveur d'agrégation par exemple) peut appeler
			// la page monitoring avec un format "serialized" ou "xml" en paramètre
			// pour avoir les données au format sérialisé java ou xml
			final TransportFormat transportFormat = TransportFormat.valueOfIgnoreCase(format);
			final Serializable serializable = createSerializable(javaInformationsList);
			httpResponse.setContentType(transportFormat.getMimeType());
			transportFormat.writeSerializableTo(serializable, httpResponse.getOutputStream());

			// on a été appelé par un serveur de collecte qui fera l'aggrégation dans le temps,
			// le stockage et les courbes, donc on arrête le timer s'il est démarré
			// et on vide les stats pour que le serveur de collecte ne récupère que les deltas
			collector.stop();
		}
	}

	private void doCompressedHtml(HttpServletRequest httpRequest, HttpServletResponse httpResponse,
			List<JavaInformations> javaInformationsList, String period) throws IOException {
		if (isCompressionSupported(httpRequest)) {
			// comme la page html peut être volumineuse avec toutes les requêtes sql et http
			// on compresse le flux de réponse en gzip (à moins que la compression http
			// ne soit pas supportée comme par ex s'il y a un proxy squid qui ne supporte que http 1.0)
			final CompressionServletResponseWrapper wrappedResponse = new CompressionServletResponseWrapper(
					httpResponse, 4096);
			try {
				doHtml(wrappedResponse, period, javaInformationsList);
			} finally {
				wrappedResponse.finishResponse();
			}
		} else {
			doHtml(httpResponse, period, javaInformationsList);
		}
	}

	private Serializable createSerializable(List<JavaInformations> javaInformationsList) {
		final List<Serializable> serialized = new ArrayList<Serializable>();
		// on clone les counters avant de les sérialiser pour ne pas avoir de problèmes de concurrences d'accès
		for (final Counter counter : collector.getCounters()) {
			serialized.add(counter.clone());
		}
		for (final JavaInformations javaInformations : javaInformationsList) {
			serialized.add(javaInformations);
		}
		return (Serializable) serialized;
	}

	private void doHtml(HttpServletResponse httpResponse, String period,
			List<JavaInformations> javaInformationsList) throws IOException {
		if (!collectorServer) {
			// avant de faire l'affichage on fait une collecte,  pour que les courbes
			// et les compteurs par jour soit à jour avec les dernières requêtes
			collector.collectLocalContextWithoutErrors();
		}

		// simple appel de monitoring sans format
		httpResponse.setContentType("text/html; charset=ISO-8859-1");
		final BufferedWriter writer = new BufferedWriter(httpResponse.getWriter());
		try {
			final HtmlReport htmlReport = new HtmlReport(collector, collectorServer,
					javaInformationsList, period, writer);
			htmlReport.toHtml(messageForReport);
		} finally {
			writer.close();
		}
	}

	private void doPdf(HttpServletRequest httpRequest, HttpServletResponse httpResponse,
			String period, List<JavaInformations> javaInformationsList) throws IOException {
		if (!collectorServer) {
			// avant de faire l'affichage on fait une collecte,  pour que les courbes
			// et les compteurs par jour soit à jour avec les dernières requêtes
			collector.collectLocalContextWithoutErrors();
		}

		// simple appel de monitoring sans format
		httpResponse.setContentType("application/pdf");
		httpResponse.addHeader("Content-Disposition", encodeFileNameToContentDisposition(
				httpRequest, PdfReport.getFileName(collector.getApplication())));
		try {
			final PdfReport pdfReport = new PdfReport(collector, javaInformationsList, period,
					httpResponse.getOutputStream());
			pdfReport.toPdf();
		} finally {
			httpResponse.getOutputStream().flush();
		}
	}

	/**
	 * Encode un nom de fichier avec des % pour Content-Disposition, avec téléchargement.
	 * (US-ASCII + Encode-Word : http://www.ietf.org/rfc/rfc2183.txt, http://www.ietf.org/rfc/rfc2231.txt
	 * sauf en MS IE qui ne supporte pas cet encodage et qui n'en a pas besoin)
	 * @param httpRequest HttpServletRequest
	 * @param fileName String
	 * @return String
	 */
	private String encodeFileNameToContentDisposition(HttpServletRequest httpRequest,
			String fileName) {
		assert fileName != null;
		final String userAgent = httpRequest.getHeader("user-agent");
		if (userAgent != null && userAgent.contains("MSIE")) {
			return "attachment;filename=" + fileName;
		}
		return encodeFileNameToStandardContentDisposition(fileName);
	}

	private String encodeFileNameToStandardContentDisposition(String fileName) {
		final int length = fileName.length();
		final StringBuilder sb = new StringBuilder(length + length / 4);
		// attachment et non inline pour proposer l'enregistrement (sauf IE6)
		// et non l'affichage direct dans le navigateur
		sb.append("attachment;filename*=\"");
		char c;
		for (int i = 0; i < length; i++) {
			c = fileName.charAt(i);
			if (c >= 'a' && c <= 'z' || c >= 'A' && c <= 'Z' || c >= '0' && c <= '9') {
				sb.append(c);
			} else {
				sb.append('%');
				if (c < 16) {
					sb.append('0');
				}
				sb.append(Integer.toHexString(c));
			}
		}
		sb.append('"');
		return sb.toString();
	}

	private void doResource(HttpServletResponse httpResponse, String resource) throws IOException {
		httpResponse.addHeader("Cache-Control", "max-age=3600"); // cache navigateur 1h
		final OutputStream out = httpResponse.getOutputStream();
		// on enlève tout ".." dans le paramètre par sécurité
		final String localResource = Parameters.getResourcePath(resource.replace("..", ""));
		// ce contentType est nécessaire sinon la css n'est pas prise en compte
		// sous firefox sur un serveur distant
		httpResponse.setContentType(Parameters.getServletContext().getMimeType(localResource));
		final InputStream in = new BufferedInputStream(getClass()
				.getResourceAsStream(localResource));
		try {
			final byte[] bytes = new byte[4 * 1024];
			int length = in.read(bytes);
			while (length != -1) {
				out.write(bytes, 0, length);
				length = in.read(bytes);
			}
		} finally {
			in.close();
		}
	}

	private void doGraph(HttpServletRequest httpRequest, HttpServletResponse httpResponse,
			String period, String graph) throws IOException {
		final int width = Math.min(Integer.parseInt(httpRequest.getParameter(WIDTH_PARAMETER)),
				1600);
		final int height = Math.min(Integer.parseInt(httpRequest.getParameter(HEIGHT_PARAMETER)),
				1600);
		final byte[] img = collector.graph(graph, period, width, height);
		if (img != null) {
			// png comme indiqué dans la classe jrobin
			httpResponse.setContentType("image/png");
			httpResponse.setContentLength(img.length);
			final String fileName = graph + ".png";
			httpResponse.addHeader("Content-Disposition", "inline;filename=" + fileName);
			httpResponse.getOutputStream().write(img);
			httpResponse.flushBuffer();
		}
	}

	private void doGraphDetail(HttpServletResponse httpResponse, String period, String graphDetail)
			throws IOException {
		httpResponse.setContentType("text/html; charset=ISO-8859-1");
		final BufferedWriter writer = new BufferedWriter(httpResponse.getWriter());
		try {
			final List<JavaInformations> javaInformationsList = Collections.emptyList();
			final HtmlReport htmlReport = new HtmlReport(collector, collectorServer,
					javaInformationsList, period, writer);
			htmlReport.writeGraphDetail(graphDetail);
		} finally {
			writer.close();
		}
	}

	private boolean isCompressionSupported(HttpServletRequest httpRequest) {
		// est-ce que le navigateur déclare accepter la compression gzip ?
		boolean supportCompression = false;
		@SuppressWarnings("unchecked")
		final List<String> acceptEncodings = Collections.list(httpRequest
				.getHeaders("Accept-Encoding"));
		for (final String name : acceptEncodings) {
			if (name.contains("gzip")) {
				supportCompression = true;
				break;
			}
		}
		return supportCompression;
	}
}
