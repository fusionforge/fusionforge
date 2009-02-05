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
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.Date;
import java.util.List;
import java.util.concurrent.ConcurrentHashMap;
import java.util.regex.Pattern;

/**
 * Données statistiques des requêtes pour un compteur nommé comme http ou sql.
 * Ces données sont accumulées au fil du temps selon les requêtes dans l'application.
 * Elles correspondent soit aux statistiques courantes depuis une date initiale,
 * soit à une période donnée pour un jour, une semaine, un mois ou une année.
 *
 * Toutes les méthodes sur une instance de cette classe sont conçues pour être thread-safe,
 * c'est-à-dire qu'elles gère un état qui est non modifiable
 * ou alors synchronisé pour être accessible et modifiable depuis plusieurs threads.
 * Les instances sont sérialisables pour pouvoir être persistées sur disque
 * et transmises au serveur de collecte.
 * @author Emeric Vernat
 */
class Counter implements Cloneable, Serializable { // NOPMD (false+: bug pmd 2230809 en v4.2.4)
	// nombre max de requêtes conservées par counter
	static final int MAX_REQUEST_COUNT = 20000;
	private static final long serialVersionUID = 6759729262180992976L;
	private String application;
	private boolean displayed;
	private final String name;
	private final String storageName;
	private final String iconName;
	// on conserve childCounterName et pas childCounter pour assurer la synchronisation/clone et la sérialisation
	private final String childCounterName;
	private final ConcurrentHashMap<String, CounterRequest> requests = new ConcurrentHashMap<String, CounterRequest>();
	private Date startDate = new Date();
	// Pour les contextes, on utilise un ThreadLocal et pas un InheritableThreadLocal
	// puisque si on crée des threads alors la requête parente peut se terminer avant les threads
	// et le contexte serait incomplet.
	private final transient ThreadLocal<CounterRequestContext> contextThreadLocal;
	private transient Pattern requestTransformPattern;

	static final class CounterRequestComparator implements Comparator<CounterRequest>, Serializable {
		private static final long serialVersionUID = 1L;

		/** {@inheritDoc} */
		public int compare(CounterRequest request1, CounterRequest request2) {
			if (request1.getDurationsSum() > request2.getDurationsSum()) {
				return 1;
			} else if (request1.getDurationsSum() < request2.getDurationsSum()) {
				return -1;
			} else {
				return 0;
			}
		}
	}

	/**
	 * Constructeur d'un compteur.
	 * @param name Nom du compteur (par exemple: sql...)
	 * @param iconName Icône du compteur (par exemple: db.png)
	 */
	Counter(String name, String iconName) {
		// ici, pas de compteur fils
		this(name, name, iconName, null, new ThreadLocal<CounterRequestContext>());
	}

	/**
	 * Constructeur d'un compteur.
	 * @param name Nom du compteur (par exemple: sql...)
	 * @param storageName Nom unique du compteur pour le stockage (par exemple: sql_20080724)
	 * @param iconName Icône du compteur (par exemple: db.png)
	 * @param childCounterName Nom du compteur fils (par exemple: sql)
	 */
	Counter(String name, String storageName, String iconName, String childCounterName) {
		// ici, pas de compteur fils
		this(name, storageName, iconName, childCounterName, new ThreadLocal<CounterRequestContext>());
	}

	/**
	 * Constructeur d'un compteur.
	 * @param name Nom du compteur (par exemple: http...)
	 * @param iconName Icône du compteur (par exemple: db.png)
	 * @param childCounter Compteur fils (par exemple: sqlCounter)
	 */
	Counter(String name, String iconName, Counter childCounter) {
		this(name, name, iconName, childCounter.getName(), childCounter.contextThreadLocal);
	}

	private Counter(String name, String storageName, String iconName, String childCounterName,
			ThreadLocal<CounterRequestContext> contextThreadLocal) {
		super();
		assert name != null;
		assert storageName != null;
		this.storageName = storageName;
		this.name = name;
		this.iconName = iconName;
		this.childCounterName = childCounterName;
		this.contextThreadLocal = contextThreadLocal;
	}

	void setApplication(String application) {
		// méthode utilisée dans le serveur de collecte
		assert application != null;
		this.application = application;
	}

	String getApplication() {
		return application;
	}

	String getName() {
		return name;
	}

