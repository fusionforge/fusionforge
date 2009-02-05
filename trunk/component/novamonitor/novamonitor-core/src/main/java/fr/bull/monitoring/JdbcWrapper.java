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

import java.lang.reflect.InvocationHandler;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.lang.reflect.Proxy;
import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.concurrent.atomic.AtomicInteger;

import javax.naming.Context;
import javax.naming.InitialContext;
import javax.servlet.ServletContext;
import javax.sql.DataSource;

/**
 * Cette classe rebind dans l'annuaire JNDI la dataSource jdbc en la remplaçant
 * par un proxy de monitoring.
 * @author Emeric Vernat
 */
class JdbcWrapper {
	// au lieu d'utiliser int avec des synchronized partout, on utilise AtomicInteger
	static final AtomicInteger ACTIVE_CONNECTION_COUNT = new AtomicInteger();
	static final AtomicInteger USED_CONNECTION_COUNT = new AtomicInteger();
	static final AtomicInteger ACTIVE_THREAD_COUNT = new AtomicInteger();

	// Cette variable sqlCounter conserve un état qui est global au filtre et à l'application (donc thread-safe).
	private final Counter sqlCounter;
	private ServletContext servletContext;
	private final Map<String, DataSource> dataSourcesBackup = new HashMap<String, DataSource>();

	/**
	 * Handler de proxy d'un statement jdbc.
	 */
	private class StatementInvocationHandler implements InvocationHandler {
		// Rq : dans les proxy de DataSource, Connection et Statement,
		// si la méthode appelée est java.sql.Wrapper.unwrap
		// on invoque toujours unwrap sur l'objet initial pour qu'il retourne lui-même
		// ou son objet wrappé. Par exemple, l'appel de unwrap sur le proxy d'un Statement
		// retournera le Statement initial du serveur ou même du driver bdd (OracleStatement...)
		// sans notre proxy pour pouvoir appeler les méthodes non standard du driver par ex.
		private String requestName;
		private final Statement statement;

		StatementInvocationHandler(String query, Statement statement) {
			super();
			assert statement != null;

			this.requestName = query;
			this.statement = statement;
			ACTIVE_CONNECTION_COUNT.incrementAndGet();
		}

		/** {@inheritDoc} */
		public Object invoke(Object proxy, Method method, Object[] args) throws Throwable {
			final String methodName = method.getName();
			if (methodName.startsWith("execute")) {
				if (isFirstArgAString(args)) {
					// la méthode est du type executeQuery(String), executeUpdate(String),
					// executeUpdate(String, ...) ou execute(String sql),
					// alors la requête sql est le premier argument (et pas query)
					requestName = (String) args[0];
				}

				// si on n'a pas trouvé la requête, on prend "null"
				requestName = String.valueOf(requestName);

				return doExecute(requestName, statement, method, args);
			} else if ("addBatch".equals(methodName) && isFirstArgAString(args)) {
				// Bien que déconseillée la méthode est addBatch(String),
				// la requête sql est alors le premier argument
				// (elle sera utilisée lors de l'appel à executeBatch())

				// Rq : on ne conserve que la dernière requête de addBatch.
				// Rq : si addBatch(String) est appelée, puis que executeUpdate(String)
				// la requête du batch est correctement ignorée ci-dessus.
				// Rq : si connection.prepareStatement(String).addBatch(String) puis executeUpdate()
				// sont appelées (et pas executeBatch()) alors la requête conservée est
				// faussement celle du batch mais l'application cloche grave.
				requestName = (String) args[0];
			}

			// ce n'est pas une méthode executeXxx du Statement
			final Object result = method.invoke(statement, args);

			if ("close".equals(methodName)) {
				ACTIVE_CONNECTION_COUNT.decrementAndGet();
			}
			return result;
		}

		private boolean isFirstArgAString(Object[] args) {
			return args != null && args.length > 0 && args[0] instanceof String;
		}
	}

	/**
	 * Handler de proxy d'une connexion jdbc.
	 */
	private class ProxyInvocationHandler implements InvocationHandler {
		private final Connection connection;

		ProxyInvocationHandler(Connection connection) {
			super();
			assert connection != null;
			this.connection = connection;
			USED_CONNECTION_COUNT.incrementAndGet();
		}

