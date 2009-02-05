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
import java.io.Serializable;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Timer;
import java.util.TimerTask;
import java.util.regex.Pattern;

import javax.servlet.ServletConfig;
import javax.servlet.ServletException;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Servlet de collecte utilisée uniquement pour serveur de collecte séparé de l'application monitorée.
 * @author Emeric Vernat
 */
public class CollectorServlet extends HttpServlet {
	private static final String COOKIE_NAME = "monitoring";

	private static final long serialVersionUID = -2070469677921953224L;

	@SuppressWarnings("all")
	private final Map<String, List<URL>> urlsByApplication = new LinkedHashMap<String, List<URL>>();
	@SuppressWarnings("all")
	private final Map<String, Collector> collectorsByApplication = new LinkedHashMap<String, Collector>();
	@SuppressWarnings("all")
	private final Map<String, List<JavaInformations>> javaInformationsByApplication = new LinkedHashMap<String, List<JavaInformations>>();

	private transient Timer timer;
	private Pattern allowedAddrPattern;

	/** {@inheritDoc} */
	@Override
	public void init(ServletConfig config) throws ServletException {
		super.init(config);
		Parameters.initialize(config.getServletContext());
		this.timer = new Timer("collector", true);
		if (Parameters.getParameter(Parameter.ALLOWED_ADDR_PATTERN) != null) {
			allowedAddrPattern = Pattern.compile(Parameters
					.getParameter(Parameter.ALLOWED_ADDR_PATTERN));
		}

		try {
			urlsByApplication.putAll(Parameters.getCollectorUrlsByApplications());
		} catch (final MalformedURLException e) {
			throw new ServletException(e.getMessage(), e);
		}

		final int periodMillis = Parameters.getResolutionSeconds() * 1000;
		final TimerTask collectTask = new TimerTask() {
			/** {@inheritDoc} */
			@Override
			public void run() {
				// il ne doit pas y avoir d'erreur dans cette task
				collectWithoutErrors();
				// cette collecte ne peut interférer avec un autre thread,
				// car les compteurs sont mis à jour et utilisés par le même timer
				// et donc le même thread (les différentes tasks ne peuvent se chevaucher)
			}
		};
		// on schedule la tâche de fond,
		// avec une exécution de suite en asynchrone pour initialiser les données
		timer.schedule(collectTask, 100, periodMillis);
	}

	void collectWithoutErrors() {
		// clone pour éviter ConcurrentModificationException et pause dans debugger
		// si undeploy et timer.cancel en parallèle
		final LinkedHashMap<String, List<URL>> clone = new LinkedHashMap<String, List<URL>>(
				urlsByApplication);
		for (final Map.Entry<String, List<URL>> entry : clone.entrySet()) {
			final String application = entry.getKey();
			final List<URL> urls = entry.getValue();
			try {
				collectForApplication(application, urls);
				assert collectorsByApplication.size() == javaInformationsByApplication.size();
			} catch (final Throwable e) { // NOPMD
				// si erreur sur une webapp (indisponibilité par exemple), on continue avec les autres
				// et il ne doit y avoir aucune erreur dans cette task
				log(e.getMessage(), e);
			}
		}
	}

	private void collectForApplication(String application, List<URL> urls) throws IOException,
			ClassNotFoundException {
		final List<JavaInformations> javaInformationsList = new ArrayList<JavaInformations>();
		Collector collector = collectorsByApplication.get(application);
		for (final URL url : urls) {
			final List<Serializable> serialized = retrieveFromUrl(url);
			final List<Counter> counters = new ArrayList<Counter>();
			for (final Serializable serializable : serialized) {
				if (serializable instanceof Counter) {
					final Counter counter = (Counter) serializable;
					counter.setApplication(application);
					counters.add(counter);
				} else if (serializable instanceof JavaInformations) {
					final JavaInformations newJavaInformations = (JavaInformations) serializable;
					javaInformationsList.add(newJavaInformations);
				}
			}
			if (collector == null) {
				// on initialise les collectors au fur et à mesure
				// puisqu'on ne peut pas forcément au démarrage
				// car la webapp à monitorer peut être indisponible
				collector = createCollector(application, counters);
				collectorsByApplication.put(application, collector);
			} else {
				for (final Counter newCounter : counters) {
					for (final Counter counter : collector.getCounters()) {
						if (counter.getName().equals(newCounter.getName())) {
							counter.addRequests(newCounter);
						}
					}
				}
			}
		}
		javaInformationsByApplication.put(application, javaInformationsList);
		collector.collectWithoutErrors(javaInformationsList);
	}

