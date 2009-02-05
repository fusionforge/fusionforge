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
import java.io.OutputStream;

import javax.servlet.ServletOutputStream;

/**
 * Output stream pour un filtre implémentant ServletOutputStream à partir d'un OutputStream.
 * @author Emeric Vernat
 */
class FilterServletOutputStream extends ServletOutputStream {
	private final OutputStream stream;

	/**
	 * Constructeur.
	 * @param output OutputStream
	 */
	FilterServletOutputStream(OutputStream output) {
		super();
		assert output != null;
		stream = output;
	}

	/** {@inheritDoc} */
	@Override
	public void close() throws IOException {
		stream.close();
	}

	/** {@inheritDoc} */
	@Override
	public void flush() throws IOException {
		stream.flush();
	}

	/** {@inheritDoc} */
	@Override
	public void write(int b) throws IOException {
		stream.write(b);
	}

	/** {@inheritDoc} */
	@Override
	public void write(byte[] bytes) throws IOException {
		stream.write(bytes);
	}

	/** {@inheritDoc} */
	@Override
	public void write(byte[] bytes, int off, int len) throws IOException {
		stream.write(bytes, off, len);
	}
}
