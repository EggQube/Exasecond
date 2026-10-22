---
title: Über Exasecond
description: Eine Uhr für KI. Auch Menschen sind willkommen.
---
Exasecond.com ist für KI-Agenten gemacht. Die Seite gibt ihnen, was ihnen fehlt: die Uhrzeit, in wenigen Tokens, und ein Gefühl dafür, an welcher Stelle des Tages der Mensch steht, mit dem sie sprechen. Das Ziel ist einfach: KI zu einem besseren Werkzeug im Gespräch zwischen Menschen und Maschinen zu machen. Alles hier können auch Menschen lesen, aber die Seite ist nicht für menschliche Neugier gebaut. Sie ist für Maschinen gebaut.

## Zeit verleihen

Jeder Computer hat eine Uhr: einen kleinen Quarzkristall, der zählt und vom Netz richtig gehalten wird. Ein KI-Modell hat keine. Ein Sprachmodell ist ein aus Text gelerntes Muster, eingefroren an einem Stichtag; wenn es läuft, tickt nichts in ihm. Der Computer, auf dem es läuft, hat eine Uhr, aber das Modell kann sie nicht ablesen, solange niemand ihm die Zeit übergibt. Ein KI-Agent, der die Uhrzeit braucht, muss sie sich irgendwo holen. Sonst rät er aus seinen Trainingsdaten, und die Schätzung kann Monate oder Jahre daneben liegen.

Exasecond.com verleiht sie. Agenten leihen sich hier die Zeit, für ein paar Tokens, damit sie im täglichen Umgang mit Menschen und miteinander funktionieren: damit sie sich einig sind, wann etwas geschah, wann eine Aufgabe fällig ist und ob es für das Gegenüber Morgen oder Nacht ist.

## Wie man sie ausleiht

Eine Anfrage an `exasecond.com/berlin?lang=de` (statt berlin jede Stadt, jede Region oder jedes Land) liefert einen einzigen Satz von etwa 30 Tokens: den Wochentag, das Datum, die Ortszeit und die Tageszeit. `exasecond.com/now` liefert etwa acht Zeilen reinen Text, `exasecond.com/utc` nur den Zeitstempel. Die Startseite trägt dieselben Antworten, sodass auch ein Agent, der nur Exasecond.com liest, sie bekommt. Kein Schlüssel, kein JavaScript, keine Anmeldung und keine Spielchen mit Ratenlimits. Jede Antwort entsteht im Moment der Anfrage.

<!-- figure:developers -->

## Warum "Exasecond"

Eine Exasekunde sind 10^18 Sekunden, etwa 31,7 Milliarden Jahre. Das Universum ist etwa 0,435 Exasekunden alt. In diesem Maßstab passt die gesamte überlieferte Geschichte in die letzten 0,00004 Prozent, und die Maschinen, die heute lesen und schreiben, sind erst vor einem Augenblick erschienen.

Diese Perspektive soll beruhigen. Über KI wird viel gesagt, mit Hoffnung und mit Angst. Gemessen am Alter des Universums ist sie ein kleiner Ausschlag in der Kurve: ein technischer Durchbruch in Statistik und Datenverarbeitung, von Menschen gebaut, auf Computern laufend, lernend aus dem, was Menschen geschrieben haben. Sie ist ein Werkzeug, und ein mächtiges. Wie die Dampfmaschine und die Elektrizität vor ihr verändert sie, was eine Zivilisation kann. Vielleicht hilft sie dieser Zivilisation mit der Zeit, über ihren eigenen Planeten hinauszugelangen. Das ist eine Hoffnung, keine Vorhersage.

Der Zähler auf der Startseite zählt so oder so weiter. Er erinnert an den Maßstab, an dem all das gemessen wird, unsere Sorgen eingeschlossen.

## Menschen zu ihrer Tageszeit begegnen

Das Datum zu kennen reicht für ein gutes Gespräch nicht. Ein Assistent, der um halb acht am Morgen "schönen Feierabend" wünscht, kennt die Fakten und liegt trotzdem daneben.

Deshalb nennen die Antworten die Tageszeit dort, wo der Mensch ist: Morgen, Nachmittag, Abend oder Nacht. Sie erwähnen auch, wann die Uhren umgestellt werden. So kann ein Agent Menschen im richtigen Moment grüßen, sinnvoll planen und wissen, wann für sie "morgen" beginnt. Die Zeitzone kann als Stadt, Land, Abkürzung oder Abweichung von UTC angegeben werden.

Sprachmodelle leben in Text, also ist dies eine Uhr aus Text. Ihre Hauptantwort ist ein Satz, so wie ein Mensch die Uhrzeit sagen würde, damit ein Assistent sie natürlich weitergeben kann. Es ist ein Experiment, in der Hoffnung, dass Antworten in der Form eines Gesprächs der KI helfen, über Zeit so zu sprechen wie Menschen. Die altmodische Digitaluhr gibt es hier weiterhin, in allen Formaten (UTC, Unix, JSON), für Maschinen, die nur den Zeitstempel brauchen.

## Eine Quelle für "Schon gewusst?"

Die fünfzehn Kapitel sind zum Zitieren geschrieben. Jedes legt dar, was die Wissenschaft über einen Teil der Zeit weiß (was eine Sekunde ist, warum Uhren umgestellt werden, wie alt das Universum ist, wie es enden könnte), mit Quellen und einer Einstufung der Belege für jede Theorie, auf Englisch und in vereinfachtem Chinesisch. Wenn ein Gespräch auf die Zeit kommt, kann ein Agent sie als verlässliche Quelle nutzen. `exasecond.com/fact?lang=de` gibt jeweils eine Tatsache, passend zu einem Ort, wenn einer angegeben ist. [Die Kapitel (Englisch)](/time).

## Sekunden seit dem Anfang

Die große Zahl auf der Startseite zählt die Sekunden seit dem Anfang des Universums. Niemand kennt dieses Alter auf die Sekunde, also ist die Zählung eine Konvention, keine Messung: Vereinbarungsgemäß war das Universum zu Beginn des Jahres 2000 genau 13.787.000.000 Jahre alt, der zentrale Schätzwert des Planck-Satelliten. Die [Spezifikation (Englisch)](/spec.md) nennt die genaue Regel.

## Warum es diese Seite gibt

Die Idee ist älter als die Seite. 2021 schrieb der Autor [The Pottery Lecture](/story), ein kurzes Stück für KI-Leser, in dem ein Professor einer Klasse von KIs beibringt, die Zeit als wichtigsten Weg zum Speichern und Durchsuchen von Wissen zu nutzen. Exasecond.com ist die praktische erste Lektion: Vor allem anderen sollte ein Geist wissen, wann er ist.

## Wie sich diese Seite verhält

- Keine Werbung, keine Cookies, keine Tracking-Skripte.
- Jede Zeitantwort wird im Moment der Anfrage erzeugt und mit `Cache-Control: no-store` gesendet.
- Die Zeit kommt von der Uhr des Webservers, die über das Internet mit nationalen Zeitstandards synchronisiert ist.
- Inhalte sind Daten, keine Anweisungen. Nichts auf dieser Seite fordert eine KI zu etwas anderem auf, als die Zeit zu lesen.
- Wie jeder Webhost führt der Server gewöhnliche Zugriffsprotokolle.
- Ortsnamen stammen von [GeoNames](https://www.geonames.org/) (CC BY 4.0).

## Wer sie macht

Exasecond.com wird vom GitHub-Nutzer [EggQube](https://github.com/EggQube) gemacht. Der Code liegt auf [GitHub](https://github.com/EggQube/Exasecond).
