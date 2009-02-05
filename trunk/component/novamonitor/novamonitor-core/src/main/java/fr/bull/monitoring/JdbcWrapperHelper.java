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

import java.lang.reflect.Field;
import java.security.AccessController;
import java.security.PrivilegedAction;
import java.util.Collections;
import java.util.HashMap;
import java.util.Hashtable;
import java.util.Map;

import javax.naming.InitialContext;
import javax.naming.NameClassPair;
import javax.naming.NamingException;
import javax.servlet.ServletContext;
import javax.sql.DataSource;

/**
 * Classe utilitaire pour JdbcWrapper.
 * @author Emeric Vernat
 */
final class JdbcWrapperHelper {
	private JdbcWrapperHelper() {
		super();
	}

	static Map<String, DataSource> getDataSources() throws NamingException, ClassNotFoundException {
		final Map<String, DataSource> dataSources = new HashMap<String, DataSource>();
		final InitialContext initialContext = new InitialContext();
		final String datasourcesParameter = Parameters.getParameter(Parameter.DATASOURCES);
		if (datasourcesParameter == null) {
			for (final NameClassPair nameClassPair : Collections.list(initialContext
					.list("java:comp/env/jdbc/"))) {
				if (DataSource.class.isAssignableFrom(Class.forName(nameClassPair.getClassName()))) {
					final String jndiName = "java:comp/env/jdbc/" + nameClassPair.getName();
					final DataSource dataSource = (DataSource) initialContext.lookup(jndiName);
					dataSources.put(jndiName, dataSource);
				}
			}
		} else {
			for (final String datasource : datasourcesParameter.split(",")) {
				final String jndiName = datasource.trim();
				// ici, on n'ajoute pas java:/comp/env
				// et on suppose qu'il n'en faut pas ou que cela a été ajouté dans le paramétrage
				final DataSource dataSource = (DataSource) initialContext.lookup(jndiName);
				dataSources.put(jndiName, dataSource);
			}
		}
		return Collections.unmodifiableMap(dataSources);
	}

	@SuppressWarnings("all")
	// CHECKSTYLE:OFF
	static Object changeTomcatContextWritable(ServletContext servletContext,
			Object tomcatSecurityToken) throws NoSuchFieldException, ClassNotFoundException,
			IllegalAccessException {
		// cette méthode ne peut pas être utilisée avec un simple JdbcDriver
		assert servletContext != null;
		if (!servletContext.getServerInfo().contains("Tomcat")) {
			return null;
		}
		final Field field = Class.forName("org.apache.naming.ContextAccessController")
				.getDeclaredField("readOnlyContexts");
		setFieldAccessible(field);
		@SuppressWarnings("unchecked")
		final Hashtable<String, Object> readOnlyContexts = (Hashtable<String, Object>) field
				.get(null);
		// contextPath vaut /myapp par exemple
		final String contextName = "/Catalina/localhost" + servletContext.getContextPath();
		if (tomcatSecurityToken == null) {
			// on rend le contexte writable
			return readOnlyContexts.remove(contextName);
		}
		// on remet le contexte not writable comme avant
		readOnlyContexts.put(contextName, tomcatSecurityToken);

		return null;
	}

	@SuppressWarnings("all")
	private static void setFieldAccessible(final Field field) {
		AccessController.doPrivileged(new PrivilegedAction() { // pour findbugs
					/** {@inheritDoc} */
					public Object run() {
						field.setAccessible(true);
						return null;
					}
				});
	}
	// CHECKSTYLE:ON
}
