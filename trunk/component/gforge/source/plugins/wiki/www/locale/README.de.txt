Die �bersetzungen der englischen Texte des PhpWikis ins Deutsche sind
derzeit und schon l�nger nicht mehr aktuell und auf dem Laufenden. Das
ist vor allem dadurch bedingt, dass die englischen Texte best�ndig
erg�nzt, �berholt und ver�ndert wurden. �brigens sind auch die anderen
fremdsprachigen Texte gegen�ber dem englischen Original nicht mehr
ganz zeitgem��. Die PhpWiki-Entwickler freuen sich immer, wenn von
anderen auch an der sprachlichen Aktualisierung von PhpWiki
weitergefeilt wird.

F�r diejenigen, die daran mitwirken wollen, sind hier ein paar
sachdienliche Vorschl�ge: Bitte laden Sie sich zun�chst die neueste
PhpWiki-Version auf Ihren Rechner von:

Nightly CVS snapshot:
http://phpwiki.sf.net/nightly/phpwiki.nightly.tar.gz

Beachten Sie bei der weiteren Bearbeitung bitte, dass Sie die Datei:
phpwiki/locale/de/LC_MESSAGES/phpwiki.php direkt nicht editieren
sollten, sondern vielmehr folgende: phpwiki/locale/po/de.po. Achten
Sie auch darauf, diese Datei mit ISO-8859-1 abzuspeichern (nicht mit
Macintosh-Charset oder Windows-1252), sonst fehlen die Umlaute und das
nach �lterer Schreibweise noch gebr�uchliche scharfe S bzw. Sz "�".

Zun�chst wurden die Dateien in LC_MESSAGES automatisch erstellt:
phpwiki.mo u. phpwiki.php. Das braucht aber GNUmake: (Seite auf
Englisch) http://www.gnu.org/software/make/make.html

cd phpwiki/locale
make

Wenn Sie Windows benutzen, d.h. nicht Linux noch Unix/Mac OS X, k�nnen
Sie auch GNUmake installieren, am besten via cygwin. (Die
Hauptentwicklung passiert momentan mit cygwin). Eine
Installationbeschreibung hierf�r an dieser Stelle zu liefern w�re zu
ausf�hrlich. �nderungen bitte an die phpwiki-talk mailingliste
schicken oder den Bug Tracker eintragen.

Alle Ihre Korrekturen werden dann von einem der Entwickler, sobald ein
entsprechender Umfang erreicht ist, bei geeigneter Gelegenheit in
ihrer Gesamtheit ins PhpWiki-CVS (auf Sourceforge) eingesetzt und
damit dann f�r alle PhpWiki-Benutzer zug�nglich und wirksam.

-- CarstenKlapp <carstenklapp@users.sourceforge.net>
   Reini Urban