	String getStorageName() {
		return storageName;
	}

	String getIconName() {
		return iconName;
	}

	String getChildCounterName() {
		return childCounterName;
	}

	Date getStartDate() {
		return startDate;
	}

	void setDisplayed(boolean displayed) {
		this.displayed = displayed;
	}

	boolean isDisplayed() {
		return displayed;
	}

	void setRequestTransformPattern(Pattern requestTransformPattern) {
		this.requestTransformPattern = requestTransformPattern;
	}

	Pattern getRequestTransformPattern() {
		return requestTransformPattern;
	}

	void bindContext() {
		contextThreadLocal.set(new CounterRequestContext(this, contextThreadLocal.get()));
	}

	void unbindContext() {
		contextThreadLocal.remove();
	}

	void addRequest(String requestName, long duration, boolean systemError, int responseSize) {
		// la méthode addRequest n'est pas synchronisée pour ne pas avoir
		// de synchronisation globale à l'application sur cette instance d'objet
		// ce qui pourrait faire une contention et des ralentissements,
		// par contre la map requests est synchronisée pour les modifications concurrentes

		assert requestName != null;
		assert duration >= 0;
		assert responseSize >= 0;

		final String aggregateRequestName;
		if (requestTransformPattern == null) {
			aggregateRequestName = requestName;
		} else {
			// ce pattern optionnel permet de transformer la description de la requête http
			// pour supprimer des parties variables (identifiant d'objet par exemple)
			// et pour permettre l'agrégation sur cette requête
			aggregateRequestName = requestTransformPattern.matcher(requestName).replaceAll("\\$");
		}

		final CounterRequestContext context = contextThreadLocal.get();
		final CounterRequest request = getCounterRequest(aggregateRequestName);
		synchronized (request) {
			// on synchronise par l'objet request pour éviter de mélanger des ajouts de hits
			// concurrents entre plusieurs threads pour le même tyre de requête.
			// Rq : on pourrait remplacer ce bloc synchronized par un synchronized
			// sur la méthode addHit dans la classe CounterRequest.
			request.addHit(duration, systemError, responseSize);

			if (context != null) {
				if (context.getParentCounter() == this) {
					// on ajoute dans la requête parente toutes les requêtes filles du contexte
					request.addChildHits(context);
					final CounterRequestContext parentContext = context.getParentContext();
					if (parentContext == null) {
						// enlève du threadLocal le contexte que j'ai créé
						// si je suis le counter parent et s'il n'y a pas de contexte parent
						contextThreadLocal.remove();
					} else {
						// ou reporte les requêtes filles dans le contexte parent et rebinde celui-ci
						parentContext.addChildRequests(context);
						contextThreadLocal.set(parentContext);
					}
				} else {
					// on ajoute une requête fille dans le contexte
					// (à priori il s'agit d'une requête sql)
					context.addChildRequest(this, aggregateRequestName, duration, systemError,
							responseSize);
				}
			}
		}
	}

	void addRequests(Counter newCounter) {
		assert getName().equals(newCounter.getName());

		// Pour toutes les requêtes du compteur en paramètre,
		// on ajoute les hits aux requêtes de ce compteur
		// (utilisée dans serveur de collecte).

		// Rq: cette méthode est thread-safe comme les autres méthodes dans cette classe,
		// bien que cela ne soit à priori pas nécessaire telle qu'elle est utilisée dans CollectorServlet
		for (final CounterRequest newRequest : newCounter.getRequests()) {
			if (newRequest.getHits() > 0) {
				final CounterRequest request = getCounterRequest(newRequest.getName());
				synchronized (request) {
					request.addHits(newRequest);
				}
			}
		}

		int size = requests.size();
		if (size > MAX_REQUEST_COUNT) {
			// Si le nombre de requêtes est supérieur à 20000 (sql non bindé par ex.),
			// on essaye ici d'éviter de saturer la mémoire (et le disque dur)
			// avec toutes ces requêtes différentes en éliminant celles ayant moins de 10 hits.
			// (utile pour une aggrégation par année dans PeriodCounterFactory par ex.)
			for (final CounterRequest request : requests.values()) {
				if (request.getHits() < 10) {
					removeRequest(request.getName());
					size--;
					if (size < MAX_REQUEST_COUNT) {
						break;
					}
				}
			}
		}
	}

