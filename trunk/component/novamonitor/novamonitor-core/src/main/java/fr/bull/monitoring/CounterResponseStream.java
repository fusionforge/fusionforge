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

import javax.servlet.http.HttpServletResponse;

/**
 * Implémentation de ServletOutputStream qui fonctionne avec le CounterServletResponseWrapper.
 * @author Emeric Vernat
 */
class CounterResponseStream extends FilterServletOutputStream {
	private int dataLength;

	/**
	 * Construit un servlet output stream associé avec la réponse spécifiée.
	 * @param response HttpServletResponse
	 * @throws java.io.IOException   Erreur d'entrée/sortie
	 */
	CounterResponseStream(HttpServletResponse response) throws IOException {
		super(response.getOutputStream());
	}

	/**
	 * Retourne la valeur de la propriété dataLength.
	 * @return int
	 */
	public int getDataLength() {
		return dataLength;
	}

	/** {@inheritDoc} */
	@Override
	public void write(int i) throws IOException {
		super.write(i);
		dataLength += 1;
	}

	/** {@inheritDoc} */
	@Override
	public void write(byte[] bytes) throws IOException {
		super.write(bytes);
		final int len = bytes.length;
		dataLength += len;
	}

	/** {@inheritDoc} */
	@Override
	public void write(byte[] bytes, int off, int len) throws IOException {
		super.write(bytes, off, len);
		dataLength += len;
	}
}
