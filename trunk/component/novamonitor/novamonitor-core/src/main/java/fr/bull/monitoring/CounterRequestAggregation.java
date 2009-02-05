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

import java.util.List;

/**
 * Aggrégation des requêtes d'un compteur pour l'affichage d'une synthèse.
 * @author Emeric Vernat
 */
class CounterRequestAggregation {
	private final List<CounterRequest> requests;
	private final CounterRequest globalRequest;
	private final int warningThreshold;
	private final int severeThreshold;
	private final boolean responseSizeDisplayed;
	private final boolean childHitsDisplayed;
	private final CounterRequest warningRequest;
	private final CounterRequest severeRequest;

	CounterRequestAggregation(Counter counter) {
		super();
		assert counter != null;
		this.requests = counter.getOrderedRequests();
		assert requests != null;

		final String counterName = counter.getName();
		this.globalRequest = new CounterRequest(counterName + " global", counterName);
		for (final CounterRequest request : requests) {
			// ici, pas besoin de synchronized sur request puisque ce sont des clones indépendants
			globalRequest.addHits(request);
		}

		// on n'affiche pas la colonne "Taille de réponse" si elle est négative car non défini
		// (pour les requêtes sql par exemple)
		this.responseSizeDisplayed = globalRequest.getResponseSizeMean() >= 0;
		this.childHitsDisplayed = globalRequest.getChildHitsMean() > 0;

		// globalMean et globalStandardDeviation sont utilisées pour déterminer
		// les seuils des couleurs des moyennes dans le tableau quand les paramètres
		// warning-threshold-millis et severe-threshold-millis ne sont pas définis
		final int globalMean = globalRequest.getMean();
		final int globalStandardDeviation = globalRequest.getStandardDeviation();
		final String paramWarning = Parameters.getParameter(Parameter.WARNING_THRESHOLD_MILLIS);
		if (paramWarning == null) {
			this.warningThreshold = globalMean + globalStandardDeviation;
		} else {
			this.warningThreshold = Integer.parseInt(paramWarning);
			if (this.warningThreshold <= 0) {
				throw new IllegalStateException(
						"Le paramètre warning-threshold-millis doit être > 0");
			}
		}
		final String paramSevere = Parameters.getParameter(Parameter.SEVERE_THRESHOLD_MILLIS);
		if (paramWarning == null) {
			this.severeThreshold = globalMean + 2 * globalStandardDeviation;
		} else {
			this.severeThreshold = Integer.parseInt(paramSevere);
			if (this.severeThreshold <= 0) {
				throw new IllegalStateException(
						"Le paramètre severe-threshold-millis doit être > 0");
			}
		}

		// synthèse globale avec requêtes global, warning et severe
		// on calcule les pourcentages de requêtes dont les temps (moyens!) dépassent les 2 seuils
		this.warningRequest = new CounterRequest(counterName + " warning", counterName);
		this.severeRequest = new CounterRequest(counterName + " severe", counterName);
		for (final CounterRequest request : requests) {
			// ici, pas besoin de synchronized sur request puisque ce sont des clones indépendants
			final int mean = request.getMean();
			if (mean > severeThreshold) {
				severeRequest.addHits(request);
			} else if (mean > warningThreshold) {
				warningRequest.addHits(request);
			}
			// les requêtes sous warning ne sont pas décomptées dans la synthèse autrement que dans global
		}
	}

	List<CounterRequest> getRequests() {
		return requests;
	}

	CounterRequest getGlobalRequest() {
		return globalRequest;
	}

	CounterRequest getWarningRequest() {
		return warningRequest;
	}

	CounterRequest getSevereRequest() {
		return severeRequest;
	}

	int getWarningThreshold() {
		return warningThreshold;
	}

	int getSevereThreshold() {
		return severeThreshold;
	}

	boolean isResponseSizeDisplayed() {
		return responseSizeDisplayed;
	}

	boolean isChildHitsDisplayed() {
		return childHitsDisplayed;
	}
}
