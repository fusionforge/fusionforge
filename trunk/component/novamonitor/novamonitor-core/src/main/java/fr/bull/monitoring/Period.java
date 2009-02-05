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

import java.util.Locale;

/**
 * Enumération des périodes possibles.
 * @author Emeric Vernat
 */
enum Period {
	/** Jour. */
	JOUR("1 jour", "Jour", 24 * 60 * 60),
	/** Semaine. */
	SEMAINE("1 semaine", "Semaine", 7 * 24 * 60 * 60),
	/** Mois. */
	MOIS("1 mois", "Mois", 31 * 24 * 60 * 60),
	/** Année. */
	ANNEE("1 an", "Année", 366 * 24 * 60 * 60),
	/** Tout.
	 * (affiche les graphs sur 2 ans et toutes les requêtes y compris les dernières minutes) */
	TOUT("tout", "Tout", 2 * 366 * 24 * 60 * 60);

	private final String label;
	private final String linkLabel;
	private final int durationSeconds;

	private Period(String label, String linkLabel, int durationSeconds) {
		this.label = label;
		this.linkLabel = linkLabel;
		this.durationSeconds = durationSeconds;
	}

	static Period valueOfIgnoreCase(String period) {
		return valueOf(period.toUpperCase(Locale.getDefault()).trim());
	}

	String getLabel() {
		return label;
	}

	String getLinkLabel() {
		return linkLabel;
	}

	int getDurationSeconds() {
		return durationSeconds;
	}
}
