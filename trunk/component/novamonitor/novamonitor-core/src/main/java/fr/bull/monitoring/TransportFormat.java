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

import java.awt.datatransfer.DataFlavor;
import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.Serializable;
import java.util.Locale;

import com.thoughtworks.xstream.XStream;
import com.thoughtworks.xstream.converters.collections.MapConverter;

/**
 * Liste des formats de transport entre un serveur de collecte et une application monitorée
 * (hors protocole à priori http).
 * @author Emeric Vernat
 */
enum TransportFormat {
	/**
	 * Sérialisation java.
	 */
	SERIALIZED(DataFlavor.javaSerializedObjectMimeType),

	/**
	 * XML.
	 */
	XML("text/xml; charset=utf-8");

	// classe interne pour qu'elle ne soit pas chargée avec la classe TransportFormat
	// et qu'ainsi on ne dépende pas de XStream si on ne se sert pas du format xml
	private static final class XmlIO {
		private XmlIO() {
			super();
		}

		static void writeToXml(Serializable serializable, BufferedOutputStream bufferedOutput)
				throws IOException {
			final XStream xstream = createXStream();
			final OutputStreamWriter writer = new OutputStreamWriter(bufferedOutput,
					XML_CHARSET_NAME);
			try {
				xstream.toXML(serializable, writer);
			} finally {
				writer.close();
			}
		}

		static Object readFromXml(InputStream bufferedInput) throws IOException {
			final XStream xstream = createXStream();
			final InputStreamReader reader = new InputStreamReader(bufferedInput, XML_CHARSET_NAME);
			try {
				return xstream.fromXML(reader);
			} finally {
				reader.close();
			}
		}

		private static XStream createXStream() {
			final XStream xstream = new XStream(); // utilise la dépendance XPP3 par défaut
			xstream.alias("counter", Counter.class);
			xstream.alias("request", CounterRequest.class);
			xstream.alias("javaInformations", JavaInformations.class);
			xstream.alias("threadInformations", ThreadInformations.class);
			final MapConverter mapConverter = new MapConverter(xstream.getMapper()) {
				/** {@inheritDoc} */
				@SuppressWarnings("unchecked")
				@Override
				public boolean canConvert(Class type) {
					return true; // Counter.requests est bien une map
				}
			};
			xstream.registerLocalConverter(Counter.class, "requests", mapConverter);
			return xstream;
		}
	}

	private static final String XML_CHARSET_NAME = "utf-8";

	private final String mimeType;

	private TransportFormat(String mimeType) {
		this.mimeType = mimeType;
	}

	static TransportFormat valueOfIgnoreCase(String transportFormat) {
		return valueOf(transportFormat.toUpperCase(Locale.getDefault()).trim());
	}

	String getMimeType() {
		return mimeType;
	}

	void writeSerializableTo(Serializable serializable, OutputStream output) throws IOException {
		final BufferedOutputStream bufferedOutput = new BufferedOutputStream(output);
		switch (this) {
		case SERIALIZED:
			final ObjectOutputStream out = new ObjectOutputStream(bufferedOutput);
			try {
				out.writeObject(serializable);
			} finally {
				out.close();
			}
			break;
		case XML:
			// Rq : sans xstream et si jdk 1.6, on pourrait sinon utiliser
			// XMLStreamWriter et XMLStreamReader ou JAXB avec des annotations
			XmlIO.writeToXml(serializable, bufferedOutput);
			break;
		default:
			throw new IllegalStateException(toString());
		}
	}

	Serializable readSerializableFrom(InputStream input) throws IOException, ClassNotFoundException {
		final InputStream bufferedInput = new BufferedInputStream(input);
		Object result;
		switch (this) {
		case SERIALIZED:
			final ObjectInputStream in = new ObjectInputStream(bufferedInput);
			try {
				result = in.readObject();
			} finally {
				in.close();
			}
			break;
		case XML:
			result = XmlIO.readFromXml(bufferedInput);
			break;
		default:
			throw new IllegalStateException(toString());
		}
		if ("null".equals(result)) {
			result = null;
		}
		// c'est un Serializable que l'on a écrit
		return (Serializable) result;
	}
}
