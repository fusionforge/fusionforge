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
import java.util.Arrays;
import java.util.Collections;
import java.util.List;
import java.util.Timer;
import java.util.TimerTask;
import java.util.logging.Level;
import java.util.regex.Pattern;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Filtre de servlet pour le monitoring.
 * C'est la classe de ce filtre qui doit être déclarée dans le fichier web.xml de la webapp.
 * @author Emeric Vernat
 */
public class MonitoringFilter implements Filter {
	Collector collector;

	// Cette variable httpCounter conserve un état qui est global au filtre et à l'application (donc thread-safe).
	private Counter httpCounter;

	private boolean monitoringDisabled;
	private boolean logEnabled;
	private boolean log4jEnabled;
	private boolean contextFactoryEnabled;
	private Pattern urlExcludePattern;
	private Pattern allowedAddrPattern;
	private FilterConfig filterConfig;
	private Timer timer;

	/** {@inheritDoc} */
	public void init(FilterConfig config) throws ServletException {
		this.filterConfig = config;
		Parameters.initialize(config);
		monitoringDisabled = Boolean.parseBoolean(Parameters.getParameter(Parameter.DISABLED));
		if (monitoringDisabled) {
			return;
		}
		this.timer = new Timer("monitoring"
				+ config.getServletContext().getContextPath().replace('/', ' '), true);

		this.contextFactoryEnabled = !monitoringDisabled
				&& Boolean.parseBoolean(Parameters.getParameter(Parameter.CONTEXT_FACTORY_ENABLED));
		if (contextFactoryEnabled) {
			MonitoringInitialContextFactory.init();
		}

		final JdbcWrapper jdbcWrapper = JdbcDriver.SINGLETON.getJdbcWrapper();
		// si l'application a utilisé JdbcDriver avant d'initialiser ce filtre
		// (par exemple dans un listener de contexte), on doit récupérer son sqlCounter
		// car il est lié à une connexion jdbc qui est certainement conservée dans un pool
		// (sinon les requêtes sql sur cette connexion ne seront pas monitorées)
		final Counter sqlCounter = jdbcWrapper.getSqlCounter();
		// sqlCounter dans JdbcWrapper peut être alimenté soit par une datasource soit par un driver
		jdbcWrapper.initServletContext(config.getServletContext());
		jdbcWrapper.rebindDataSources();

		// liaison des compteurs : les contextes par thread du sqlCounter ont pour parent le httpCounter
		this.httpCounter = new Counter("http", "dbweb.png", sqlCounter);

		final String application = Parameters.getCurrentApplication();
		final Counter ejbCounter = MonitoringInterceptor.getEjbCounter();
		final List<Counter> counters = Arrays.asList(new Counter[] { httpCounter, sqlCounter,
				ejbCounter, });
		for (final Counter counter : counters) {
			// le paramètre pour ce nom de compteur doit exister
			final Parameter parameter = Parameter.valueOfIgnoreCase(counter.getName()
					+ "_TRANSFORM_PATTERN");
			if (Parameters.getParameter(parameter) != null) {
				final Pattern pattern = Pattern.compile(Parameters.getParameter(parameter));
				counter.setRequestTransformPattern(pattern);
			}
		}
		final String displayedCounters = Parameters.getParameter(Parameter.DISPLAYED_COUNTERS);
		// displayedCounters doit être traité avant l'initialisation du collector
		// sinon les dayCounters ne seront pas bons
		if (displayedCounters == null) {
			// par défaut, les compteurs http et sql sont affichés
			httpCounter.setDisplayed(true);
			sqlCounter.setDisplayed(true);
			ejbCounter.setDisplayed(false);
		} else {
			setDisplayedCounters(counters, displayedCounters);
		}
		this.collector = new Collector(application, counters, timer);
		logEnabled = Boolean.parseBoolean(Parameters.getParameter(Parameter.LOG));
		try {
			Class.forName("org.apache.log4j.Logger");
			log4jEnabled = true;
		} catch (final ClassNotFoundException e) {
			log4jEnabled = false;
		}

		if (Parameters.getParameter(Parameter.URL_EXCLUDE_PATTERN) != null) {
			// lance une PatternSyntaxException si la syntaxe du pattern est invalide
			urlExcludePattern = Pattern.compile(Parameters
					.getParameter(Parameter.URL_EXCLUDE_PATTERN));
		}
		if (Parameters.getParameter(Parameter.ALLOWED_ADDR_PATTERN) != null) {
			allowedAddrPattern = Pattern.compile(Parameters
					.getParameter(Parameter.ALLOWED_ADDR_PATTERN));
		}

		initCollect();
	}

	private void setDisplayedCounters(List<Counter> counters, String displayedCounters) {
		for (final Counter counter : counters) {
			counter.setDisplayed(false);
		}
		for (final String displayedCounter : displayedCounters.split(",")) {
			final String displayedCounterName = displayedCounter.trim();
			for (final Counter counter : counters) {
				if (displayedCounterName.equalsIgnoreCase(counter.getName())) {
					counter.setDisplayed(true);
					break;
				}
			}
		}
	}

