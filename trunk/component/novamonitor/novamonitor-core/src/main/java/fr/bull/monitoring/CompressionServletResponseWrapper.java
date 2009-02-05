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

import javax.servlet.ServletOutputStream;
import javax.servlet.http.HttpServletResponse;

/**
 * Implémentation de FilterServletResponseWrapper qui fonctionne avec le CompressionServletResponseStream.
 * @author Emeric Vernat
 */
class CompressionServletResponseWrapper extends FilterServletResponseWrapper {
	private final int compressionThreshold;

	/**
	 * Constructeur qui crée un adapteur de ServletResponse wrappant la response spécifiée.
	 * @param response HttpServletResponse
	 * @param compressionThreshold int
	 */
	CompressionServletResponseWrapper(HttpServletResponse response, int compressionThreshold) {
		super(response);
		assert compressionThreshold >= 0;
		this.compressionThreshold = compressionThreshold;
	}

	/** {@inheritDoc} */
	@Override
	public ServletOutputStream createOutputStream() {
		return new CompressionResponseStream((HttpServletResponse) getResponse(),
				compressionThreshold);
	}

	/**
	 * Termine et ferme la response.
	 * @throws IOException e
	 */
	void finishResponse() throws IOException {
		close();
	}

	/**
	 * Ne fait rien
	 * @param length int
	 */
	@Override
	public void setContentLength(int length) {
		// ne fait rien
	}
}
