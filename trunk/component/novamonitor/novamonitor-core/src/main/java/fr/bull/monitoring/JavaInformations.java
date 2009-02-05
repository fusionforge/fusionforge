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
import java.lang.management.GarbageCollectorMXBean;
import java.lang.management.ManagementFactory;
import java.lang.management.OperatingSystemMXBean;
import java.lang.management.ThreadMXBean;
import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.Comparator;
import java.util.Date;
import java.util.List;
import java.util.Map;

import javax.naming.NamingException;
import javax.servlet.ServletContext;
import javax.sql.DataSource;

/**
 * Informations systèmes sur le serveur, sans code html de présentation.
 * L'état d'une instance est initialisé à son instanciation et non mutable;
 * il est donc de fait thread-safe.
 * Cet état est celui d'une instance de JVM java, de ses threads et du système à un instant t.
 * Les instances sont sérialisables pour pouvoir être transmises au serveur de collecte.
 * @author Emeric Vernat
 */
class JavaInformations implements Serializable { // NOPMD
	// les stack traces des threads ne sont récupérées qu'à partir de java 1.6.0 update 1
	// pour éviter la fuite mémoire du bug http://bugs.sun.com/bugdatabase/view_bug.do?bug_id=6434648
	static final boolean STACK_TRACES_ENABLED = "1.6.0_01".compareTo(Parameters.JAVA_VERSION) <= 0;
	private static final long serialVersionUID = 3281861236369720876L;
	private static final Date START_DATE = new Date();
	private final long usedMemory;
	private final long maxMemory;
	private final int sessionCount;
	private final int activeThreadCount;
	private final int usedConnectionCount;
	private final int activeConnectionCount;
	private final long processCpuTimeMillis;
	private final double systemLoadAverage;
	private final String host;
	private final String os;
	private final int availableProcessors;
	private final String javaVersion;
	private final String jvmVersion;
	private final String pid;
	private final String serverInfo;
	private final String contextPath;
	private final Date startDate;
	private final String jvmArguments;
	private final String memoryDetails;
	private final String threadsDetails;
	private final String dataBaseVersion;
	@SuppressWarnings("all")
	private final List<ThreadInformations> threadInformationsList;

	static final class ThreadInformationsComparator implements Comparator<ThreadInformations>,
			Serializable {
		private static final long serialVersionUID = 1L;

		/** {@inheritDoc} */
		public int compare(ThreadInformations thread1, ThreadInformations thread2) {
			return thread1.getName().compareToIgnoreCase(thread2.getName());
		}
	}

	JavaInformations(ServletContext servletContext, boolean includeDataBaseAndThreadsAndPID) {
		super();
		usedMemory = Runtime.getRuntime().totalMemory() - Runtime.getRuntime().freeMemory();
		maxMemory = Runtime.getRuntime().maxMemory();
		sessionCount = SessionListener.getSessionCount();
		activeThreadCount = JdbcWrapper.getActiveThreadCount();
		usedConnectionCount = JdbcWrapper.getUsedConnectionCount();
		activeConnectionCount = JdbcWrapper.getActiveConnectionCount();
		final OperatingSystemMXBean operatingSystem = ManagementFactory.getOperatingSystemMXBean();
		if ("com.sun.management.OperatingSystem".equals(operatingSystem.getClass().getName())) {
			final com.sun.management.OperatingSystemMXBean osBean = (com.sun.management.OperatingSystemMXBean) operatingSystem;
			// nano-secondes converties en milli-secondes
			processCpuTimeMillis = osBean.getProcessCpuTime() / 1000000;

			if ("1.6".compareTo(Parameters.JAVA_VERSION) < 0
					&& operatingSystem.getSystemLoadAverage() >= 0) {
				// systemLoadAverage n'existe qu'à partir du jdk 1.6
				systemLoadAverage = operatingSystem.getSystemLoadAverage();
			} else {
				systemLoadAverage = -1;
			}
		} else {
			processCpuTimeMillis = -1;
			systemLoadAverage = -1;
		}
		host = Parameters.getHostName() + '@' + Parameters.getHostAddress();
		os = System.getProperty("os.name") + ' ' + System.getProperty("sun.os.patch.level") + ", "
				+ System.getProperty("os.arch") + '/' + System.getProperty("sun.arch.data.model");
		availableProcessors = Runtime.getRuntime().availableProcessors();
		javaVersion = System.getProperty("java.runtime.name") + ", "
				+ System.getProperty("java.runtime.version");
		jvmVersion = System.getProperty("java.vm.name") + ", "
				+ System.getProperty("java.vm.version") + ", " + System.getProperty("java.vm.info");
		if (servletContext == null) {
			serverInfo = null;
			contextPath = null;
		} else {
			serverInfo = servletContext.getServerInfo();
			contextPath = servletContext.getContextPath();
		}
		startDate = START_DATE;
		final StringBuilder jvmArgs = new StringBuilder();
		for (final String jvmArg : ManagementFactory.getRuntimeMXBean().getInputArguments()) {
			jvmArgs.append(jvmArg).append('\n');
		}
		if (jvmArgs.length() > 0) {
			jvmArgs.deleteCharAt(jvmArgs.length() - 1);
		}
		jvmArguments = jvmArgs.toString();
		memoryDetails = buildMemoryDetails();
		final ThreadMXBean threadBean = ManagementFactory.getThreadMXBean();
		threadsDetails = "Nombre = " + threadBean.getThreadCount() + ",\nMaximum = "
				+ threadBean.getPeakThreadCount() + ",\nTotal démarrés = "
				+ threadBean.getTotalStartedThreadCount();

		if (includeDataBaseAndThreadsAndPID) {
			dataBaseVersion = buildDataBaseVersion();
			threadInformationsList = buildThreadInformationsList();
			pid = PID.getPID();
		} else {
			dataBaseVersion = null;
			threadInformationsList = null;
			pid = null;
		}
	}