	private void initCollect() {
		try {
			Class.forName("org.jrobin.core.RrdDb");
		} catch (final ClassNotFoundException e) {
			// si pas de jar jrobin, alors pas de collecte
			return;
		}

		final int periodMillis = Parameters.getResolutionSeconds() * 1000;
		// on schedule la tâche de fond
		final TimerTask task = new TimerTask() {
			/** {@inheritDoc} */
			@Override
			public void run() {
				// il ne doit pas y avoir d'erreur dans cette task
				collector.collectLocalContextWithoutErrors();
			}
		};
		timer.schedule(task, periodMillis, periodMillis);

		// on appelle la collecte pour que les instances jrobin soient définies
		// au cas où un graph de la page de monitoring soit demandé de suite
		collector.collectLocalContextWithoutErrors();

		if (Parameters.getParameter(Parameter.MAIL_SESSION) != null
				&& Parameters.getParameter(Parameter.ADMIN_EMAILS) != null) {
			MailReport.scheduleReportMail(collector, timer);
		}
	}

	/** {@inheritDoc} */
	public void destroy() {
		if (monitoringDisabled) {
			return;
		}
		try {
			// on rebind les dataSources initiales à la place des proxy
			JdbcDriver.SINGLETON.getJdbcWrapper().stop();
			JdbcDriver.SINGLETON.deregister();
		} finally {
			if (contextFactoryEnabled) {
				MonitoringInitialContextFactory.stop();
			}

			// on arrête le thread du collector,
			// on persiste les compteurs pour les relire à l'initialisation et ne pas perdre les stats
			// et on vide les compteurs
			timer.cancel();
			collector.stop();
			Collector.stopJRobin();

			// nettoyage avant le retrait de la webapp au cas où celui-ci ne suffise pas
			collector = null;
			httpCounter = null;
			urlExcludePattern = null;
			allowedAddrPattern = null;
			filterConfig = null;
			timer = null;
		}
	}

	/** {@inheritDoc} */
	public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
			throws IOException, ServletException {
		// pour tests
		//		try {
		//			final DataSource ds = (DataSource) new InitialContext()
		//					.lookup("java:comp/env/jdbc/TestDB");
		//			final Connection connection = ds.getConnection();
		//			try {
		//				connection.createStatement().executeQuery("select 1");
		//			} finally {
		//				connection.rollback();
		//				connection.close();
		//			}
		//		} catch (final Exception e) {
		//			throwException(e);
		//		}

		if (!(request instanceof HttpServletRequest) || !(response instanceof HttpServletResponse)
				|| monitoringDisabled) {
			// si ce n'est pas une requête http, on ne la monitore pas
			chain.doFilter(request, response);
			return;
		}
		final HttpServletRequest httpRequest = (HttpServletRequest) request;
		final HttpServletResponse httpResponse = (HttpServletResponse) response;

		if (isRequestExcluded(httpRequest)) {
			// si cette url est exclue, on ne monitore pas cette requête http
			chain.doFilter(request, response);
			return;
		}
		if (httpRequest.getRequestURI().endsWith("monitoring")) {
			if (isRequestNotAllowed(httpRequest)) {
				httpResponse.sendError(HttpServletResponse.SC_FORBIDDEN, "Accès interdit");
				return;
			}
			doMonitoring(httpRequest, httpResponse);
			return;
		}

		final CounterServletResponseWrapper wrappedResponse = new CounterServletResponseWrapper(
				httpResponse);
		final long start = System.currentTimeMillis();
		boolean systemError = false;
		try {
			JdbcWrapper.ACTIVE_THREAD_COUNT.incrementAndGet();
			// on binde le contexte de la requête http pour les requêtes sql
			httpCounter.bindContext();
			chain.doFilter(request, wrappedResponse);
			wrappedResponse.flushBuffer();
		} catch (final Throwable t) { // NOPMD
			// on catche Throwable pour avoir tous les cas d'erreur système
			systemError = true;
			throwException(t);
		} finally {
			try {
				JdbcWrapper.ACTIVE_THREAD_COUNT.decrementAndGet();

				// Si la durée est négative (arrive bien que rarement en cas de synchronisation d'horloge système),
				// alors on considère que la durée est 0.

				// Rq : sous Windows XP, currentTimeMillis a une résolution de 16ms environ
				// (discrètisation de la durée en 0, 16 ou 32 ms, etc ...)
				// et sous linux ou Windows Vista la résolution est bien meilleure.
				// On n'utilise pas nanoTime car il peut être un peu plus lent
				// (voir http://bugs.sun.com/bugdatabase/view_bug.do?bug_id=6440250)
				final long duration = Math.max(System.currentTimeMillis() - start, 0);

				// systemError est true s'il y a une exception envoyée par la servlet
				// ou si il y a un status d'erreur dans la réponse
				// (status comme 403 unauthorized, 404 not found ou 500 internal server error
				// mais pas 200 ok ou 304 not modified par exemple)
				systemError |= wrappedResponse.getStatus() >= 400;

				// taille du flux sortant
				final int responseSize = wrappedResponse.getDataLength();
				// nom identifiant la requête
				final String requestName = getRequestName(httpRequest, wrappedResponse);

				// on enregistre la requête dans les statistiques
				httpCounter.addRequest(requestName, duration, systemError, responseSize);
				if (logEnabled) {
					// on log sur Log4J ou java.util.logging dans la catégorie correspond au nom du filtre dans web.xml
					log(httpRequest, requestName, duration, systemError, responseSize);
				}
			} finally {
				// normalement le unbind du contexte a été fait dans httpCounter.addRequest
				// mais pour être sûr au cas où il y ait une exception comme OutOfMemoryError
				// on le refait ici pour éviter des erreurs par la suite,
				// car il ne doit pas y avoir de contexte restant au delà de la requête http
				httpCounter.unbindContext();
			}
		}
	}