		/** {@inheritDoc} */
		public Object invoke(Object proxy, Method method, Object[] args) throws Throwable {
			Object result = method.invoke(connection, args);
			if (result instanceof Statement) {
				final String methodName = method.getName();
				final String requestName;
				if ("prepareStatement".equals(methodName) || "prepareCall".equals(methodName)) {
					// la méthode est du type prepareStatement(String) ou prepareCall(String),
					// alors la requête sql est le premier argument
					requestName = (String) args[0];
				} else {
					requestName = null;
				}
				result = createStatementProxy(requestName, (Statement) result);
			} else if ("close".equals(method.getName())) {
				USED_CONNECTION_COUNT.decrementAndGet();
			}
			return result;
		}
	}

	// ce handler désencapsule les InvocationTargetException des 3 proxy
	private static class DelegatingInvocationHandler implements InvocationHandler {
		private final InvocationHandler delegate;

		DelegatingInvocationHandler(InvocationHandler delegate) {
			super();
			this.delegate = delegate;
		}

		/** {@inheritDoc} */
		public Object invoke(Object proxy, Method method, Object[] args) throws Throwable {
			try {
				return delegate.invoke(proxy, method, args);
			} catch (final InvocationTargetException e) {
				if (e.getTargetException() != null) {
					throw e.getTargetException();
				}
				throw e;
			}
		}
	}

	JdbcWrapper(Counter sqlCounter, ServletContext servletContext) {
		super();
		assert sqlCounter != null;
		this.sqlCounter = sqlCounter;
		// servletContext est null pour un simple JdbcDriver
		this.servletContext = servletContext;
	}

	void initServletContext(ServletContext context) {
		assert context != null;
		this.servletContext = context;
	}

	static int getUsedConnectionCount() {
		return USED_CONNECTION_COUNT.get();
	}

	static int getActiveConnectionCount() {
		return ACTIVE_CONNECTION_COUNT.get();
	}

	static int getActiveThreadCount() {
		return ACTIVE_THREAD_COUNT.get();
	}

	Counter getSqlCounter() {
		return sqlCounter;
	}

	Object doExecute(String requestName, Statement statement, Method method, Object[] args)
			throws IllegalAccessException, InvocationTargetException {
		assert requestName != null;
		assert statement != null;
		assert method != null;

		final long start = System.currentTimeMillis();
		boolean systemError = true;
		try {
			final Object result = method.invoke(statement, args);
			systemError = false;
			return result;
		} catch (final InvocationTargetException e) {
			if (e.getCause() instanceof SQLException) {
				final int errorCode = ((SQLException) e.getCause()).getErrorCode();
				if (errorCode >= 20000 && errorCode < 30000) {
					// Dans Oracle par exemple, les erreurs 20000 à 30000 sont standardisées
					// comme étant des erreurs lancées par l'application dans des procédures stockées
					// pour être traitées comme des erreurs de saisies ou comme des règles de gestion.
					// Ce ne sont donc pas des erreurs systèmes.
					systemError = false;
				}
			}
			throw e;
		} finally {
			final long duration = Math.max(System.currentTimeMillis() - start, 0);
			sqlCounter.addRequest(requestName, duration, systemError, -1);
		}
	}

	boolean rebindDataSources() {
		boolean ok;
		// on cherche une datasource avec InitialContext pour afficher nom et version bdd + nom et version driver jdbc
		// (le nom de la dataSource recherchée dans JNDI est du genre jdbc/Xxx qui est le nom standard d'une DataSource)
		try {
			final InitialContext initialContext = new InitialContext();
			for (final Map.Entry<String, DataSource> entry : JdbcWrapperHelper.getDataSources()
					.entrySet()) {
				final String jndiName = entry.getKey();
				final DataSource dataSource = entry.getValue();
				if (!isProxyAlready(dataSource)) {
					// si dataSource est déjà un proxy, il ne faut pas faire un proxy d'un proxy ni un rebinding
					final DataSource dataSourceProxy = createDataSourceProxy(dataSource);
					final Object tomcatSecurityToken = JdbcWrapperHelper
							.changeTomcatContextWritable(servletContext, null);
					initialContext.rebind(jndiName, dataSourceProxy);
					dataSourcesBackup.put(jndiName, dataSource);
					JdbcWrapperHelper.changeTomcatContextWritable(servletContext,
							tomcatSecurityToken);
				}
			}
			ok = true;
		} catch (final Throwable t) { // NOPMD
			// ça n'a pas marché, tant pis
			ok = false;
		}
		return ok;
	}

