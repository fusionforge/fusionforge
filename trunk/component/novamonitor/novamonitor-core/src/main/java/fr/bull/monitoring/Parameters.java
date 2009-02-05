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

import java.net.InetAddress;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.UnknownHostException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import javax.servlet.FilterConfig;
import javax.servlet.ServletContext;

/**
 * Classe d'accès aux paramètres.
 * @author evernat
 */
final class Parameters {
	static final String TEMPORARY_DIRECTORY = System.getProperty("java.io.tmpdir");
	static final String JAVA_VERSION = System.getProperty("java.version");
	// résolution (ou pas) par défaut en s de stockage des valeurs dans les fichiers RRD
	private static final int DEFAULT_RESOLUTION_SECONDS = 60;
	// stockage des fichiers RRD de JRobin dans le répertoire temp/monitoring/<context> par défaut
	private static final String DEFAULT_DIRECTORY = "monitoring";

	private static final String PREFIX = "monitoring.";

	private static FilterConfig filterConfig;
	private static ServletContext servletContext;

	private Parameters() {
		super();
	}

	static void initialize(FilterConfig config) {
		filterConfig = config;
		if (config != null) {
			initialize(config.getServletContext());
		}
	}

	static void initialize(ServletContext context) {
		if ("1.5".compareTo(JAVA_VERSION) > 0) {
			throw new IllegalStateException("La version java doit être 1.5 au minimum et non "
					+ JAVA_VERSION);
		}
		servletContext = context;
	}

	/**
	 * @return Contexte de servlet de la webapp, soit celle monitorée ou soit celle de collecte.
	 */
	static ServletContext getServletContext() {
		assert servletContext != null;
		return servletContext;
	}

	/**
	 * @return Nom et urls des applications telles que paramétrées dans un serveur de collecte.
	 * @throws MalformedURLException e
	 */
	static Map<String, List<URL>> getCollectorUrlsByApplications() throws MalformedURLException {
		// les paramètres de contexte de la webapp sur le serveur de collecte
		// contiennent les noms et les urls des applications à monitorer
		// par ex.: production=http://prod1:8080/myapp
		// ou alors les propriétés systèmes
		// par ex.: monitoring.production=http://prod1:8080/myapp
		@SuppressWarnings("unchecked")
		final List<String> parameterNames = Collections.list(getServletContext()
				.getInitParameterNames());
		final Map<String, List<URL>> urlsByApplication = new LinkedHashMap<String, List<URL>>();
		for (final String parameterName : parameterNames) {
			final String parameter = getServletContext().getInitParameter(parameterName);
			if (parameter.startsWith("http://") || parameter.startsWith("https://")) {
				urlsByApplication.put(parameterName, parseUrl(parameter));
			}
		}
		for (final String propertyName : System.getProperties().stringPropertyNames()) {
			if (propertyName.startsWith(PREFIX)) {
				final String property = System.getProperty(propertyName);
				if (property.startsWith("http://") || property.startsWith("https://")) {
					urlsByApplication.put(propertyName.substring(PREFIX.length()),
							parseUrl(property));
				}
			}
		}
		if (urlsByApplication.isEmpty()) {
			throw new IllegalStateException(
					"Les applications à monitorer doivent être définies comme paramètres de contexte de la webapp."
							+ "\nPar exemple dans un context tomcat :"
							+ "\n<Parameter name='test' value='http://localhost:8080/test/' override='false'/>"
							+ "\nou bien comme propriétés systèmes au lancement du serveur, par exemple :"
							+ "\n-Dmonitoring.test=http://localhost:8080/test/");
		}
		return Collections.unmodifiableMap(urlsByApplication);
	}

	private static List<URL> parseUrl(String value) throws MalformedURLException {
		// pour un cluster, le paramètre vaut "url1,url2"
		final List<URL> urls = new ArrayList<URL>();
		final TransportFormat transportFormat;
		if (Parameters.getParameter(Parameter.TRANSPORT_FORMAT) == null) {
			transportFormat = TransportFormat.SERIALIZED;
		} else {
			transportFormat = TransportFormat.valueOfIgnoreCase(Parameters
					.getParameter(Parameter.TRANSPORT_FORMAT));
		}
		final String suffix = "/monitoring?format="
				+ transportFormat.toString().toLowerCase(Locale.getDefault());

		for (final String s : value.split(",")) {
			final URL url = new URL(s.trim() + suffix);
			urls.add(url);
		}
		return urls;
	}

