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

/**
 * Contexte d'une requête pour un compteur (non synchronisé).
 * Le contexte sera initialisé dans un ThreadLocal puis sera utilisé à l'enregistrement de la requête parente.
 * Par exemple, le contexte d'une requête http a zéro ou plusieurs requêtes sql.
 * @author Emeric Vernat
 */
class CounterRequestContext implements ICounterRequestContext {
	private final Counter parentCounter;
	private final CounterRequestContext parentContext;
	// le type int suffit pour ces 2 champs (initialisés à 0)
	private int childHits;
	private int childDurationsSum;

	CounterRequestContext(Counter parentCounter, CounterRequestContext parentContext) {
		super();
		assert parentCounter != null;
		this.parentCounter = parentCounter;
		// parentContext est non null si on a ejb dans http
		// et il est null pour http ou pour ejb sans http
		this.parentContext = parentContext;
	}

	Counter getParentCounter() {
		return parentCounter;
	}

	CounterRequestContext getParentContext() {
		return parentContext;
	}

	/** {@inheritDoc} */
	public int getChildHits() {
		return childHits;
	}

	/** {@inheritDoc} */
	public long getChildDurationsSum() {
		return childDurationsSum;
	}

	// pour l'instant on ne conserve que le nombre de hits et la durée totale des requêtes filles,
	// mais on pourrait envisager pour un drill-down de conserver pour chaque requête mère
	// des statistiques sur quelles sont les requêtes filles appelées
	@SuppressWarnings("unused")
	void addChildRequest(Counter childCounter, String requestName, long duration,
			boolean systemError, int responseSize) {
		childHits++;
		childDurationsSum += duration;
	}

	void addChildRequests(CounterRequestContext childContext) {
		childHits += childContext.getChildHits();
		childDurationsSum += childContext.getChildDurationsSum();
	}
}
