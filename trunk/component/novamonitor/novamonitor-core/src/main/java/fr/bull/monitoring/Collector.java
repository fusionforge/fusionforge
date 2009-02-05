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
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collection;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Timer;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Collecteur de données sur les compteurs, avec son propre thread, pour remplir les courbes.
 * @author Emeric Vernat
 */
final class Collector {
	// période entre 2 collectes en milli-secondes
	private final int periodMillis;
	private final Timer timer;
	private final String application;
	private final List<Counter> counters;
	private final Map<String, JRobin> requestJRobinsById = new ConcurrentHashMap<String, JRobin>();
	// les instances jrobins des compteurs sont créées à l'initialisation
	private final Map<String, JRobin> counterJRobins = new LinkedHashMap<String, JRobin>();
	// globalRequestsByCounter, requestsById, dayCountersByCounter et cpuTimeMillis
	// sont utilisés par un seul thread lors des collectes,
	// (et la méthode centrale "collect" est synchronisée pour éviter un accès concurrent
	// avec la mise à jour avant le rapport html)
	private final Map<Counter, CounterRequest> globalRequestsByCounter = new HashMap<Counter, CounterRequest>();
	private final Map<String, CounterRequest> requestsById = new HashMap<String, CounterRequest>();
	private final Map<Counter, Counter> dayCountersByCounter = new LinkedHashMap<Counter, Counter>();
	private long cpuTimeMillis;
	private long lastCollectDuration;

	Collector(String application, List<Counter> counters, Timer timer) {
		super();
		assert application != null;
		assert counters != null;
		assert timer != null;
		this.timer = timer;
		this.application = application;
		this.counters = Collections.unmodifiableList(new ArrayList<Counter>(counters));
		// c'est le collector qui fixe le nom de l'application (avant la lecture des éventuels fichiers)
		for (final Counter counter : counters) {
			counter.setApplication(application);
			final Counter dayCounter = new PeriodCounterFactory(counter)
					.createDayCounterAtDate(new Date());
			dayCountersByCounter.put(counter, dayCounter);
		}
		periodMillis = Parameters.getResolutionSeconds() * 1000;

		try {
			// on relit les compteurs à l'initialisation pour récupérer les stats;
			// d'abord les compteurs non temporels, au cas où les compteurs par jour soient illisibles,
			for (final Counter counter : counters) {
				counter.readFromFile();
			}
			// et seulement ensuite les compteurs du jour
			for (final Counter counter : counters) {
				dayCountersByCounter.get(counter).readFromFile();
			}
		} catch (final IOException e) {
			// lecture échouée, tant pis
			// (on n'interrompt pas toute l'initialisation juste pour un fichier illisible)
			printStackTrace(e);
		} catch (final ClassNotFoundException e) {
			// idem
			printStackTrace(e);
		}
	}

	String getApplication() {
		return application;
	}

	List<Counter> getCounters() {
		return counters;
	}

	long getLastCollectDuration() {
		return lastCollectDuration;
	}

	List<Counter> getPeriodCounters(Period period) throws IOException {
		final Collection<Counter> currentDayCounters = dayCountersByCounter.values();
		final List<Counter> result = new ArrayList<Counter>(currentDayCounters.size());
		switch (period) {
		case JOUR:
			result.addAll(currentDayCounters);
			break;
		case SEMAINE:
			for (final Counter dayCounter : currentDayCounters) {
				result.add(new PeriodCounterFactory(dayCounter).getWeekCounter());
			}
			break;
		case MOIS:
			for (final Counter dayCounter : currentDayCounters) {
				result.add(new PeriodCounterFactory(dayCounter).getMonthCounter());
			}
			break;
		case ANNEE:
			for (final Counter dayCounter : currentDayCounters) {
				result.add(new PeriodCounterFactory(dayCounter).getYearCounter());
			}
			break;
		case TOUT:
			result.addAll(counters);
			break;
		default:
			throw new IllegalArgumentException(period.toString());
		}
		return Collections.unmodifiableList(result);
	}

	void collectLocalContextWithoutErrors() {
		// ici on n'inclue pas les informations de la bdd et des threads
		// car on n'en a pas besoin pour la collecte et cela économise des requêtes sql
		final JavaInformations javaInformations = new JavaInformations(Parameters
				.getServletContext(), false);

		collectWithoutErrors(Collections.singletonList(javaInformations));
	}