	@SuppressWarnings("all")
	private List<ThreadInformations> buildThreadInformationsList() {
		final ThreadMXBean threadBean = ManagementFactory.getThreadMXBean();
		final List<Thread> threads;
		final Map<Thread, StackTraceElement[]> stackTraces;
		if (STACK_TRACES_ENABLED) {
			stackTraces = Thread.getAllStackTraces();
			threads = new ArrayList<Thread>(stackTraces.size());
			threads.addAll(stackTraces.keySet());
		} else {
			// on récupère les threads sans stack trace en contournant bug 6434648 avant 1.6.0_01
			ThreadGroup group = Thread.currentThread().getThreadGroup();
			while (group.getParent() != null) {
				group = group.getParent();
			}
			final Thread[] threadsArray = new Thread[group.activeCount()];
			group.enumerate(threadsArray, true);

			stackTraces = Collections.emptyMap();
			threads = Arrays.asList(threadsArray);
		}

		final boolean cpuTimeEnabled = threadBean.isThreadCpuTimeSupported()
				&& threadBean.isThreadCpuTimeEnabled();
		final List<ThreadInformations> threadInfosList = new ArrayList<ThreadInformations>(threads
				.size());
		for (final Thread thread : threads) {
			final StackTraceElement[] stackTraceElements = stackTraces.get(thread);
			final List<StackTraceElement> stackTraceElementList = stackTraceElements == null ? null
					: Collections.unmodifiableList(Arrays.asList(stackTraceElements));
			final long cpuTimeMillis;
			final long userTimeMillis;
			if (cpuTimeEnabled) {
				cpuTimeMillis = threadBean.getThreadCpuTime(thread.getId()) / 1000000;
				userTimeMillis = threadBean.getThreadUserTime(thread.getId()) / 1000000;
			} else {
				cpuTimeMillis = -1;
				userTimeMillis = -1;
			}
			threadInfosList.add(new ThreadInformations(thread, stackTraceElementList,
					cpuTimeMillis, userTimeMillis));
		}
		return Collections.unmodifiableList(threadInfosList);
	}

	private String buildMemoryDetails() {
		// Rq : il y a beaucoup d'autres infos accessibles depuis ManagementFactory
		// par ex sur les threads et le cpu par thread, la mémoire heap et non-heap,
		// le garbage collector, les classes...
		final String mo = " Mo";
		final String nonHeapMemory = "Non heap memory = "
				+ ManagementFactory.getMemoryMXBean().getNonHeapMemoryUsage().getUsed() / 1024
				/ 1024 + mo;
		// classes actuellement chargées
		final String classLoading = "Loaded classes = "
				+ ManagementFactory.getClassLoadingMXBean().getLoadedClassCount();
		// Rq : on pourrait faire une courbe du temps GC dans le temps
		long garbageCollectionTime = 0;
		for (final GarbageCollectorMXBean garbageCollector : ManagementFactory
				.getGarbageCollectorMXBeans()) {
			garbageCollectionTime += garbageCollector.getCollectionTime();
		}
		final String gc = "Garbage collection time = " + garbageCollectionTime + " ms";
		final OperatingSystemMXBean operatingSystem = ManagementFactory.getOperatingSystemMXBean();
		String osInfo = "";
		if ("com.sun.management.OperatingSystem".equals(operatingSystem.getClass().getName())) {
			final com.sun.management.OperatingSystemMXBean osBean = (com.sun.management.OperatingSystemMXBean) operatingSystem;
			osInfo = "Process cpu time = " + osBean.getProcessCpuTime() / 1000000
					+ " ms,\nCommitted virtual memory = " + osBean.getCommittedVirtualMemorySize()
					/ 1024 / 1024 + mo + ",\nFree physical memory = "
					+ osBean.getFreePhysicalMemorySize() / 1024 / 1024 + mo
					+ ",\nTotal physical memory = " + osBean.getTotalPhysicalMemorySize() / 1024
					/ 1024 + mo + ",\nFree swap space = " + osBean.getFreeSwapSpaceSize() / 1024
					/ 1024 + mo + ",\nTotal swap space = " + osBean.getTotalSwapSpaceSize() / 1024
					/ 1024 + mo;
		}

		// System load average for the last minute.
		// The system load average is the sum of
		// the number of runnable entities queued to the available processors
		// and the number of runnable entities running on the available processors
		// averaged over a period of time.

		String sysLoadAverage = "";
		if ("1.6".compareTo(Parameters.JAVA_VERSION) < 0
				&& operatingSystem.getSystemLoadAverage() >= 0) {
			// systemLoadAverage n'existe qu'à partir du jdk 1.6
			sysLoadAverage = "System load average = " + operatingSystem.getSystemLoadAverage();
		}
		final String next = ",\n";
		return nonHeapMemory + next + classLoading + next + gc + next + osInfo + next
				+ sysLoadAverage;
	}