	boolean stop() {
		boolean ok;
		try {
			final InitialContext initialContext = new InitialContext();
			for (final Map.Entry<String, DataSource> entry : dataSourcesBackup.entrySet()) {
				final String jndiName = entry.getKey();
				final DataSource dataSource = entry.getValue();
				final Object tomcatSecurityToken = JdbcWrapperHelper.changeTomcatContextWritable(
						servletContext, null);
				initialContext.rebind(jndiName, dataSource);
				JdbcWrapperHelper.changeTomcatContextWritable(servletContext, tomcatSecurityToken);
			}
			ok = true;
		} catch (final Throwable t) { // NOPMD
			// ça n'a pas marché, tant pis
			ok = false;
		}
		dataSourcesBackup.clear();
		return ok;
	}

	Context createContextProxy(final Context context) {
		assert context != null;
		final InvocationHandler invocationHandler = new InvocationHandler() {
			/** {@inheritDoc} */
			public Object invoke(Object proxy, Method method, Object[] args) throws Throwable {
				Object result = method.invoke(context, args);
				if (result instanceof DataSource) {
					result = createDataSourceProxy((DataSource) result);
				}
				return result;
			}
		};
		return createProxy(context, invocationHandler);
	}

	DataSource createDataSourceProxy(final DataSource dataSource) {
		assert dataSource != null;
		final InvocationHandler invocationHandler = new InvocationHandler() {
			/** {@inheritDoc} */
			public Object invoke(Object proxy, Method method, Object[] args) throws Throwable {
				Object result = method.invoke(dataSource, args);
				if (result instanceof Connection) {
					result = createConnectionProxy((Connection) result);
				}
				return result;
			}
		};
		return createProxy(dataSource, invocationHandler);
	}

	Connection createConnectionProxy(Connection connection) {
		assert connection != null;
		if (isMonitoringDisabled()) {
			return connection;
		}
		final InvocationHandler invocationHandler = new ProxyInvocationHandler(connection);
		return createProxy(connection, invocationHandler);
	}

	private static boolean isMonitoringDisabled() {
		// on doit réévaluer ici le paramètre, car au départ le servletContext
		// n'est pas forcément défini si c'est un driver jdbc sans dataSource
		return Boolean.parseBoolean(Parameters.getParameter(Parameter.DISABLED));
	}

	Statement createStatementProxy(String query, Statement statement) {
		assert statement != null;
		// Si un proxy de connexion a été créé dans un driver jdbc et que par la suite le
		// servletContext a un paramètre désactivant le monitoring, alors ce n'est pas grave
		// les requêtes sql seront simplement aggrégées dans le counter pour cette connexion
		// jusqu'à sa fermeture (pour éviter ce détail, il suffirait simplement d'utiliser une
		// dataSource jdbc et pas un driver).
		// Rq : on ne réévalue pas le paramètre ici pour raison de performances sur la recherche
		// dans les paramètres du système, du contexte et du filtre alors que dans 99.999999999%
		// des exécutions il n'y a pas le paramètre.
		final InvocationHandler invocationHandler = new StatementInvocationHandler(query, statement);
		return createProxy(statement, invocationHandler);
	}

	@SuppressWarnings("unchecked")
	private <T> T createProxy(T object, InvocationHandler invocationHandler) {
		if (isProxyAlready(object)) {
			// si l'objet est déjà un proxy créé pas nous, initialisé par exemple
			// depuis SessionListener ou MonitoringInitialContextFactory,
			// alors il ne faut pas faire un proxy du proxy
			return object;
		}
		final ClassLoader classLoader = object.getClass().getClassLoader(); // NOPMD
		// Rq: object.get.Class().getInterfaces() ne suffit pas pour Connection dans Tomcat
		// car la connection est une instance de PoolGuardConnectionWrapper
		// et connection.getClass().getInterfaces() est vide dans ce cas
		final List<Class> interfaces = new ArrayList(Arrays.asList(object.getClass()
				.getInterfaces()));
		interfaces.addAll(Arrays.asList(object.getClass().getSuperclass().getInterfaces()));
		final Class[] interfacesArray = interfaces.toArray(new Class[interfaces.size()]);

		// ce handler désencapsule les InvocationTargetException des 3 proxy
		final InvocationHandler ih = new DelegatingInvocationHandler(invocationHandler);
		return (T) Proxy.newProxyInstance(classLoader, interfacesArray, ih);
	}

	private boolean isProxyAlready(Object object) {
		return Proxy.isProxyClass(object.getClass())
				&& Proxy.getInvocationHandler(object) instanceof DelegatingInvocationHandler;
	}
}
