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
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

/**
 * Données statistiques d'une requête identifiée hors paramètres dynamiques comme un identifiant.
 *
 * Les méthodes d'une instance de cette classe ne sont pas thread-safe.
 * L'état d'une instance doit être accédé ou modifié par l'intermédiaire d'une instance de Counter,
 * qui gèrera les accès concurrents sur les instances de cette classe.
 * @author Emeric Vernat
 */
class CounterRequest implements Cloneable, Serializable {
	private static final long serialVersionUID = -4301825473892026959L;
	private final String name;
	private final String id;
	// tous ces champs de type long sont initialisés à 0,
	// il peut être supposé que le type long est suffisant
	// sans dépassement de capacité (max : 2^63-1 soit un peu moins de 10^19)
	// et le type long est préféré au type BigInteger pour raison de performances
	private long hits;
	private long durationsSum;
	private long durationsSquareSum;
	private long maximum;
	private long systemErrors;
	private long responseSizesSum;
	private long childHits;
	private long childDurationsSum;

	CounterRequest(String name, String counterName) {
		super();
		assert name != null;
		assert counterName != null;
		this.name = name;
		this.id = buildId(name, counterName);
	}

	String getName() {
		return name;
	}

	String getId() {
		return id;
	}

	long getHits() {
		return hits;
	}

	long getDurationsSum() {
		return durationsSum;
	}

	int getMean() {
		if (hits > 0) {
			return (int) (durationsSum / hits);
		}
		return -1;
	}

	// retourne l'écart type (ou sigma, standard deviation en anglais)
	int getStandardDeviation() {
		//    soit un ensemble de valeurs Xi
		//    la moyenne est m = somme(Xi) / n,
		//    la déviation de chaque valeur par rapport à la moyenne est di = Xi - m
		//    la variance des valeurs est V = somme(di^2)/(n-1) = somme( (Xi-m)^2 ) / (n-1)
		//			dont on peut dériver V = (somme (Xi^2) - (somme(Xi)^2) / n) / (n-1)
		//			(dont une approximation est V = somme (Xi^2) / n - m^2 mais seulement quand n est élevé).
		//			Cela ne nécessite que de retenir la somme des Xi et la somme des Xi^2
		//			car on ne souhaite pas conserver toutes les valeurs des Xi pour ne pas saturer la mémoire,
		//    et l'écart type (ou sigma) est la racine carrée de la variance : s = sqrt(V)
		//
		//    on dit alors la moyenne est m +/- s

		// Références :
		//      http://web.archive.org/web/20070710000323/http://www.med.umkc.edu/tlwbiostats/variability.html
		//      http://web.archive.org/web/20050512031826/http://helios.bto.ed.ac.uk/bto/statistics/tress3.html
		//		http://www.bmj.com/collections/statsbk/2.html
		if (hits > 0) {
			return (int) Math.sqrt((durationsSquareSum - (double) durationsSum * durationsSum
					/ hits)
					/ (hits - 1));
		}
		return -1;
	}

	long getMaximum() {
		return maximum;
	}

	float getSystemErrorPercentage() {
		// pourcentage d'erreurs systèmes entre 0 et 100,
		// le type de retour est float pour être mesurable
		// car il est probable que le pourcentage soit inférieur à 1%
		if (hits > 0) {
			return 100f * systemErrors / hits;
		}
		return 0;
	}

	int getResponseSizeMean() {
		if (hits > 0) {
			return (int) (responseSizesSum / hits);
		}
		return -1;
	}

	int getChildHitsMean() {
		if (hits > 0) {
			return (int) (childHits / hits);
		}
		return -1;
	}

	int getChildDurationsMean() {
		if (hits > 0) {
			return (int) (childDurationsSum / hits);
		}
		return -1;
	}

	void addHit(long duration, boolean systemError, int responseSize) {
		hits++;
		durationsSum += duration;
		durationsSquareSum += duration * duration;
		if (duration > maximum) {
			maximum = duration;
		}
		if (systemError) {
			systemErrors++;
		}
		responseSizesSum += responseSize;
	}

	void addChildHits(ICounterRequestContext context) {
		childHits += context.getChildHits();
		childDurationsSum += context.getChildDurationsSum();
	}

	void addHits(CounterRequest request) {
		assert request != null;
		if (request.hits != 0) {
			hits += request.hits;
			durationsSum += request.durationsSum;
			durationsSquareSum += request.durationsSquareSum;
			if (request.maximum > maximum) {
				maximum = request.maximum;
			}
			systemErrors += request.systemErrors;
			responseSizesSum += request.responseSizesSum;
			childHits += request.childHits;
			childDurationsSum += request.childDurationsSum;
		}
	}

	void removeHits(CounterRequest request) {
		assert request != null;
		if (request.hits != 0) {
			hits -= request.hits;
			durationsSum -= request.durationsSum;
			durationsSquareSum -= request.durationsSquareSum;
			// on ne peut pas enlever le maximum puisqu'on ne connaît pas le précédent maximum
			//			if (request.maximum > maximum) {
			//				maximum = request.maximum;
			//			}
			systemErrors -= request.systemErrors;
			responseSizesSum -= request.responseSizesSum;
			childHits -= request.childHits;
			childDurationsSum -= request.childDurationsSum;
		}
	}

	/** {@inheritDoc} */
	@Override
	public CounterRequest clone() { // NOPMD
		try {
			return (CounterRequest) super.clone();
		} catch (final CloneNotSupportedException e) {
			// ne peut arriver puisque CounterRequest implémente Cloneable
			throw new IllegalStateException(e);
		}
	}

	// retourne l'id supposé unique de la requête pour le stockage
	private static String buildId(String name, String counterName) {
		try {
			// SHA1 est un algorithme de hashage qui évite les conflits à 2^80 près entre
			// les identifiants supposés uniques (SHA1 est mieux que MD5 qui est mieux que CRC32).
			final MessageDigest messageDigest = MessageDigest.getInstance("SHA-1");
			messageDigest.update(name.getBytes());
			final byte[] digest = messageDigest.digest();

			final StringBuilder sb = new StringBuilder(digest.length * 2);
			sb.append(counterName);
			// encodage en chaîne héxadécimale,
			// puisque les caractères bizarres ne peuvent être utilisés sur un système de fichiers
			int j;
			for (final byte element : digest) {
				j = element < 0 ? 256 + element : element;
				if (j < 16) {
					sb.append('0');
				}
				sb.append(Integer.toHexString(j));
			}

			return sb.toString();
		} catch (final NoSuchAlgorithmException e) {
			// ne peut arriver car SHA1 est un algorithme disponible par défaut dans le JDK Sun
			throw new IllegalStateException(e);
		}
	}

	/** {@inheritDoc} */
	@Override
	public String toString() {
		return getClass().getSimpleName() + "[name=" + getName() + ", hits=" + getHits() + ", id="
				+ getId() + ']';
	}
}