	private Collector createCollector(String application, final List<Counter> counters) {
		final Collector collector = new Collector(application, counters, timer);
		if (Parameters.getParameter(Parameter.MAIL_SESSION) != null
				&& Parameters.getParameter(Parameter.ADMIN_EMAILS) != null) {
			MailReport.scheduleReportMail(collector, timer);
		}
		return collector;
	}

	@SuppressWarnings("unchecked")
	private List<Serializable> retrieveFromUrl(URL url) throws IOException, ClassNotFoundException {
		final LabradorRetriever labradorRetriever = new LabradorRetriever(url);
		return (List<Serializable>) labradorRetriever.call();
	}

	/** {@inheritDoc} */
	@Override
	protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException,
			IOException {
		final String application = getApplication(req, resp);
		if (application == null) {
			resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
					"Données non disponibles pour toutes les applications");
			return;
		}
		if (!isApplicationDataAvailable(application)) {
			resp.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
					"Données non disponibles pour l'application " + application);
			return;
		}
		if (allowedAddrPattern != null
				&& !allowedAddrPattern.matcher(req.getRemoteAddr()).matches()) {
			resp.sendError(HttpServletResponse.SC_FORBIDDEN, "Accès interdit");
			return;
		}
		final Collector collector = collectorsByApplication.get(application);
		final MonitoringController monitoringController = new MonitoringController(collector, true);
		final String actionParameter = req.getParameter(MonitoringController.ACTION_PARAMETER);
		if (actionParameter != null
				&& Action.valueOfIgnoreCase(actionParameter) != Action.CLEAR_COUNTER) {
			// on forwarde l'action (gc, invalidate session ou heap dump) sur l'application monitorée
			// et on récupère les informations à jour (notamment mémoire et nb de sessions)
			forwardActionAndUpdateData(actionParameter, application);
		} else {
			// nécessaire si action clear_counter
			monitoringController.executeActionIfNeeded(req);
		}
		// la récupération de javaInformationsList doit être après forwardActionAndUpdateData
		// pour être à jour
		final List<JavaInformations> javaInformationsList = javaInformationsByApplication
				.get(application);
		monitoringController.doReport(req, resp, javaInformationsList);
	}

	private void forwardActionAndUpdateData(String actionParameter, String application)
			throws IOException {
		final List<URL> urls = urlsByApplication.get(application);
		final List<URL> actionUrls = new ArrayList<URL>();
		for (final URL url : urls) {
			actionUrls.add(new URL(url.toString() + "&action=" + actionParameter));
		}
		try {
			collectForApplication(application, actionUrls);
		} catch (final ClassNotFoundException e) {
			throw new IllegalStateException(e);
		}
	}

	private String getApplication(HttpServletRequest req, HttpServletResponse resp) {
		// on utilise un cookie client pour stocker l'application
		// car la page html est faite pour une seule application sans passer son nom en paramètre des requêtes
		// et pour ne pas perdre l'application choisie entre les reconnexions
		String application = req.getParameter("application");
		if (application == null) {
			// pas de paramètre application dans la requête, on cherche le cookie
			final Cookie[] cookies = req.getCookies();
			if (cookies != null) {
				for (final Cookie cookie : Arrays.asList(cookies)) {
					if (COOKIE_NAME.equals(cookie.getName())) {
						application = cookie.getValue();
						if (!isApplicationDataAvailable(application)) {
							cookie.setMaxAge(-1);
							resp.addCookie(cookie);
							application = null;
						}
						break;
					}
				}
			}
			if (application == null && !collectorsByApplication.isEmpty()) {
				// pas de cookie, on prend la première application
				application = collectorsByApplication.keySet().iterator().next();
			}
		} else if (isApplicationDataAvailable(application)) {
			// un paramètre application est présent dans la requête: l'utilisateur a choisi une application,
			// donc on fixe le cookie
			final Cookie cookie = new Cookie(COOKIE_NAME, String.valueOf(application));
			cookie.setMaxAge(30 * 24 * 60 * 60); // cookie persistant, valide pendant 30 jours
			resp.addCookie(cookie);
		}
		return application;
	}

	private boolean isApplicationDataAvailable(String application) {
		return collectorsByApplication.containsKey(application)
				&& javaInformationsByApplication.containsKey(application);
	}

	/** {@inheritDoc} */
	@Override
	public void destroy() {
		for (final Collector collector : collectorsByApplication.values()) {
			collector.stop();
		}
		timer.cancel();
		Collector.stopJRobin();

		// nettoyage avant le retrait de la webapp au cas où celui-ci ne suffise pas
		urlsByApplication.clear();
		collectorsByApplication.clear();
		javaInformationsByApplication.clear();

		super.destroy();
	}
}
