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

import java.lang.reflect.Method;

import javax.interceptor.AroundInvoke;
import javax.interceptor.InvocationContext;

/**
 * Intercepteur pour EJB 3 (Java EE 5).
 * Il est destiné à un compteur pour les statistiques d'exécutions de méthodes sur les "façades métiers"
 * ( @Stateless, @Stateful ou @MessageDriven ).
 * Il peut être paramétré dans le fichier ejb-jar.xml pour certains ejb ou pour tous les ejb,
 * ou alors par l'annotation @Interceptors dans les sources java des implémentations d'ejb.
 * @author Emeric Vernat
 */
public class MonitoringInterceptor {
	private static final Counter EJB_COUNTER = new Counter("ejb", "ejb.png", JdbcDriver.SINGLETON
			.getJdbcWrapper().getSqlCounter());
	private static final boolean DISABLED = Boolean.parseBoolean(Parameters
			.getParameter(Parameter.DISABLED));

	static Counter getEjbCounter() {
		return EJB_COUNTER;
	}

	/**
	 * Intercepte une exécution de méthode sur un ejb.
	 * @param context InvocationContext
	 * @return Object
	 * @throws Exception e
	 */
	@AroundInvoke
	public Object intercept(InvocationContext context) throws Exception { // NOPMD
		if (DISABLED) {
			return context.proceed();
		}
		// cette méthode est appelée par le conteneur ejb grâce à l'annotation AroundInvoke
		final long start = System.currentTimeMillis();
		boolean systemError = false;
		try {
			EJB_COUNTER.bindContext();
			return context.proceed();
		} catch (final Throwable t) { // NOPMD
			// on catche Throwable pour avoir tous les cas d'erreur système
			systemError = true;
			throwException(t);
			return null;
		} finally {
			final long duration = Math.max(System.currentTimeMillis() - start, 0);

			// nom identifiant la requête
			final Method method = context.getMethod();
			final String requestName = method.getDeclaringClass().getSimpleName() + '.'
					+ method.getName();

			// on enregistre la requête dans les statistiques
			EJB_COUNTER.addRequest(requestName, duration, systemError, -1);
		}
	}

	private void throwException(Throwable t) throws Exception { // NOPMD
		if (t instanceof Error) {
			throw (Error) t;
		} else if (t instanceof Exception) {
			throw (Exception) t;
		} else {
			// ne peut arriver, mais par acquis de conscience
			throw new Exception(t.getMessage(), t); // NOPMD
		}
	}
}
