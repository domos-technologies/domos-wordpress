<!-- Centered image -->
<p align="center">
</p>

<div align="center">
  	<img src="resources/images/domos-logo.svg" alt="domos Logo" width="200"/>
	<h1><a href="https://domos.de">domos</a> WordPress Integration</h1>
</div>

## Funktionen
- Synchronisation von Objekten
- Shortcodes für die Darstellung einer Objektliste und -suche
- Seiten für die Darstellung von Objekten
- Eingebettete Karte

---

## Installation

1. Lade das Plugin in das Verzeichnis `/wp-content/plugins/` hoch
2. Aktiviere das Plugin "domos Core" im Menü 'Plugins' in WordPress
3. Konfiguriere das Plugin im Menü 'domos', indem du die URL zu deiner domos-Instanz angibst
4. Füge den Shortcode `[domos-list]` in eine Seite ein, um eine Liste der Objekte anzuzeigen
5. Syncronisiere die Objekte im Menü 'domos'
6. Fertig! Die Objekte werden nun regelmäßig synchronisiert

---

## Konfiguration der Synchronisation

Um die Objekte zu synchronisieren, musst du die URL deiner domos-Instanz angeben. Dies kannst du in den Einstellungen des Plugins machen.

| Einstellung                                                 | Wert                                                                                                                     |
|-------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------|
| ![Einstellungen](resources/images/docs/domos-menu-item.png) | Die domos Einstellungen findest du im WordPress-Admin-Bereich unten im Menü 'domos'.                                     |
| ![Einstellungen](resources/images/docs/settings-2.png)      | Du kannst die URL deiner domos-Instanz hier angeben.                                                                     |
| ![Einstellungen](resources/images/docs/settings-3.png)      | Nach dem Speichern wird die Verbindung zu deiner domos-Instanz hergestellt und gespeichert.                              |
| ![Einstellungen](resources/images/docs/settings-4.png)      | Unter dem Menüpunkt "Sync" kannst du die Objekte nun synchronisieren. Dies erfolgt automatisch jeden Tag um Mitternacht. |
| ![Einstellungen](resources/images/docs/settings-5.png)      | Wenn alles geklappt hat, siehst du, was synchronisiert wurde.                                                            |

---

## Einbettung der Objektsuche/-karte

Die einfache Objektsuche/-karte kann über den 
Shortcode `[domos-list]` in eine beliebige Seite eingebettet werden.

---

## Überschreiben des Theme-Layouts für Exposés
WordPress Post Name: domos_estate
Wenn du ein Theme verwendest, dass ein eigenes Layout für Objekte oder die Standard WordPress-Post-Liste verwendest, kannst du in deinem Theme die Datei `single-domos_estate.php` oder `archive-domos_estate.php` überschreiben.

- `single-domos_estate.php` wird für einzelne Exposés verwendet.
- `archive-domos_estate.php` wird für die Standard WordPress-Post-Liste verwendet.

---

## Weitere Konfigurationen via WordPress-Filter

### 1. `domos_primary_shades`