	private void doMonitoring(HttpServletRequest httpRequest, HttpServletResponse httpResponse)
			throws IOException {
		final MonitoringController monitoringController = new MonitoringController(collector, false);
		monitoringController.executeActionIfNeeded(httpRequest);
		// javaInformations doit être réinstanciée et doit être après executeActionIfNeeded
		// pour avoir des informations à jour
		final JavaInformations javaInformations;
		if (monitoringController.isJavaInformationsNeeded(httpRequest)) {
			javaInformations = new JavaInformations(filterConfig.getServletContext(), true);
		} else {
			javaInformations = null;
		}
		monitoringController.doReport(httpRequest, httpResponse, Collections
				.singletonList(javaInformations));
	}

	private String getRequestName(HttpServletRequest httpRequest,
			CounterServletResponseWrapper wrappedResponse) {
		final String requestName;
		if (wrappedResponse.getStatus() == HttpServletResponse.SC_NOT_FOUND) {
			// Sécurité : si status http est 404, alors requestName est Error404
			// pour éviter de saturer la mémoire avec potentiellement beaucoup d'url différentes
			requestName = "Error404";
		} else {
			// on ne prend pas httpRequest.getPathInfo()
			// car requestURI == <context>/<servlet>/<pathInfo>,
			// et dans le cas où il y a plusieurs servlets (par domaine fonctionnel ou technique)
			// pathInfo ne contient pas l'indication utile de la servlet
			final String tmp = httpRequest.getRequestURI().substring(
					httpRequest.getContextPath().length());
			requestName = tmp + ' ' + httpRequest.getMethod();
		}
		return requestName;
	}

	private boolean isRequestExcluded(HttpServletRequest httpRequest) {
		return urlExcludePattern != null
				&& urlExcludePattern.matcher(
						httpRequest.getRequestURI()
								.substring(httpRequest.getContextPath().length())).matches();
	}

	private boolean isRequestNotAllowed(final HttpServletRequest httpRequest) {
		return allowedAddrPattern != null
				&& !allowedAddrPattern.matcher(httpRequest.getRemoteAddr()).matches();
	}

	// cette méthode est protected pour pouvoir être surchargée dans une classe définie par l'application
	@SuppressWarnings("unused")
	protected void log(HttpServletRequest httpRequest, String requestName, long duration,
			boolean systemError, int responseSize) {
		// dans les 2 cas, on ne construit le message de log
		// que si le logger est configuré pour écrire le niveau INFO
		final String filterName = filterConfig.getFilterName();
		if (log4jEnabled) {
			if (org.apache.log4j.Logger.getLogger(filterName).isInfoEnabled()) {
				org.apache.log4j.Logger.getLogger(filterName).info(
						buildLogMessage(httpRequest, duration, systemError, responseSize));
			}
		} else if (java.util.logging.Logger.getLogger(filterName).isLoggable(Level.INFO)) {
			java.util.logging.Logger.getLogger(filterName).info(
					buildLogMessage(httpRequest, duration, systemError, responseSize));
		}
	}

	private String buildLogMessage(HttpServletRequest httpRequest, long duration,
			boolean systemError, int responseSize) {
		final StringBuilder msg = new StringBuilder();
		msg.append("remoteAddr = ").append(httpRequest.getRemoteAddr());
		msg.append(", request = ").append(
				httpRequest.getRequestURI().substring(httpRequest.getContextPath().length()));
		if (httpRequest.getQueryString() != null) {
			msg.append('?').append(httpRequest.getQueryString());
		}
		msg.append(' ').append(httpRequest.getMethod());
		msg.append(": ").append(duration).append(" ms");
		if (systemError) {
			msg.append(", erreur");
		}
		msg.append(", ").append(responseSize / 1024).append(" Ko");
		return msg.toString();
	}

	private void throwException(Throwable t) throws IOException, ServletException {
		if (t instanceof Error) {
			throw (Error) t;
		} else if (t instanceof RuntimeException) {
			throw (RuntimeException) t;
		} else if (t instanceof IOException) {
			throw (IOException) t;
		} else if (t instanceof ServletException) {
			throw (ServletException) t;
		} else {
			// n'arrive à priori pas car chain.doFilter ne déclare que IOException et ServletException
			// mais au cas où
			throw new ServletException(t.getMessage(), t);
		}
	}
}