	void collectWithoutErrors(List<JavaInformations> javaInformationsList) {
		final long start = System.currentTimeMillis();
		try {
			collect(javaInformationsList);
		} catch (final Throwable t) { // NOPMD
			printStackTrace(t);
		}
		// note : on n'inclue pas "new JavaInformations" de collectLocalContextWithoutErrors
		// mais il est inférieur à 1 ms (sans bdd)
		lastCollectDuration = System.currentTimeMillis() - start;
	}

	private void collect(List<JavaInformations> javaInformationsList) throws IOException {
		synchronized (this) {
			collectJavaInformations(javaInformationsList);

			for (final Counter counter : counters) {
				// collecte pour chaque compteur (hits par minute, temps moyen, % d'erreurs système)
				// Rq : il serait possible d'ajouter le débit total en Ko / minute (pour http)
				// mais autant monitorer les vrais débits réseaux au niveau de l'OS
				collectCounterData(counter);
			}
		}
	}

	private void collectJavaInformations(List<JavaInformations> javaInformationsList)
			throws IOException {
		if (javaInformationsList.isEmpty()) {
			// si pas d'informations, on ne met pas 0 : on ne met rien
			return;
		}
		long usedMemory = 0;
		long processesCpuTimeMillis = 0;
		long sessionCount = 0;
		long activeThreadCount = 0;
		long activeConnectionCount = 0;
		long usedConnectionCount = 0;
		double systemLoadAverage = 0;

		for (final JavaInformations javaInformations : javaInformationsList) {
			usedMemory += javaInformations.getUsedMemory();
			sessionCount += javaInformations.getSessionCount();
			activeThreadCount += javaInformations.getActiveThreadCount();
			activeConnectionCount += javaInformations.getActiveConnectionCount();
			usedConnectionCount += javaInformations.getUsedConnectionCount();

			if (javaInformations.getProcessCpuTimeMillis() >= 0) {
				// processesCpuTime n'est supporté que par le jdk sun
				processesCpuTimeMillis += javaInformations.getProcessCpuTimeMillis();
			} else {
				processesCpuTimeMillis = -1;
			}
			if (javaInformations.getSystemLoadAverage() >= 0) {
				// systemLoadAverage n'est supporté qu'à partir du jdk 1.6
				systemLoadAverage += javaInformations.getSystemLoadAverage();
			} else {
				systemLoadAverage = -1;
			}
		}
		// collecte de la mémoire java
		getCounterJRobin("usedMemory", "Mémoire utilisée").addValue(usedMemory);

		// collecte du pourcentage d'utilisation cpu et de la charge système
		if (processesCpuTimeMillis >= 0) {
			// processesCpuTimeMillis est la somme pour tous les serveurs (et pour tous les coeurs)
			// donc ce temps peut être n fois supérieur à periodMillis où n est le nombre total de coeurs
			// et cpuPercentage s'approchera à pleine charge de n * 100
			final int cpuPercentage = (int) ((processesCpuTimeMillis - this.cpuTimeMillis) * 100 / periodMillis);
			getCounterJRobin("cpu", "% CPU").addValue(cpuPercentage);
			this.cpuTimeMillis = processesCpuTimeMillis;
		}
		if (systemLoadAverage >= 0) {
			getCounterJRobin("systemLoad", "System load").addValue(systemLoadAverage);
		}

		// collecte du nombre de sessions http, du nombre de threads actifs
		// (requêtes http en cours), du nombre de connexions jdbc actives
		// et du nombre de connexions jdbc ouvertes
		getCounterJRobin("httpSessions", "Sessions http").addValue(sessionCount);
		getCounterJRobin("activeThreads", "Threads actifs").addValue(activeThreadCount);
		getCounterJRobin("activeConnections", "Connexions jdbc actives").addValue(
				activeConnectionCount);
		getCounterJRobin("usedConnections", "Connexions jdbc utilisées").addValue(
				usedConnectionCount);

		// on pourrait collecter la valeur 100 dans jrobin pour qu'il fasse la moyenne
		// du pourcentage de disponibilité, mais cela n'aurait pas de sens sans
		// différenciation des indisponibilités prévues de celles non prévues
	}