	/**
	 * @return nom réseau de la machine
	 */
	static String getHostName() {
		try {
			return InetAddress.getLocalHost().getHostName();
		} catch (final UnknownHostException ex) {
			return "unknown";
		}
	}

	/**
	 * @return adresse ip de la machine
	 */
	static String getHostAddress() {
		try {
			return InetAddress.getLocalHost().getHostAddress();
		} catch (final UnknownHostException ex) {
			return "unknown";
		}
	}

	/**
	 * @return Chemin complet d'une resource.
	 */
	static String getResourcePath(String fileName) {
		final Class<Parameters> classe = Parameters.class;
		final String packageName = classe.getName().substring(0,
				classe.getName().length() - classe.getSimpleName().length() - 1);
		return '/' + packageName.replace('.', '/') + "/resource/" + fileName;
	}

	/**
	 * @return Résolution en secondes des courbes et période d'appels par le serveur de collecte le cas échéant.
	 */
	static int getResolutionSeconds() {
		final String param = getParameter(Parameter.RESOLUTION_SECONDS);
		if (param != null) {
			// lance une NumberFormatException si ce n'est pas un nombre
			final int result = Integer.parseInt(param);
			if (result <= 0) {
				throw new IllegalStateException(
						"Le paramètre resolution-seconds doit être > 0 (entre 60 et 600 recommandé)");
			}
			return result;
		}
		return DEFAULT_RESOLUTION_SECONDS;
	}

	/**
	 * @return Répertoire de stockage des compteurs et des données pour les courbes.
	 */
	static String getStorageDirectory(String application) {
		final String param = getParameter(Parameter.STORAGE_DIRECTORY);
		final String dir;
		if (param == null) {
			dir = DEFAULT_DIRECTORY;
		} else {
			dir = param;
		}
		// Si le nom du répertoire commence par '/', on considère que c'est un chemin absolu,
		// sinon on considère que c'est un chemin relatif par rapport au répertoire temporaire
		// ('temp' dans TOMCAT_HOME pour tomcat).
		final String directory;
		if (dir.length() > 0 && dir.charAt(0) == '/') {
			directory = dir;
		} else {
			directory = TEMPORARY_DIRECTORY + '/' + dir;
		}
		if (servletContext != null) {
			return directory + '/' + application;
		}
		return directory;
	}

	/**
	 * @return Nom de l'application courante et nom du sous-répertoire de stockage dans une application monitorée.
	 */
	static String getCurrentApplication() {
		if (servletContext != null) {
			// Le nom de l'application et donc le stockage des fichiers est dans le sous-répertoire
			// ayant pour nom le contexte de la webapp et le nom du serveur
			// pour pouvoir monitorer plusieurs webapps sur le même serveur et
			// pour pouvoir stocker sur un répertoire partagé entre plusieurs serveurs
			return servletContext.getContextPath() + '_' + getHostName();
		}
		return null;
	}

	/**
	 * Recherche la valeur d'un paramètre qui peut être défini par ordre de priorité croissant :
	 * - dans les paramètres d'initialisation du filtre (fichier web.xml dans la webapp)
	 * - dans les paramètres du contexte de la webapp avec le préfixe "monitoring." (fichier xml de contexte dans Tomcat)
	 * - dans les propriétés systèmes avec le préfixe "monitoring." (commande de lancement java)
	 * @param name Enum du paramètre
	 * @return valeur du paramètre ou null si pas de paramètre défini
	 */
	static String getParameter(Parameter parameter) {
		assert parameter != null;
		final String name = parameter.getCode();
		final String globalName = PREFIX + name;
		String result = System.getProperty(globalName);
		if (result != null) {
			return result;
		}
		if (servletContext != null) {
			result = servletContext.getInitParameter(globalName);
			if (result != null) {
				return result;
			}
		}
		if (filterConfig != null) {
			result = filterConfig.getInitParameter(name);
			if (result != null) {
				return result;
			}
		}
		return null;
	}
}
