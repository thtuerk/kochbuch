# Kochbuch

Dieses Projekt implementiert ein sehr einfaches Content Management
System, das für Kochbücher gedacht ist. Es benutzt PHP, Git und Pandoc
sowie durch Pandoc Latex.

## Motivation

Es gibt tolle Open Source Rezeptverwaltungen. Diese haben jedoch viele
Features, die ich und meine Familie nicht benötigen. Umgekehrt fehlten
uns bei diesen Rezeptverwaltungen einige Features. Unsere Anforderung
war, relativ wenige Rezepte (etwa 200) zu verwalten. Auf die Rezepte
sollen nur engste Familienmitglieder (die aber nicht alle im selben
Haus leben) Zugriff haben. Rezepte sollen einfach bearbeitet und in
verschiedenen Formaten ausgeben zu können. Sowohl für die Weitergabe
an Bekannte als auch für uns selbst sollten Rezept _schön_ ausgedruckt
werden können. Wir wollten Rezepte sehr einfach auf unserem sehr alten
eBook-Reader lesen und auch einfach per eMail verschicken
können. Features wie eine Suche nach Zutaten, eine aufwändige
Benutzerverwaltung oder das Anpassen der Mengenangaben in einem Rezept
benötigen wir nicht. Die Gestaltung der Rezepte sollte persönlichem
Geschmack folgen können.

Als Familie verwalten wir seit etlichen Jahren (> 15 Jahre) unsere
Rezepte über eine Webapplikation. Wir waren recht zufrieden. Ansätze
wie RecipeML erwiesen sich im Laufe der Zeit jedoch als etwas
umständlich. Die Struktur von Rezepte musste teilweise angepasst werden,
um sich einfach in RecipeML ausdrücken zu lassen. Des weiteren war über
die Jahre die Konvertierung von Rezepten immer mal wieder ein Thema.
Es war teilweise aufwändig, unsere Rezepte von einer Verwaltung in
eine andere zu portieren und wir hatten teilweise sogar das Problem,
ob wir noch Tools finden konnten, die elektronische Form unserer
Rezepte lesen konnten.

Als die von uns verwendete, schon recht alte, PHP Webapplikation in
die Jahre kam und sich nur noch schwer dazu bewegen ließ mit aktuellen
PHP und Datenbankversionen zusammen zu arbeiten, beschloss ich, nachdem
ich im Netz nach Alternativen gesucht hatte, selbst eine sehr einfache
Rezeptverwaltung zu schreiben. Nachdem wir diese nun schon fast 3
Jahre eingesetzt haben, komme ich endlich dazu, den Code hier zu
veröffentlichen.

## Unsere Kochbuch Applikation

Die Kochbuch Applikation ist sehr einfach, fast primitiv
gehalten. Rezepte werden in Markdown geschrieben. Sie können
Kategorien zugeordnet werden. Des weiteren können weitere, nicht
bearbeitbare Dateien zu jedem Rezept gespeichert werden. Dies wird vor
allem für Bilder genutzt.  GIT wird verwendet um alle Dateien zu
verwalten. Mittels Pandoc können Rezepte formatieren werden. Mögliche
Ausgabeformate sind z.B. HTML, PDF, DOCX, EPUB oder TXT ausgegeben
werden. Eine einfache PHP Oberfläche tätigt die nötigen Aufrufe von
Git und Pandoc. Die Bilder im Unterverzeichnis `screenshots` vermitteln
einen ersten Eindruck der Oberfläche.

Jedes Rezept wird in einer Datei namens `rezept-name.md`
gespeichert. Zudem existiert ein Verzeichnis namens
`rezept-name.md.dir` in dem zugehörige Dateien gespeichert werden.  In
diesem Verzeichnis befindet sich eine ASCII-Datei `kategorien`. Diese
enthält Kathegorien, zu der das Rezept gehört. Unterkategorien werden
durch `/` abgetrennt. Es können mehrere Kathegorien und
Unterkategorien angegeben werden. Das Verzeichnis enthält zudem Bilder
und andere Dateien, die zu dem Rezept gehören.

## Installation

Die Kochbuch Applikation benötigt ein GIT repository zum Speichern der
Rezepte. Erstellen sie bitte ein solches Repository und checken Sie es
an einer geeigneten Stelle aus, so dass der Webserver volle
Zugriffsrechte hat. Ebenso wird ein Verzeichnis für temporäre Dateien
benötigt, auf das der Webserver vollen Zugriff hat. Nach dem
Vorbereiten dieser Verzeichnisse, passen Sie bitte `config.php` an und
tragen diese Verzeichnisse in den Variablen `TMP_DIR` sowie `DATA_DIR`
ein. Ebenso passen Sie bitte das Array `AUTHORS` an, um Benutzer sowie
deren Credentials anzulegen. Per default existiert ein user `admin` mit
Passwort `admin`.

Das Kochbuch unterstützt das Versenden von eMails mit HTML Anhang an eine
festgelegte eMail-Adresse. Dies wird benutzt um Rezepte auf dem IOTP Drucker
der Familie auszugeben. Bei Bedarf, kommentieren Sie bitte die Variable
`IOTP-EMAIL` ein und passen Sie sie auf die gewünschte eMail-Adresse an.

Sollte ein Webseite verfügbar sein, die das Betrachten des
GIT-Repositories erlaubt (z.B. eine Git-List Instanz) kann diese
mittels der Funktion `git_revision_link` sowie der Variablen
`GIT_NAME` und `GIT_MASTER_LINK` konfiguriert werden.


## Kontaktinformationen

Thomas Tuerk
http://www.thomas-tuerk.de
eMail: thomas@tuerk-brechen.de