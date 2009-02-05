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

import java.io.File;
import java.io.FilenameFilter;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;

/**
 * Factory pour les compteurs par jour, par semaine, par mois et par année.
 * @author Emeric Vernat
 */
class PeriodCounterFactory {
	// Note d'implémentation : Calendar.getInstance() crée à chaque appel une nouvelle instance
	// de Calendar à la date et à l'heure courante (cette date-heure peut être modifiée)

	private final Counter currentDayCounter;

	PeriodCounterFactory(Counter currentDayCounter) {
		super();
		assert currentDayCounter != null;
		this.currentDayCounter = currentDayCounter;
	}

	Counter buildNewDayCounter() throws IOException {
		// 1 fois par jour on supprime tous les fichiers .ser modifiés il y a plus d'un an
		// (malheureusement les dates de modification des fichiers rrd ne sont pas mises à jour
		// par jrobin quand jrobin y ajoute des valeurs)
		deleteObsoleteCounterFiles(currentDayCounter.getApplication());

		final Calendar start = Calendar.getInstance();
		start.setTime(currentDayCounter.getStartDate());
		if (start.get(Calendar.MONTH) != Calendar.getInstance().get(Calendar.MONTH)) {
			// le mois a changé, on crée un compteur vide qui sera enregistré dans un nouveau fichier;
			// ce compteur agrégé pour le mois est utilisé pour de meilleurs performances sur le compteur de l'année
			// on calcule le monthCounter et on l'enregistre (optimisation pour getYearCounter)
			getMonthCounterAtDate(currentDayCounter.getStartDate());
		}

		return createDayCounterAtDate(new Date());
	}

	// compteur d'un jour donné
	private Counter getDayCounterAtDate(Date day) {
		final Counter dayCounter = createDayCounterAtDate(day);
		try {
			dayCounter.readFromFile();
		} catch (final IOException e) {
			// lecture échouée, tant pis
			// (on n'interrompt pas tout un rapport juste pour un des fichiers illisible)
			printStackTrace(e);
		} catch (final ClassNotFoundException e) {
			// idem
			printStackTrace(e);
		}
		return dayCounter;
	}

	// compteur des 7 derniers jours
	Counter getWeekCounter() {
		final Counter weekCounter = createPeriodCounter("yyyyWW", currentDayCounter.getStartDate());
		weekCounter.addRequests(currentDayCounter);
		final Calendar dayCalendar = Calendar.getInstance();
		dayCalendar.setTime(currentDayCounter.getStartDate());
		for (int i = 1; i < 7; i++) {
			dayCalendar.add(Calendar.DAY_OF_YEAR, -1);
			weekCounter.addRequests(getDayCounterAtDate(dayCalendar.getTime()));
		}
		return weekCounter;
	}

	// compteur des 31 derniers jours
	Counter getMonthCounter() {
		final Counter monthCounter = createMonthCounterAtDate(currentDayCounter.getStartDate());
		monthCounter.addRequests(currentDayCounter);
		final Calendar dayCalendar = Calendar.getInstance();
		dayCalendar.setTime(currentDayCounter.getStartDate());
		for (int i = 1; i < 31; i++) {
			// ici c'est un mois flottant (ie une durée), et pas un mois entier
			dayCalendar.add(Calendar.DAY_OF_YEAR, -1);
			monthCounter.addRequests(getDayCounterAtDate(dayCalendar.getTime()));
		}
		return monthCounter;
	}

	// compteur des 366 derniers jours
	Counter getYearCounter() throws IOException {
		final Counter yearCounter = createPeriodCounter("yyyy", currentDayCounter.getStartDate());
		yearCounter.addRequests(currentDayCounter);
		final Calendar dayCalendar = Calendar.getInstance();
		final int currentMonth = dayCalendar.get(Calendar.MONTH);
		dayCalendar.setTime(currentDayCounter.getStartDate());
		dayCalendar.add(Calendar.DAY_OF_YEAR, -366);
		for (int i = 1; i < 366; i++) {
			dayCalendar.add(Calendar.DAY_OF_YEAR, 1);
			if (dayCalendar.get(Calendar.DAY_OF_MONTH) == 1
					&& dayCalendar.get(Calendar.MONTH) != currentMonth) {
				// optimisation : on récupère les statistiques précédemment calculées pour ce mois entier
				// au lieu de parcourir à chaque fois les statistiques de chaque jour du mois
				yearCounter.addRequests(getMonthCounterAtDate(dayCalendar.getTime()));
				final int nbDaysInMonth = dayCalendar.getActualMaximum(Calendar.DAY_OF_MONTH);
				// nbDaysInMonth - 1 puisque l'itération va ajouter 1 à i et à dayCalendar
				dayCalendar.add(Calendar.DAY_OF_YEAR, nbDaysInMonth - 1);
				i += nbDaysInMonth - 1;
			} else {
				yearCounter.addRequests(getDayCounterAtDate(dayCalendar.getTime()));
			}
		}
		return yearCounter;
	}