Modifiziert die primären Farben, die im Plugin verwendet werden. Verwendet Farb-Paletten im [Tailwind-Format](https://tailwindcss.com/docs/customizing-colors#color-palette-reference).

**Standardwert:** Ein Array von Farbcodes für die Schattierungen 50 bis 950.

**Anwendung:**
```php
add_filter('domos_primary_shades', fn () => [
    '50' => '#f4f7fb',
    '100' => '#e9eff7',
    '200' => '#c7d7ec',
    '300' => '#a5bee1',
    '400' => '#628eca',
    '500' => '#1e5db3',
    '600' => '#1b54a1',
    '700' => '#174686',
    '800' => '#12386b',
    '900' => '#0f2e58',
    '950' => '#091c36',
]);
```

### 2. `domos_gray_shades`

Modifiziert die grauen Farben, die im Plugin verwendet werden. Verwendet Farb-Paletten im [Tailwind-Format](https://tailwindcss.com/docs/customizing-colors#color-palette-reference).

**Standardwert:** Ein Array von Farbcodes für die Schattierungen 50 bis 950.

**Anwendung:**
```php
add_filter('domos_gray_shades', fn () => [
    '50' => '#fafafa',
    '100' => '#f5f5f5',
    '200' => '#e5e5e5',
    '300' => '#d4d4d4',
    '400' => '#a3a3a3',
    '500' => '#737373',
    '600' => '#525252',
    '700' => '#404040',
    '800' => '#262626',
    '900' => '#171717',
    '950' => '#0a0a0a',
]);
```

### 3. `domos_is_using_dark_mode`

Bestimmt, ob das Plugin den Dunkelmodus verwenden soll.

**Standardwert:** `false`

**Anwendung:**
```php
add_filter('domos_is_using_dark_mode', fn () => true);
```

### 4. `domos_privacy_policy_url`

Legt die URL für die Datenschutzrichtlinie fest.

**Standardwert:** `'/datenschutz'`

**Anwendung:**
```php
add_filter('domos_privacy_policy_url', fn () => '/neue-datenschutzrichtlinie');
```

### 5. `domos_default_navbar_height`

Die Kopfbereiche der Exposés können "Full-Height"-Varianten verwenden, welche den Kopfbereich auf die gesamte Höhe des Bildschirms bringen.
Legt die Höhe für die Navigationsleiste fest, damit diese von der "Full-Height" abgezogen werden kann. (HTML-Einheiten wie `px`, `em` oder `rem` möglich).

**Standardwert:** `80px`

**Anwendung:**
```php
add_filter('domos_default_navbar_height', fn () => '100rem');
```

### 6. `domos_lottie_colors`

Modifiziert die Farben, die in den Merkmal-Icons verwendet werden.

![Animiertes Icon](resources/images/docs/animated-icon.png)

**Standardwert:** Ein Array mit 'color1' (primäre Schattierung 900) und 'color2' (primäre Schattierung 500).

**Anwendung:**
```php
add_filter('domos_lottie_colors', fn () => [
    'color1' => '#0f2e58',  // Angepasstes Dunkelblau
    'color2' => '#1e5db3',  // Angepasstes Hellblau
]);
```

### 7. `domos_lightbox_enabled`

Bestimmt, ob die Lightbox-Funktion aktiviert ist.

**Standardwert:** `false`

**Anwendung:**
```php
add_filter('domos_lightbox_enabled', fn () => true);
```

## Hinweise

- Alle Filter sollten vor der Initialisierung des DOMOS Plugins hinzugefügt werden, z.B. in `functions.php` oder einem eigenen Plugin.
- Bei der Modifizierung von Farbarrays stellen Sie bitte sicher, dass Sie die gleiche Struktur (50 bis 950) beibehalten, um Fehler zu vermeiden. Für inspiration kannst du dir die [Tailwind-Palette](https://tailwindcss.com/docs/customizing-colors#color-palette-reference) anschauen.
- Dieses Plugin benötigt eine WordPress-Installation mit mindestens PHP 8.0. Ältere PHP-Versionen werden nicht mehr unterstützt.

## Vollständiges Beispiel

Hier ist ein vollständiges Beispiel, das zeigt, wie alle Filter zusammen verwendet werden können:

```php
<?php
// DOMOS Plugin Filter anpassen

// Primäre Schattierungen modifizieren
add_filter('domos_primary_shades', fn () => [
    '50' => '#f4f7fb',
    '100' => '#e9eff7',
    '200' => '#c7d7ec',
    '300' => '#a5bee1',
    '400' => '#628eca',
    '500' => '#1e5db3',
    '600' => '#1b54a1',
    '700' => '#174686',
    '800' => '#12386b',
    '900' => '#0f2e58',
    '950' => '#091c36',
]);

// Graue Schattierungen modifizieren
add_filter('domos_gray_shades', fn () => [
    '50' => '#fafafa',
    '100' => '#f5f5f5',
    '200' => '#e5e5e5',
    '300' => '#d4d4d4',
    '400' => '#a3a3a3',
    '500' => '#737373',
    '600' => '#525252',
    '700' => '#404040',
    '800' => '#262626',
    '900' => '#171717',
    '950' => '#0a0a0a',
]);

// Dunkelmodus aktivieren
add_filter('domos_is_using_dark_mode', fn () => true);

// Benutzerdefinierte Datenschutzrichtlinien-URL festlegen
add_filter('domos_privacy_policy_url', fn () => '/benutzerdefinierte-datenschutzrichtlinie');

// Benutzerdefinierte Navbar-Höhe festlegen
add_filter('domos_default_navbar_height', fn () => '90px');

// Lottie-Farben anpassen
add_filter('domos_lottie_colors', fn () => [
    'color1' => '#0f2e58',
    'color2' => '#1e5db3',
]);

// Lightbox-Funktion aktivieren
add_filter('domos_lightbox_enabled', fn () => true);
```

Dieses Beispiel zeigt, wie alle verfügbaren Filter verwendet werden können, um das Verhalten und Aussehen des DOMOS Plugins anzupassen.
