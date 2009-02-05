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

import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.util.Calendar;
import java.util.Collections;
import java.util.Date;
import java.util.Timer;
import java.util.TimerTask;

import javax.mail.MessagingException;
import javax.naming.NamingException;

/**
 * Génération de rapport pdf hebdomadaire et envoi par email aux administrateurs paramétrés.
 * @author Emeric Vernat
 */
class MailReport {
	static void scheduleReportMail(final Collector collector, final Timer timer) {
		final MailReport mailReport = new MailReport();
		final TimerTask task = new TimerTask() {
			/** {@inheritDoc} */
			@Override
			public void run() {
				try {
					// envoi du rapport
					mailReport.sendReportMail(collector);
				} catch (final Throwable t) { // NOPMD
					// pas d'erreur dans cette task
					printStackTrace(t);
				}
				// on reschedule à la même heure la semaine suivante sans utiliser de période de 24h*7
				// car certains jours font 23h ou 25h et on ne veut pas introduire de décalage
				scheduleReportMail(collector, timer);
			}
		};

		// schedule 1 fois la tâche
		timer.schedule(task, mailReport.getNextExecutionDate());
	}

	static void printStackTrace(Throwable t) {
		// ne connaissant pas log4j ici, on ne sait pas loguer ailleurs que dans la sortie d'erreur
		t.printStackTrace(System.err);
	}

	Date getNextExecutionDate() {
		// calcule de la date de prochaine exécution (le dimanche à minuit)
		final Calendar calendar = Calendar.getInstance();
		calendar.set(Calendar.DAY_OF_WEEK, Calendar.SUNDAY);
		calendar.set(Calendar.HOUR_OF_DAY, 0);
		calendar.set(Calendar.MINUTE, 0);
		calendar.set(Calendar.SECOND, 0);
		calendar.set(Calendar.MILLISECOND, 0);
		if (calendar.getTimeInMillis() < System.currentTimeMillis()) {
			// on utilise add et non roll pour ne pas tourner en boucle le 31/12
			calendar.add(Calendar.DAY_OF_YEAR, 7);
		}
		return calendar.getTime();
	}

	void sendReportMail(Collector collector) throws IOException, NamingException,
			MessagingException {
		final JavaInformations javaInformations = new JavaInformations(Parameters
				.getServletContext(), true);
		final File tmpFile = new File(System.getProperty("java.io.tmpdir"), PdfReport
				.getFileName(collector.getApplication()));
		try {
			final OutputStream output = new BufferedOutputStream(new FileOutputStream(tmpFile));
			try {
				final PdfReport pdfReport = new PdfReport(collector, Collections
						.singletonList(javaInformations), Period.SEMAINE.toString(), output);
				pdfReport.toPdf();
			} finally {
				output.close();
			}

			final String subject = "Monitoring sur " + collector.getApplication();
			final Mailer mailer = new Mailer("java:comp/env/"
					+ Parameters.getParameter(Parameter.MAIL_SESSION));
			final String adminEmails = Parameters.getParameter(Parameter.ADMIN_EMAILS);
			mailer.send(adminEmails, subject, "", Collections.singletonList(tmpFile), false);
		} finally {
			if (!tmpFile.delete()) {
				tmpFile.deleteOnExit();
			}
		}
	}
}