	private Counter getMonthCounterAtDate(Date day) throws IOException {
		final Counter monthCounter = createMonthCounterAtDate(day);
		try {
			final Counter readCounter = new CounterStorage(monthCounter).readFromFile();
			if (readCounter != null) {
				// monthCounter déjà calculé et enregistré
				return readCounter;
			}
		} catch (final IOException e) {
			// lecture échouée, tant pis
			// (on n'interrompt pas tout un rapport juste pour un des fichiers illisible)
			printStackTrace(e);
		} catch (final ClassNotFoundException e) {
			// idem
			printStackTrace(e);
		}
		// monthCounter n'est pas encore calculé (il est calculé à la fin de chaque mois,
		// mais le serveur a pu aussi être arrêté ce jour là),
		// alors on le calcule et on l'enregistre (optimisation pour getYearCounter)
		final Calendar dayCalendar = Calendar.getInstance();
		dayCalendar.setTime(day);
		final int nbDaysInMonth = dayCalendar.getActualMaximum(Calendar.DAY_OF_MONTH);
		for (int i = 1; i <= nbDaysInMonth; i++) {
			dayCalendar.set(Calendar.DAY_OF_MONTH, i);
			monthCounter.addRequests(getDayCounterAtDate(dayCalendar.getTime()));
		}
		monthCounter.writeToFile();
		return monthCounter;
	}

	Counter createDayCounterAtDate(Date day) {
		// le nom du compteur par jour est celui du compteur inital
		// auquel on ajoute la date en suffixe pour que son enregistrement soit unique
		return createPeriodCounter("yyyyMMdd", day);
	}

	private Counter createMonthCounterAtDate(Date day) {
		// le nom du compteur par mois est celui du compteur inital
		// auquel on ajoute le mois en suffixe pour que son enregistrement soit unique
		return createPeriodCounter("yyyyMM", day);
	}

	private Counter createPeriodCounter(String dateFormatPattern, Date date) {
		final String storageName = currentDayCounter.getName() + '_'
				+ new SimpleDateFormat(dateFormatPattern, Locale.getDefault()).format(date);
		// ceci crée une nouvelle instance sans requêtes avec startDate à la date courante
		final Counter result = new Counter(currentDayCounter.getName(), storageName,
				currentDayCounter.getIconName(), currentDayCounter.getChildCounterName());
		result.setApplication(currentDayCounter.getApplication());
		result.setDisplayed(currentDayCounter.isDisplayed());
		result.setRequestTransformPattern(currentDayCounter.getRequestTransformPattern());
		return result;
	}

	private static boolean deleteObsoleteCounterFiles(String application) {
		final File storageDir = new File(Parameters.getStorageDirectory(application));
		final Calendar nowMinusOneYearAndADay = Calendar.getInstance();
		nowMinusOneYearAndADay.add(Calendar.YEAR, -1);
		nowMinusOneYearAndADay.add(Calendar.DAY_OF_YEAR, -1);
		boolean result = true;
		// filtre pour ne garder que les fichiers d'extension .ser et pour éviter d'instancier des File inutiles
		final FilenameFilter filenameFilter = new FilenameFilter() {
			/** {@inheritDoc} */
			public boolean accept(File dir, String name) {
				return name.endsWith(".ser.gz");
			}
		};
		for (final File file : storageDir.listFiles(filenameFilter)) {
			if (file.lastModified() < nowMinusOneYearAndADay.getTimeInMillis()) {
				result = result && file.delete();
			}
		}
		// on retourne true si tous les fichiers ser obsolètes ont été supprimés, false sinon
		return result;
	}

	private void printStackTrace(Throwable t) {
		// ne connaissant pas log4j ici, on ne sait pas loguer ailleurs que dans la sortie d'erreur
		t.printStackTrace(System.err);
	}
}
