# Changelog

## 3.3.0
- added: multi-language support. Configure other languages in the plugin settings.
- fix: remove old domos logo

## 3.2.1
- Updated version of domos/schema to 1.2.2
- Make EstatePost::find fail gracefully to avoid sync-lock

## 3.2.0
- Updated version of domos/schema to 1.2

## 3.1.0
- Updated version of domos/schema to 1.0

## 3.0.0
- Removed image synchronization
- Improved estate synchronization speed
- Added API key support (which will become mandatory in future versions)

## 2.1.0
- Added support for dark mode brandings
- Added custom font support
- Privacy policy URL is now configurable
- Recommended Estates now correctly redirect to the WordPress pages, instead of domos-hosted pages

## 2.0.3
- Fixes script conflict with Elementor
- Loading admin scripts (Alpine) from plugin files instead of CDN

## 2.0.2
- Fixed Admin UI not saving properly in some WP configurations due to nonce issues

## 2.0.1
- Fixed sliders not rendering properly

## 2.0.0
- Changed block rendering from included block templates, to domos-server driven HTML rendering.
- Improved estate synchronization
