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
import java.io.InputStream;
import java.io.Serializable;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLConnection;
import java.util.zip.GZIPInputStream;

/**
 * Classe permettant d'ouvrir une connexion http et de récupérer les objets java sérialisés dans la réponse.
 * Utilisée dans le serveur de collecte.
 * @author Emeric Vernat
 */
class LabradorRetriever {
	/** Timeout des connections serveur en millisecondes (0 : pas de timeout). */
	private static final int CONNECTION_TIMEOUT = 20000;

	/** Timeout de lecture des connections serveur en millisecondes (0 : pas de timeout). */
	private static final int READ_TIMEOUT = 60000;

	private final URL url;

	// Rq: les configurations suivantes sont celles par défaut, on ne les change pas
	//	    static { HttpURLConnection.setFollowRedirects(true);
	//	    URLConnection.setDefaultAllowUserInteraction(true); }

	LabradorRetriever(URL url) {
		super();
		assert url != null;
		this.url = url;
	}

	Serializable call() throws IOException, ClassNotFoundException {
		final URLConnection connection = openConnection(url);
		// Rq: on ne gère pas pour l'instant les éventuels cookie de session http,
		// puisque le filtre de monitoring n'est pas censé créer des sessions
		//		if (cookie != null) { connection.setRequestProperty("Cookie", cookie); }

		connection.connect();

		//		final String setCookie = connection.getHeaderField("Set-Cookie");
		//		if (setCookie != null) { cookie = setCookie; }
		final Serializable result = read(connection);

		if (result instanceof RuntimeException) {
			throw (RuntimeException) result;
		} else if (result instanceof Error) {
			throw (Error) result;
		} else if (result instanceof IOException) {
			throw (IOException) result;
		} else if (result instanceof ClassNotFoundException) {
			throw (ClassNotFoundException) result;
		} else if (result instanceof Exception) {
			final Exception e = (Exception) result;
			// Rq: le constructeur de IOException avec message et cause n'existe qu'en jdk 1.6
			final IOException ex = new IOException(e.getMessage());
			ex.initCause(e);
			throw ex;
		}
		return result;
	}

	/**
	 * Ouvre la connection http.
	 * @param url URL
	 * @return Object
	 * @throws IOException   Exception de communication
	 */
	private static URLConnection openConnection(URL url) throws IOException {
		final URLConnection connection = url.openConnection();
		connection.setUseCaches(false);
		if (CONNECTION_TIMEOUT > 0) {
			connection.setConnectTimeout(CONNECTION_TIMEOUT);
		}
		if (READ_TIMEOUT > 0) {
			connection.setReadTimeout(READ_TIMEOUT);
		}
		connection.setRequestProperty("Accept-Encoding", "gzip");
		return connection;
	}

	/**
	 * Lit l'objet renvoyé dans le flux de réponse.
	 * @return Object
	 * @param connection URLConnection
	 * @throws IOException   Exception de communication
	 * @throws ClassNotFoundException   Une classe transmise par le serveur n'a pas été trouvée
	 */
	private static Serializable read(URLConnection connection) throws IOException,
			ClassNotFoundException {
		InputStream input = connection.getInputStream();
		try {
			if ("gzip".equals(connection.getContentEncoding())) {
				input = new GZIPInputStream(input);
			}
			final String contentType = connection.getContentType();
			final TransportFormat transportFormat;
			if (contentType != null && contentType.startsWith("text/xml")) {
				transportFormat = TransportFormat.XML;
			} else {
				transportFormat = TransportFormat.SERIALIZED;
			}
			return transportFormat.readSerializableFrom(input);
		} finally {
			// ce close doit être fait en finally
			// (http://java.sun.com/j2se/1.5.0/docs/guide/net/http-keepalive.html)
			input.close();

			if (connection instanceof HttpURLConnection) {
				final InputStream error = ((HttpURLConnection) connection).getErrorStream();
				if (error != null) {
					error.close();
				}
			}
		}
	}
}
