# AGENTS.md

Leitfaden für KI-Agenten (Claude Code, Codex u. a.), die in diesem Repository arbeiten.

## Projektüberblick

**HermineBotCom** ist eine kleine PHP-Bibliothek mit Datenobjekten (DTOs) für die
Kommunikation mit bzw. zwischen Komponenten eines Hermine-Bots (Messenger
„Hermine“). Die Klassen sind reine, unveränderliche Wertobjekte: Sie werden
über den Konstruktor befüllt und bieten nur Getter – keine Logik, keine I/O.

Im Repository selbst gibt es keinen Einstiegspunkt. Die Klassen werden von zwei
Projekten als Git-Submodule (`submodule/HermineBotCom`) eingebunden:

- **StAN-Manager** (Producer) – erzeugt `Message`/`Placeholder` und schickt sie per
  Symfony Messenger (`async`-Transport) ab.
- **HermineBot** (Consumer) – verarbeitet sie in `HermineMessageHandler` bzw.
  `HerminePlaceholderHandler`.

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
5. In StAN-Manager das Messenger-Routing (`config/packages/messenger.yaml`) und in
   HermineBot einen passenden `#[AsMessageHandler]` ergänzen; Submodule in beiden
   Projekten aktualisieren und in beiden Plesk-Repos pullen (siehe „Deployment“).

Hinweis: Die Typen `AnnounceCommands`, `Content` und `RequestContent` wurden in
Commit `3eab91f` bewusst wieder entfernt – nicht ohne Rückfrage wiederherstellen.

## Git

- Hauptbranch: `main`. Commit-Messages bisher kurz, englisch, Präfix im Stil
  `ADD …` / `Refactored …`.
- Achtung Zeilenenden: Die Windows-Arbeitskopie enthält teils CRLF, das Repository
  LF. Unter WSL zeigt `git status` solche Dateien als geändert, obwohl
  `git diff --ignore-cr-at-eol` leer ist. Reine Zeilenenden-Änderungen nicht
  committen; geänderte Dateien vor dem Commit auf LF bringen.
- Nur committen/pushen, wenn der Nutzer es ausdrücklich verlangt.

## Deployment

- **Lokal** (Entwicklung von HermineBot / StAN-Manager) zählt der im jeweiligen
  Projekt gepinnte Submodule-Commit (`git submodule update --remote` + Commit dort).
- **Produktiv** zählt das nicht: Der Server (Plesk) hat für HermineBotCom zwei eigene
  Git-Repos, die `main` in den Submodule-Ordner der jeweiligen Domain bereitstellen –
  eines für StAN-Manager, eines für HermineBot. Live ist, was dort zuletzt per
  Plesk-Pull bereitgestellt wurde.
- Es gibt keinen GitHub-Webhook. Ein Push auf `main` geht **nicht** automatisch live;
  bereitgestellt wird erst, wenn in Plesk „Jetzt Pull ausführen“ geklickt wird.
- Bei einer Änderung immer **beide** Plesk-Repos pullen, sonst laufen Producer und
  Consumer mit unterschiedlichen Klassen. Lang laufende Prozesse (StAN-Manager-Daemon,
  Messenger-Worker von HermineBot) übernehmen neuen Code erst nach einem Neustart.
- Server-Aktionen (Pull, Neustart) nur durch den Nutzer oder mit seiner ausdrücklichen
  Freigabe.
