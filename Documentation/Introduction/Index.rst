.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


Einführung
==========

Mit Hilfe der evangtermine-Extension lassen sich die Veranstaltungshinweise aus dem Webangebot
evangelische-termine.de in TYPO3-Websites integrieren. Im Unterschied zu Vorgängerversionen
nutzt die Version ab 2.0.0 dazu die XML-Ausgabe von evangelische-termine.de als Datenquelle
und Fluid-Templates für die Anzeige. Das heißt: Die nötigen (Fluid-) Kenntnisse vorausgesetzt,
sind sehr flexible Layoutanpassungen möglich.

Ab Version 3.0.0 kann gewählt werden, ob die Veranstaltungshinweise direkt aus der XML-Ausgabe geholt und angezeigt werden
oder per CLI-Skript in der TYPO3-Datenbank gespeichert werden. Die Anzeige im Frontend erfolgt dann mit den gespeicherten Daten.

Wer ist wer
-----------

Das Termin-Verwaltungssystem http://www.evangelische-termine.de ist ein Produkt der *Vernetzten Kirche im
Evangelischen Presseverband für Bayern e.V.* Web: http://www.vernetzte-kirche.de, E-Mail: vernetztekirche@elkb.de.

evangelische-termine.de wird zur Zeit angewendet von:

- `Evangelisch-Lutherische Kirche in Bayern <https://www.bayern-evangelisch.de>`_
- `Evangelische Kirche Berlin-Brandenburg-schlesische Oberlausitz <https://www.ekbo.de>`_
- `Evangelische Kirche in Hessen und Nassau <https://www.ekhn.de>`_
- `Evangelisch-Lutherische Kirche in Oldenburg <https://www.kirche-oldenburg.de>`_
- `Evangelische Kirche im Rheinland <https://www.ekir.de>`_
- `Evangelisch-Lutherische Kirche Sachsens <https://www.evlks.de>`_
- `Evangelische Kirche von Westfalen <https://www.evangelisch-in-westfalen.de>`_

Die TYPO3-Extension **evangtermine** wurde entwickelt und betreut von der Evangelischen Kirche von Westfalen.
Ab Version 3.0.0 wird sie von Sudhaus7 entwickelt und betreut.

Der aktuelle Code dieser Extension wird auf GitHub verwaltet: https://github.com/sudhaus7/evangtermine
Hinweise auf Bugs, Fehler in dieser Dokumentation, Verbesserungsvorschläge können gerne dort eingegeben werden
oder auch per Mail an den Autor: dsimon@sudhaus7.de.