	private void collectCounterData(Counter counter) throws IOException {
		// counterName vaut http, sql ou ws par exemple
		final String counterName = counter.getName();
		// on calcule les totaux depuis le départ
		final CounterRequest newGlobalRequest = new CounterRequest(counterName + " global",
				counterName);
		final List<CounterRequest> requests = counter.getRequests();
		for (final CounterRequest request : requests) {
			// ici, pas besoin de synchronized sur request puisque ce sont des clones indépendants
			newGlobalRequest.addHits(request);
		}

		// on récupére les instances de jrobin même s'il n'y a pas de hits ou pas de précédents totaux
		// pour être sûr qu'elles soient initialisées (si pas instanciée alors pas de courbe)
		final JRobin hitsJRobin = getCounterJRobin(counterName + "HitsRate", "Hits " + counterName
				+ " par minute");
		final JRobin meanTimesJRobin = getCounterJRobin(counterName + "MeanTimes", "Temps "
				+ counterName + " moyens (ms)");
		final JRobin systemErrorsJRobin = getCounterJRobin(counterName + "SystemErrors",
				"% d'erreurs " + counterName);

		final CounterRequest globalRequest = globalRequestsByCounter.get(counter);
		if (globalRequest != null) {
			// on clone et on soustrait les précédents totaux
			// pour obtenir les totaux sur la dernière période
			// rq : s'il n'y a de précédents totaux (à l'initialisation)
			// alors on n'inscrit pas de valeurs car les nouveaux hits
			// ne seront connus (en delta) qu'au deuxième passage
			// (au 1er passage, globalRequest contient déjà les données lues sur disque)
			final CounterRequest lastPeriodGlobalRequest = newGlobalRequest.clone();
			lastPeriodGlobalRequest.removeHits(globalRequest);

			final long hits = lastPeriodGlobalRequest.getHits();
			final long hitsParMinute = hits * 60 * 1000 / periodMillis;

			// on remplit le stockage avec les données
			hitsJRobin.addValue(hitsParMinute);
			// s'il n'y a pas eu de hits, alors la moyenne vaut -1 : elle n'a pas de sens
			if (hits > 0) {
				meanTimesJRobin.addValue(lastPeriodGlobalRequest.getMean());
				systemErrorsJRobin.addValue(lastPeriodGlobalRequest.getSystemErrorPercentage());

				// s'il y a eu des requêtes, on persiste le compteur pour ne pas perdre les stats
				// en cas de crash ou d'arrêt brutal (mais normalement ils seront aussi persistés
				// lors de l'arrêt du serveur)
				counter.writeToFile();
			}
		}

		// on sauvegarde les nouveaux totaux pour la prochaine fois
		globalRequestsByCounter.put(counter, newGlobalRequest);

		// données de temps moyen pour les courbes par requête
		int size = requests.size();
		final Counter dayCounter = getCurrentDayCounter(counter);
		for (final CounterRequest newRequest : requests) {
			if (size > Counter.MAX_REQUEST_COUNT && newRequest.getHits() < 10) {
				// Si le nombre de requêtes est supérieur à 20000
				// on suppose que l'application a des requêtes sql non bindées
				// (bien que cela ne soit en général pas conseillé).
				// En tout cas, on essaye ici d'éviter de saturer
				// la mémoire (et le disque dur) avec toutes ces requêtes
				// différentes en éliminant celles ayant moins de 10 hits.
				removeRequest(counter, newRequest);
				size--;
				continue;
			}

			final String label = "Temps moyens (ms) de "
					+ newRequest.getName()
							.substring(0, Math.min(30, newRequest.getName().length()));
			final String requestStorageId = newRequest.getId();
			// on récupére les instances de jrobin même s'il n'y a pas pas de précédents totaux
			final JRobin requestJRobin = getRequestJRobin(requestStorageId, label);

			final CounterRequest request = requestsById.get(requestStorageId);
			if (request != null) {
				// idem : on clone et on soustrait les requêtes précédentes
				// sauf si c'est l'initialisation
				final CounterRequest lastPeriodRequest = newRequest.clone();
				lastPeriodRequest.removeHits(request);
				if (lastPeriodRequest.getHits() > 0) {
					// s'il n'y a pas eu de hits, alors la moyenne vaut -1 : elle n'a pas de sens
					requestJRobin.addValue(lastPeriodRequest.getMean());

					// aggrégation de la requête sur le compteur pour le jour courant
					dayCounter.addHits(lastPeriodRequest);
				}
			}
			requestsById.put(requestStorageId, newRequest);
		}
		dayCounter.writeToFile();
	}

