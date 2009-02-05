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
import java.util.List;

/**
 * Informations sur un thread java, sans code html de présentation.
 * L'état d'une instance est initialisé à son instanciation et non mutable;
 * il est donc de fait thread-safe.
 * Cet état est celui d'un thread java à un instant t.
 * Les instances sont sérialisables pour pouvoir être transmises au serveur de collecte.
 * @author Emeric Vernat
 */
class ThreadInformations implements Serializable {
	private static final long serialVersionUID = 3604281253550723654L;
	private final String name;
	private final int priority;
	private final boolean daemon;
	private final Thread.State state;
	private final long cpuTimeMillis;
	private final long userTimeMillis;
	@SuppressWarnings("all")
	private final List<StackTraceElement> stackTrace;

	@SuppressWarnings("all")
	ThreadInformations(Thread thread, List<StackTraceElement> stackTrace, long cpuTimeMillis,
			long userTimeMillis) {
		super();
		assert thread != null;
		assert stackTrace == null || stackTrace instanceof Serializable;

		this.name = thread.getName();
		this.priority = thread.getPriority();
		this.daemon = thread.isDaemon();
		this.state = thread.getState();
		this.stackTrace = stackTrace;
		this.cpuTimeMillis = cpuTimeMillis;
		this.userTimeMillis = userTimeMillis;
	}

	String getName() {
		return name;
	}

	int getPriority() {
		return priority;
	}

	boolean isDaemon() {
		return daemon;
	}

	Thread.State getState() {
		return state;
	}

	List<StackTraceElement> getStackTrace() {
		return stackTrace;
	}

	long getCpuTimeMillis() {
		return cpuTimeMillis;
	}

	long getUserTimeMillis() {
		return userTimeMillis;
	}
}