	private String buildDataBaseVersion() {
		final StringBuilder result = new StringBuilder();
		try {
			// on commence par voir si le driver jdbc a été utilisé
			// car s'il n'y a pas de datasource une exception est déclenchée
			final JdbcDriver jdbcDriver = JdbcDriver.SINGLETON;
			if (jdbcDriver.getLastConnectUrl() != null) {
				final Connection connection = DriverManager.getConnection(jdbcDriver
						.getLastConnectUrl(), jdbcDriver.getLastConnectInfo());
				connection.setAutoCommit(false);
				try {
					appendDataBaseVersion(result, connection);
				} finally {
					connection.rollback();
					connection.close();
				}
				return result.toString();
			}

			// on cherche une datasource avec InitialContext pour afficher nom et version bdd + nom et version driver jdbc
			// (le nom de la dataSource recherchée dans JNDI est du genre jdbc/Xxx qui est le nom standard d'une DataSource)
			for (final Map.Entry<String, DataSource> entry : JdbcWrapperHelper.getDataSources()
					.entrySet()) {
				final String jndiName = entry.getKey();
				final DataSource dataSource = entry.getValue();
				final Connection connection = dataSource.getConnection();
				connection.setAutoCommit(false);
				try {
					if (result.length() > 0) {
						result.append('\n');
					}
					result.append(jndiName).append(":\n");
					appendDataBaseVersion(result, connection);
				} finally {
					connection.rollback();
					connection.close();
				}
			}
		} catch (final NamingException e) {
			result.append(e.getMessage());
		} catch (final ClassNotFoundException e) {
			result.append(e.getMessage());
		} catch (final SQLException e) {
			result.append(e.getMessage());
		}
		if (result.length() > 0) {
			return result.toString();
		}
		return null;
	}

	private void appendDataBaseVersion(StringBuilder result, Connection connection)
			throws SQLException {
		final DatabaseMetaData metaData = connection.getMetaData();
		// Sécurité: pour l'instant on n'indique pas metaData.getUserName()
		result.append(metaData.getURL()).append('\n');
		result.append(metaData.getDatabaseProductName()).append(", ").append(
				metaData.getDatabaseProductVersion()).append('\n');
		result.append("Driver JDBC:\n").append(metaData.getDriverName()).append(", ").append(
				metaData.getDriverVersion());
	}

	long getUsedMemory() {
		return usedMemory;
	}

	long getMaxMemory() {
		return maxMemory;
	}

	int getSessionCount() {
		return sessionCount;
	}

	int getActiveThreadCount() {
		return activeThreadCount;
	}

	int getUsedConnectionCount() {
		return usedConnectionCount;
	}

	int getActiveConnectionCount() {
		return activeConnectionCount;
	}

	long getProcessCpuTimeMillis() {
		return processCpuTimeMillis;
	}

	double getSystemLoadAverage() {
		return systemLoadAverage;
	}

	String getHost() {
		return host;
	}

	String getOS() {
		return os;
	}

	int getAvailableProcessors() {
		return availableProcessors;
	}

	String getJavaVersion() {
		return javaVersion;
	}

	String getJvmVersion() {
		return jvmVersion;
	}

	String getPID() {
		return pid;
	}

	String getServerInfo() {
		return serverInfo;
	}

	String getContextPath() {
		return contextPath;
	}

	Date getStartDate() {
		return startDate;
	}

	String getJvmArguments() {
		return jvmArguments;
	}

	String getMemoryDetails() {
		return memoryDetails;
	}

	String getThreadsDetails() {
		return threadsDetails;
	}

	String getDataBaseVersion() {
		return dataBaseVersion;
	}

	List<ThreadInformations> getThreadInformationsList() {
		// on trie sur demande (si affichage)
		final List<ThreadInformations> result = new ArrayList<ThreadInformations>(
				threadInformationsList);
		Collections.sort(result, new ThreadInformationsComparator());
		return Collections.unmodifiableList(result);
	}
}