	private Counter getCurrentDayCounter(Counter counter) throws IOException {
		Counter dayCounter = dayCountersByCounter.get(counter);
		final Calendar start = Calendar.getInstance();
		start.setTime(dayCounter.getStartDate());
		if (start.get(Calendar.DAY_OF_YEAR) != Calendar.getInstance().get(Calendar.DAY_OF_YEAR)) {
			// le jour a changé, on crée un compteur vide qui sera enregistré dans un nouveau fichier
			dayCounter = new PeriodCounterFactory(dayCounter).buildNewDayCounter();
			dayCountersByCounter.put(counter, dayCounter);
		}
		return dayCounter;
	}

	private void removeRequest(Counter counter, CounterRequest newRequest) {
		counter.removeRequest(newRequest.getName());
		requestsById.remove(newRequest.getId());
		final JRobin requestJRobin = requestJRobinsById.remove(newRequest.getId());
		if (requestJRobin != null) {
			requestJRobin.deleteFile();
		}
	}

	private JRobin getRequestJRobin(String requestId, String label) throws IOException {
		JRobin jrobin = requestJRobinsById.get(requestId);
		if (jrobin == null) {
			jrobin = JRobin.createInstance(getApplication(), requestId, label);
			requestJRobinsById.put(requestId, jrobin);
		}
		return jrobin;
	}

	private JRobin getCounterJRobin(String name, String label) throws IOException {
		JRobin jrobin = counterJRobins.get(name);
		if (jrobin == null) {
			jrobin = JRobin.createInstance(getApplication(), name, label);
			counterJRobins.put(name, jrobin);
		}
		return jrobin;
	}

	byte[] graph(String graphName, String period, int width, int height) throws IOException {
		JRobin jrobin = counterJRobins.get(graphName);
		if (jrobin == null) {
			jrobin = requestJRobinsById.get(graphName);
			if (jrobin == null) {
				// un graph n'est pas toujours de suite dans jrobin
				return null;
			}
		}
		return jrobin.graph(period, width, height);
	}

	Collection<JRobin> getCounterJRobins() {
		return Collections.unmodifiableCollection(counterJRobins.values());
	}

	void clearCounter(String counterName) {
		for (final Counter counter : counters) {
			if (counter.getName().equalsIgnoreCase(counterName)) {
				final List<CounterRequest> requests = counter.getRequests();
				// on réinitialise le counter
				counter.clear();
				// et on purge les données correspondantes du collector utilisées pour les deltas
				globalRequestsByCounter.remove(counter);
				for (final CounterRequest request : requests) {
					requestsById.remove(request.getId());
					requestJRobinsById.remove(request.getId());
				}
				break;
			}
		}
	}

	void stop() {
		try {
			timer.cancel();

			// on persiste les compteurs pour les relire à l'initialisation et ne pas perdre les stats
			for (final Counter counter : counters) {
				counter.writeToFile();
			}
		} catch (final IOException e) {
			// persistance échouée, tant pis
			printStackTrace(e);
		} finally {
			for (final Counter counter : counters) {
				counter.clear();
			}
			// ici on ne fait pas de nettoyage de la liste counters car cette méthode
			// est appelée sur la webapp monitorée quand il y a un serveur de collecte
			// et que cette liste est envoyée au serveur de collecte,
			// et on ne fait pas de nettoyage des maps qui servent dans le cas
			// où le monitoring de la webapp monitorée est appelée par un navigateur
			// directement même si il y a par ailleurs un serveur de collecte
			// (dans ce dernier cas les données sont bien sûr partielles)
		}
	}

	static void stopJRobin() {
		try {
			JRobin.stop();
		} catch (final Throwable t) { // NOPMD
			printStackTrace(t);
		}
	}

	static void printStackTrace(Throwable t) {
		// ne connaissant pas log4j ici, on ne sait pas loguer ailleurs que dans la sortie d'erreur
		t.printStackTrace(System.err);
	}

	/** {@inheritDoc} */
	@Override
	public String toString() {
		return getClass().getSimpleName() + "[application=" + getApplication() + ", periodMillis="
				+ periodMillis + ", counters=" + getCounters() + ']';
	}
}
