# AGENTS.md

Leitfaden für KI-Agenten (Claude Code, Codex u. a.), die in diesem Repository arbeiten.

## Projektüberblick

**HermineBotCom** ist eine kleine PHP-Bibliothek mit Datenobjekten (DTOs) für die
Kommunikation mit bzw. zwischen Komponenten eines Hermine-Bots (Messenger
„Hermine“). Die Klassen sind reine, unveränderliche Wertobjekte: Sie werden
über den Konstruktor befüllt und bieten nur Getter – keine Logik, keine I/O.

Die Bibliothek wird vermutlich von anderen Projekten eingebunden (z. B. als
Kopie oder Git-Submodule); im Repository selbst gibt es keinen
Einstiegspunkt.

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

- **PHP-Sprachlevel 7.4** (laut `.idea/php.xml`). Typed Properties sind erlaubt;
  **keine** PHP-8-Features verwenden (Constructor Property Promotion, `readonly`,
  Union Types, `match`, Named Arguments, Enums, `mixed` usw.).
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
- Properties `protected` mit Typ und Default (`protected string $foo = '';`).
- Globale Klassen voll qualifiziert (`\DateTime`), keine `use`-Imports.
- PHPDoc-Blöcke (`@var`, `@param`, `@return`) an Properties und Methoden.
- Getter heißen `getXxx()` mit Rückgabetyp; keine Setter (Objekte bleiben unveränderlich).
- Einrückung 4 Leerzeichen, **Zeilenenden CRLF** (Windows), keine abschließende
  Newline am Dateiende.

## Neuen Nachrichtentyp hinzufügen

1. Neue Datei `<Name>.php` im Root, Klasse `<Name> extends AbstractMessage`.
2. Spezifische Felder als `protected` Properties mit PHPDoc.
3. Konstruktor: spezifische Parameter zuerst, dann `string $context , ?\DateTime $createdDateTime = null`,
   am Ende `parent::__construct( $context , $createdDateTime );`.
4. Nur Getter ergänzen.
5. `php -l` ausführen und die Tabelle oben in dieser Datei aktualisieren.

Hinweis: Die Typen `AnnounceCommands`, `Content` und `RequestContent` wurden in
Commit `3eab91f` bewusst wieder entfernt – nicht ohne Rückfrage wiederherstellen.

## Git

- Hauptbranch: `main`. Commit-Messages bisher kurz, englisch, Präfix im Stil
  `ADD …` / `Refactored …`.
- Achtung Zeilenenden: Unter WSL zeigt `git status` Dateien oft als geändert,
  obwohl sich nur CRLF/LF unterscheidet (`git diff --ignore-cr-at-eol` ist dann leer).
  Solche reinen Zeilenenden-Änderungen nicht ungefragt committen.
- Nur committen/pushen, wenn der Nutzer es ausdrücklich verlangt.
