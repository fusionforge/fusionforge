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
 * Implémentation de FilterServletResponseWrapper qui fonctionne avec le CounterResponseStream,
 * pour calculer la taille du flux de réponse.
 * @author Emeric Vernat
 */
class CounterServletResponseWrapper extends FilterServletResponseWrapper {
	/**
	 * Constructeur qui crée un adapteur de HttpServletResponse wrappant la response spécifiée.
	 * @param response HttpServletResponse
	 */
	CounterServletResponseWrapper(HttpServletResponse response) {
		super(response);
		assert response != null;
	}

	/**
	 * Retourne la taille en octets du flux écrit dans la réponse.
	 * @return int
	 */
	public int getDataLength() {
		return getStream() == null ? 0 : ((CounterResponseStream) getStream()).getDataLength();
	}

	/** {@inheritDoc} */
	@Override
	public ServletOutputStream createOutputStream() throws IOException {
		return new CounterResponseStream((HttpServletResponse) getResponse());
	}
}
