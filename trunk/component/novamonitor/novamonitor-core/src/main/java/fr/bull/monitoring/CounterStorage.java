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

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.util.zip.GZIPInputStream;
import java.util.zip.GZIPOutputStream;

/**
 * Classe chargée de l'enregistrement et de la lecture d'un counter.
 * @author Emeric Vernat
 */
class CounterStorage {
	private final Counter counter;

	CounterStorage(Counter counter) {
		super();
		this.counter = counter;
	}

	void writeToFile() throws IOException {
		final File file = new File(Parameters.getStorageDirectory(counter.getApplication()) + '/'
				+ counter.getStorageName() + ".ser.gz");
		final FileOutputStream out = new FileOutputStream(file);
		try {
			final ObjectOutputStream output = new ObjectOutputStream(new GZIPOutputStream(
					new BufferedOutputStream(out)));
			try {
				// on clone le counter avant de le sérialiser pour ne pas avoir de problèmes de concurrences d'accès
				output.writeObject(counter);
			} finally {
				// ce close libère les ressources du ObjectOutputStream et du GZIPOutputStream
				output.close();
			}
		} finally {
			out.close();
		}
	}

	Counter readFromFile() throws IOException, ClassNotFoundException {
		final File file = new File(Parameters.getStorageDirectory(counter.getApplication()) + '/'
				+ counter.getStorageName() + ".ser.gz");
		if (file.exists()) {
			final FileInputStream in = new FileInputStream(file);
			try {
				final ObjectInputStream input = new ObjectInputStream(new GZIPInputStream(
						new BufferedInputStream(in)));
				try {
					// on retourne l'instance du counter lue
					return (Counter) input.readObject();
				} finally {
					// ce close libère les ressources du ObjectInputStream et du GZIPInputStream
					input.close();
				}
			} finally {
				in.close();
			}
		}
		// ou on retourne null si le fichier n'existe pas
		return null;
	}
}
