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

import java.io.Serializable;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicInteger;

import javax.servlet.ServletContextEvent;
import javax.servlet.ServletContextListener;
import javax.servlet.http.HttpSession;
import javax.servlet.http.HttpSessionActivationListener;
import javax.servlet.http.HttpSessionEvent;
import javax.servlet.http.HttpSessionListener;

/**
 * Listener de session http pour le monitoring.
 * C'est la classe de ce listener qui doit être déclarée dans le fichier web.xml de la webapp.
 * Ce listener fait également listener de contexte de servlet
 * et listener de passivation/activation de sessions.
 * @author Emeric Vernat
 */
public class SessionListener implements HttpSessionListener, HttpSessionActivationListener,
		ServletContextListener, Serializable {
	private static final long serialVersionUID = -1624944319058843901L;
	private static final String SESSION_ACTIVATION_KEY = "monitoring.sessionActivation";
	// au lieu d'utiliser un int avec des synchronized partout, on utilise un AtomicInteger
	private static final AtomicInteger SESSION_COUNT = new AtomicInteger();

	// attention : this est mis en session, cette map doit donc restée statique
	private static final ConcurrentHashMap<String, HttpSession> SESSION_MAP_BY_ID = new ConcurrentHashMap<String, HttpSession>();

	static int getSessionCount() {
		// nous pourrions nous contenter d'utiliser SESSION_MAP_BY_ID.size()
		// mais on se contente de SESSION_COUNT qui est suffisant pour avoir cette valeur
		// (SESSION_MAP_BY_ID servira pour la fonction d'invalidateAllSessions)
		return SESSION_COUNT.get();
	}

	static void invalidateAllSessions() {
		for (final HttpSession session : SESSION_MAP_BY_ID.values()) {
			session.invalidate();
		}
	}

	/** {@inheritDoc} */
	public void contextInitialized(ServletContextEvent event) {
		Parameters.initialize(event.getServletContext());

		// on initialise le monitoring des DataSource jdbc même si cette initialisation
		// sera refaite dans MonitoringFilter au cas où ce listener ait été oublié dans web.xml
		final JdbcWrapper jdbcWrapper = JdbcDriver.SINGLETON.getJdbcWrapper();
		jdbcWrapper.initServletContext(event.getServletContext());
		jdbcWrapper.rebindDataSources();
	}

	/** {@inheritDoc} */
	public void contextDestroyed(ServletContextEvent event) {
		// nettoyage avant le retrait de la webapp au cas où celui-ci ne suffise pas
		SESSION_MAP_BY_ID.clear();
	}

	// Rq : avec les sessions, on pourrait faire des statistiques sur la durée moyenne des sessions
	// (System.currentTimeMillis() - event.getSession().getCreationTime())
	// ou le délai entre deux requêtes http par utilisateur
	// (System.currentTimeMillis() - httpRequest.getSession().getLastAccessedTime())

	/** {@inheritDoc} */
	public void sessionCreated(HttpSessionEvent event) {
		// pour être notifié des passivations et activations, on enregistre un HttpSessionActivationListener (this)
		final HttpSession session = event.getSession();
		session.setAttribute(SESSION_ACTIVATION_KEY, this);

		// pour getSessionCount
		SESSION_COUNT.incrementAndGet();

		// pour invalidateAllSession
		SESSION_MAP_BY_ID.put(session.getId(), session);
	}

	/** {@inheritDoc} */
	public void sessionDestroyed(HttpSessionEvent event) {
		final HttpSession session = event.getSession();
		session.removeAttribute(SESSION_ACTIVATION_KEY);

		// pour getSessionCount
		SESSION_COUNT.decrementAndGet();

		// pour invalidateAllSession
		SESSION_MAP_BY_ID.remove(session.getId());
	}

	/** {@inheritDoc} */
	public void sessionDidActivate(HttpSessionEvent event) {
		// pour getSessionCount
		SESSION_COUNT.incrementAndGet();

		// pour invalidateAllSession
		SESSION_MAP_BY_ID.put(event.getSession().getId(), event.getSession());
	}

	/** {@inheritDoc} */
	public void sessionWillPassivate(HttpSessionEvent event) {
		// pour getSessionCount
		SESSION_COUNT.decrementAndGet();

		// pour invalidateAllSession
		SESSION_MAP_BY_ID.remove(event.getSession().getId());
	}

	/** {@inheritDoc} */
	@Override
	public String toString() {
		return getClass().getSimpleName() + "[sessionCount=" + getSessionCount() + ']';
	}
}