	void addHits(CounterRequest counterRequest) {
		if (counterRequest.getHits() > 0) {
			// clone pour être thread-safe ici
			final CounterRequest newRequest = counterRequest.clone();
			final CounterRequest request = getCounterRequest(newRequest.getName());
			synchronized (request) {
				request.addHits(newRequest);
			}
		}
	}

	void removeRequest(String requestName) {
		assert requestName != null;
		requests.remove(requestName);
	}

	private CounterRequest getCounterRequest(String requestName) {
		CounterRequest request = requests.get(requestName);
		if (request == null) {
			request = new CounterRequest(requestName, getName());
			// putIfAbsent a l'avantage d'être garanti atomique, même si ce n'est pas indispensable
			final CounterRequest precedentRequest = requests.putIfAbsent(requestName, request);
			if (precedentRequest != null) {
				request = precedentRequest;
			}
		}
		return request;
	}

	/**
	 * @return Liste des requêtes non triées,
	 * 	la liste et ses objets peuvent être utilisés sans synchronized et sans crainte d'accès concurrents.
	 */
	List<CounterRequest> getRequests() {
		// thread-safe :
		// on crée une copie de la collection et on clone ici chaque CounterRequest de manière synchronizée
		// de manière à ce que l'appelant n'est pas à se préoccuper des synchronisations nécessaires
		// Rq : l'Iterator sur ConcurrentHashMap.values() est garanti ne pas lancer ConcurrentModificationException
		// même s'il y a des ajouts concurrents
		final List<CounterRequest> result = new ArrayList<CounterRequest>(requests.size());
		for (final CounterRequest request : requests.values()) {
			// on synchronize sur request en cas d'ajout en parallèle d'un hit sur cette request
			synchronized (request) {
				result.add(request.clone());
			}
		}
		return result;
	}

	/**
	 * @return Liste des requêtes triées par durée cumulée décroissante,
	 * 	la liste et ses objets peuvent être utilisés sans synchronized et sans crainte d'accès concurrents.
	 */
	List<CounterRequest> getOrderedRequests() {
		final List<CounterRequest> requestList = getRequests();
		if (requestList.size() > 1) {
			Collections.sort(requestList, new CounterRequestComparator());
			Collections.reverse(requestList);
		}
		return requestList;
	}

	void clear() {
		requests.clear();
		startDate = new Date();
	}

	/** {@inheritDoc} */
	@Override
	//CHECKSTYLE:OFF
	public Counter clone() { // NOPMD
		//CHECKSTYLE:ON
		final Counter counter = new Counter(getName(), getStorageName(), getIconName(),
				getChildCounterName(), new ThreadLocal<CounterRequestContext>());
		counter.application = getApplication();
		counter.startDate = getStartDate();
		counter.displayed = isDisplayed();
		counter.requestTransformPattern = getRequestTransformPattern();
		// on ne copie pas contextThreadLocal,
		// la méthode getRequests() clone les instances de CounterRequest
		for (final CounterRequest request : getRequests()) {
			counter.requests.put(request.getName(), request);
		}
		return counter;
	}

	void writeToFile() throws IOException {
		// on clone le counter avant de le sérialiser pour ne pas avoir de problèmes de concurrences d'accès
		new CounterStorage(this.clone()).writeToFile();
	}

	void readFromFile() throws IOException, ClassNotFoundException {
		final Counter counter = new CounterStorage(this).readFromFile();
		if (counter != null) {
			final Counter newCounter = clone();
			startDate = counter.getStartDate();
			requests.clear();
			for (final CounterRequest request : counter.getRequests()) {
				requests.put(request.getName(), request);
			}
			// on ajoute les nouvelles requêtes enregistrées avant de lire le fichier
			// (par ex. les premières requêtes collectées par le serveur de collecte lors de l'initialisation)
			addRequests(newCounter);
		}
	}

	/** {@inheritDoc} */
	@Override
	public String toString() {
		return getClass().getSimpleName() + "[application=" + getApplication() + ", name="
				+ getName() + ", storageName=" + getStorageName() + ", startDate=" + getStartDate()
				+ ", childCounterName=" + getChildCounterName() + ", " + requests.size()
				+ " requests, displayed=" + isDisplayed() + ']';
	}
}
