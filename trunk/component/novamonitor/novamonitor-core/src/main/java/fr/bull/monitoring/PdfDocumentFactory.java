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

import com.lowagie.text.Document;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Element;
import com.lowagie.text.HeaderFooter;
import com.lowagie.text.PageSize;
import com.lowagie.text.Phrase;
import com.lowagie.text.Rectangle;
import com.lowagie.text.pdf.PdfWriter;

/**
 * Factory pour les documents pdf.
 * @author Emeric Vernat
 */
class PdfDocumentFactory {
	private final String application;
	private final OutputStream output;

	PdfDocumentFactory(String application, OutputStream output) {
		super();
		assert application != null;
		assert output != null;
		this.application = application;
		this.output = output;
	}

	Document createDocument() throws DocumentException, IOException {
		// creation of a document-object
		final Rectangle pageSize = PageSize.A4; // landscape : PageSize.A4.rotate()
		final Document document = new Document(pageSize, 40, 40, 40, 40);
		// we add some meta information to the document
		document.addAuthor(application);
		document.addCreator("NovaMonitor par E. Vernat, www.novaforge.org");

		final String title = "Monitoring sur " + application;
		createWriter(document, title);
		return document;
	}

	// We create a writer that listens to the document and directs a PDF-stream to output
	private void createWriter(Document document, String title) throws DocumentException,
			IOException {
		final PdfWriter writer = PdfWriter.getInstance(document, output);
		//writer.setViewerPreferences(PdfWriter.PageLayoutTwoColumnLeft);

		// title
		final HeaderFooter header = new HeaderFooter(new Phrase(title), false);
		header.setAlignment(Element.ALIGN_LEFT);
		header.setBorder(Rectangle.NO_BORDER);
		document.setHeader(header);
		document.addTitle(title);

		// simple page numbers : x
		//HeaderFooter footer = new HeaderFooter(new Phrase(), true);
		//footer.setAlignment(Element.ALIGN_RIGHT);
		//footer.setBorder(Rectangle.TOP);
		//document.setFooter(footer);

		// add the event handler for advanced page numbers : x/y
		writer.setPageEvent(new PdfAdvancedPageNumberEvents());
	}
}
