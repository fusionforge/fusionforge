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
import java.util.Date;
import java.util.List;

import javax.activation.DataHandler;
import javax.activation.DataSource;
import javax.activation.FileDataSource;
import javax.mail.Message;
import javax.mail.MessagingException;
import javax.mail.Multipart;
import javax.mail.Session;
import javax.mail.Transport;
import javax.mail.internet.InternetAddress;
import javax.mail.internet.MimeBodyPart;
import javax.mail.internet.MimeMessage;
import javax.mail.internet.MimeMultipart;
import javax.naming.InitialContext;
import javax.naming.NamingException;

/**
 * Cette classe permet d'envoyer des emails.
 * @author Emeric Vernat
 */
class Mailer {
	private final String jndiName;
	private Session session;
	private InternetAddress fromAddress;

	/**
	 * Constructeur.
	 * @param jndiName String
	 */
	Mailer(String jndiName) {
		super();
		this.jndiName = jndiName;
	}

	/**
	 * Instanciation à la demande car cela peut être long en particulier si le serveur de mail est loin.
	 * @return Session
	 * @throws NamingException e
	 */
	private Session getSession() throws NamingException {
		if (session == null) {
			synchronized (this) {
				final InitialContext ctx = new InitialContext();
				session = (Session) ctx.lookup(jndiName);
			}
			fromAddress = InternetAddress.getLocalAddress(session);
		}
		return session;
	}

	/**
	 * Envoye un mail.
	 * @param toAddress Adresses mails des destinataires séparées par des virgules.
	 * @param subject Titre du mail
	 * @param message Corps du mail
	 * @param attachments Liste de fichiers à attacher
	 * @param highPriority Priorité haute
	 * @throws NamingException e
	 * @throws MessagingException e
	 */
	void send(String toAddress, String subject, String message, List<File> attachments,
			boolean highPriority) throws NamingException, MessagingException {
		final InternetAddress[] toAddresses = InternetAddress.parse(toAddress, false);
		final Message msg = new MimeMessage(getSession());
		msg.setRecipients(Message.RecipientType.TO, toAddresses);
		msg.setSubject(subject);
		msg.setSentDate(new Date());
		msg.setFrom(fromAddress);
		if (highPriority) {
			msg.setHeader("X-Priority", "1");
			msg.setHeader("x-msmail-priority", "high");
		}

		// Content is stored in a MIME multi-part message with one body part
		final MimeBodyPart mbp = new MimeBodyPart();
		mbp.setText(message);
		final Multipart multipart = new MimeMultipart();
		multipart.addBodyPart(mbp);
		if (attachments != null && !attachments.isEmpty()) {
			for (final File attachment : attachments) {
				final DataSource source = new FileDataSource(attachment);
				final MimeBodyPart messageBodyPart = new MimeBodyPart();
				messageBodyPart.setDataHandler(new DataHandler(source));
				messageBodyPart.setFileName(attachment.getName());
				multipart.addBodyPart(messageBodyPart);
			}
		}
		msg.setContent(multipart);
		// protocol smtp, ou smpts si mail.transport.protocol=smtps est indiqué dans la configuration de la session mail
		String protocol = session.getProperty("mail.transport.protocol");
		if (protocol == null) {
			protocol = "smtp";
		}
		// authentification avec user et password si mail.smtp.auth=true (ou mail.smtps.auth=true)
		if (Boolean.parseBoolean(session.getProperty("mail." + protocol + ".auth"))) {
			final Transport tr = session.getTransport(protocol);
			try {
				tr.connect(session.getProperty("mail." + protocol + ".user"), session
						.getProperty("mail." + protocol + ".password"));
				msg.saveChanges(); // don't forget this
				tr.sendMessage(msg, msg.getAllRecipients());
			} finally {
				tr.close();
			}
		} else {
			Transport.send(msg);
		}
	}
}
