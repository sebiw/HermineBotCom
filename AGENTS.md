# AGENTS.md

Leitfaden für KI-Agenten (Claude Code, Codex u. a.), die in diesem Repository arbeiten.

## Projektüberblick

**HermineBotCom** ist eine kleine PHP-Bibliothek mit Datenobjekten (DTOs) für die
Kommunikation mit bzw. zwischen Komponenten eines Hermine-Bots (Messenger
„Hermine“). Die Klassen sind reine, unveränderliche Wertobjekte: Sie werden
über den Konstruktor befüllt und bieten nur Getter – keine Logik, keine I/O.

Im Repository selbst gibt es keinen Einstiegspunkt. Verarbeitet werden die
Nachrichten im HermineBot von `HermineMessageHandler` bzw. `HerminePlaceholderHandler`.

## Projektverbund

Dieses Projekt ist Teil eines Verbunds aus drei Repositories, die zusammen betrieben werden. Änderungen an einer Schnittstelle wirken sich auf die anderen aus.

| Projekt | Rolle |
|---|---|
| [StAN-Manager](https://github.com/sebiw/StAN-Manager) (privat) | Material- und Vorgangsverwaltung. **Producer**: stellt Statusänderungen als `HermineBotCom\Message` in die Queue. **API**: liefert Reports unter `/api/open-messages` und `/api/open-files` (JSON-POST mit API-Benutzer und Key). |
| [HermineBot](https://github.com/sebiw/HermineBot) | Bot für den Messenger Hermine (Stashcat). **Consumer**: verarbeitet die Queue (`messenger:consume`) und sendet an Hermine; ruft die Reports des StAN-Managers per REST als Platzhalter ab. |
| [HermineBotCom](https://github.com/sebiw/HermineBotCom) | Gemeinsame Nachrichtenklassen (`Message`, `Placeholder`, `AbstractMessage`), in beiden Anwendungen als Submodule `submodule/HermineBotCom` eingebunden. |

**Schnittstellen**

- **Queue**: Symfony Messenger, Doctrine-Transport auf eine gemeinsame Datenbank `messages` (`auto_setup=0`). Nachrichten werden mit PHP `serialize()` übertragen – Klassen- und Property-Namen von HermineBotCom bestimmen das Format.
- **REST**: Der Bot ruft die Report-Endpunkte des StAN-Managers auf; die Antwort (`content`) wird als Platzhaltertext in Bot-Nachrichten eingesetzt.
- **Submodule**: Lokal gilt der im jeweiligen Projekt gepinnte Commit. Produktiv werden die drei Repos getrennt bereitgestellt; dort zählt, was zuletzt von `main` bereitgestellt wurde – Submodule werden nicht automatisch mitgezogen.

**Wer hier etwas ändert, prüft dort**

| Änderung an … | Auswirkung / prüfen |
|---|---|
| HermineBotCom-Klassen | Abwärtskompatibel halten (alte Nachrichten in der Queue). Submodule in StAN-Manager **und** HermineBot aktualisieren; Messenger-Routing (StAN-Manager) und Handler (HermineBot) anpassen; beide produktiv bereitstellen. |
| Neuer Nachrichtentyp | Klasse in HermineBotCom, Routing im StAN-Manager, `#[AsMessageHandler]` im HermineBot. |
| `/api/*` im StAN-Manager | Platzhalter-Konfiguration des Bots (REST-Endpunkte) und erwartetes Antwortformat (`content`) prüfen; Breaking Change = MAJOR. |
| Workflow/Status im StAN-Manager | Texte, die über die Queue an Hermine gehen, ggf. anpassen. |
| Queue-Konfiguration (Transport, DB `messages`) | In beiden Anwendungen identisch halten. |
| PHP-/Symfony-Version | Nur gemeinsam für alle drei Projekte anheben (HermineBotCom: keine Features über die gemeinsame PHP-Version hinaus). |

**Interne Informationen**

Hosting-Anbieter, Hostnamen, Produktiv-URLs, Server-Pfade, Benutzer- und Kanalnamen, Zugangsdaten und Momentaufnahmen des Live-Stands gehören **nicht** in dieses Repo (auch nicht in Commits, Issues oder PRs). Sie werden außerhalb von Git in einer privaten Betriebsdoku gepflegt, die lokal über `CLAUDE.local.md` geladen wird. Ein lokaler Pre-commit-Hook prüft Commits auf solche Angaben.

**Commit, Tag und Push**

Agenten committen, taggen und pushen in keinem der drei Repos eigenständig – auch nicht bei Changelog-Einträgen oder Releases. Änderungen fertig vorbereiten, Aufteilung und Commit-Message vorschlagen und auf ausdrückliche Freigabe des Nutzers warten. Eine Freigabe gilt nur für den besprochenen Umfang; Push braucht eine eigene Freigabe.

## Kompatibilität (wichtig)

Die Objekte werden mit dem Standard-Serializer von Symfony Messenger (PHP
`serialize()`) über die Queue übertragen. Producer und Consumer können
vorübergehend unterschiedliche Stände dieses Repos haben, und in der Queue
können noch Nachrichten älterer Versionen liegen. Deshalb gilt:

- Klassennamen, Namespace, Property-Namen und deren Sichtbarkeit (`protected`)
  **nicht ändern** – sie bestimmen das serialisierte Format.
- Öffentliche API (Konstruktor-Signaturen inkl. Parameterreihenfolge, Getter mit
  Rückgabetypen) stabil halten. Neue Konstruktorparameter nur optional und am Ende.
- Breaking Changes nur in Absprache und mit gleichzeitiger Bereitstellung in
  beiden Projekten (siehe „Deployment“).
- Bei Änderungen prüfen: alt serialisiertes Objekt mit neuem Code deserialisieren
  und umgekehrt.

## Struktur

Alle Klassen liegen flach im Repository-Root, Namespace `HermineBotCom`:

| Datei                 | Klasse                     | Felder (zusätzlich zur Basis)       | Zweck                                         |
|-----------------------|----------------------------|-------------------------------------|-----------------------------------------------|
| `AbstractMessage.php` | `abstract AbstractMessage` | `context`, `createdDateTime`        | Gemeinsame Basis aller Nachrichtentypen       |
| `Message.php`         | `Message`                  | `message`, `channel`                | Nachricht an/aus einem Hermine-Channel        |
| `Placeholder.php`     | `Placeholder`              | `message`, `key`                    | Aktives Senden von Platzhalter-Informationen  |

Basis-Konstruktor: `__construct(string $context, ?\DateTime $createdDateTime = null)` –
ohne Zeitstempel wird `new \DateTime()` verwendet.

Unterklassen nehmen ihre eigenen Felder zuerst und rufen am Ende
`parent::__construct($context, $createdDateTime)` auf, z. B.:

```php
new Message(string $message, string $channel, string $context, ?\DateTime $createdDateTime = null);
new Placeholder(string $message, string $key, string $context, ?\DateTime $createdDateTime = null);
```

## Tooling & Umgebung

- **PHP 8.1** – Mindestversion von HermineBot und StAN-Manager (`"php": ">=8.1"`).
  Features ab 8.2 (z. B. `readonly`-Klassen, DNF-Types, Konstanten in Traits) sind
  **nicht** erlaubt. Ein Versionssprung erfolgt nur gemeinsam für alle drei Projekte.
- Kein Composer, kein Autoloader, keine Tests, kein Linter/CI. Nichts davon
  ungefragt einführen.
- Syntaxprüfung bei Bedarf: `php -l <Datei>.php`.
- IDE: PhpStorm (`.idea/` ist per `.gitignore` ausgeschlossen).

## Code-Konventionen

Bestehenden Stil exakt beibehalten:

- `<?php`, Leerzeile, `namespace HermineBotCom;`, Leerzeile, Klasse.
- Öffnende Klammer der Klasse auf derselben Zeile (`class Foo extends AbstractMessage {`),
  bei Methoden auf der nächsten Zeile.
- Leerzeichen innerhalb der Parameterklammern und vor Kommas:
  `__construct( string $message , string $key , ... )`.
- Properties per Constructor Property Promotion als `protected readonly` mit Typ
  (`protected readonly string $foo`); nur wenn ein Wert im Konstruktor berechnet wird,
  klassisch deklarieren (siehe `$createdDateTime`).
- Globale Klassen voll qualifiziert (`\DateTime`), keine `use`-Imports.
- PHPDoc-Blöcke (`@param`, `@return`) an Konstruktor und Methoden, `@var` an
  klassisch deklarierten Properties.
- Getter heißen `getXxx()` mit Rückgabetyp; keine Setter (Objekte bleiben unveränderlich).
- Veränderbare Objekte (`\DateTime`) beim Speichern und im Getter `clone`n, damit
  der interne Zustand nicht von außen verändert werden kann.
- Einrückung 4 Leerzeichen, Zeilenenden im Repository **LF**, keine abschließende
  Newline am Dateiende.

## Neuen Nachrichtentyp hinzufügen

1. Neue Datei `<Name>.php` im Root, Klasse `<Name> extends AbstractMessage`.
2. Konstruktor: spezifische Felder zuerst als `protected readonly` (Promotion), dann
   `string $context , ?\DateTime $createdDateTime = null`, im Rumpf
   `parent::__construct( $context , $createdDateTime );`.
3. Nur Getter ergänzen.
4. `php -l` ausführen und die Tabelle oben in dieser Datei aktualisieren.
5. Routing, Handler, Submodule und Bereitstellung in StAN-Manager und HermineBot
   nachziehen (siehe „Projektverbund“ und „Deployment“).

Hinweis: Die Typen `AnnounceCommands`, `Content` und `RequestContent` wurden in
Commit `3eab91f` bewusst wieder entfernt – nicht ohne Rückfrage wiederherstellen.

## Git

- Hauptbranch: `main`. Commit-Messages bisher kurz, englisch, Präfix im Stil
  `ADD …` / `Refactored …`.
- Zeilenenden: Repository LF, Arbeitsverzeichnis CRLF. In WSL muss
  `core.autocrlf=true` gesetzt sein, sonst erscheinen alle Dateien als geändert.
  Reine Zeilenenden-Änderungen nicht committen.
- Nur committen, taggen oder pushen, wenn der Nutzer es ausdrücklich freigibt (siehe „Commit, Tag und Push“).

## Deployment

- Lokal und produktiv gelten unterschiedliche Stände (siehe „Schnittstellen“ unter
  „Projektverbund“). Ein Push auf `main` geht **nicht** automatisch live.
- Produktiv wird HermineBotCom für StAN-Manager und HermineBot getrennt bereitgestellt.
  Bei einer Änderung immer **beide** Kopien bereitstellen, sonst laufen Producer und
  Consumer mit unterschiedlichen Klassen.
- Bereitstellung und sonstige Server-Aktionen nur durch den Nutzer oder mit seiner
  ausdrücklichen Freigabe.
