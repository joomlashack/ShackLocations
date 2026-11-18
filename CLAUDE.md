# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Shack Locations (Free) is a Joomla component for map-based location directories. It is a fork of John Pitchers' FocalPoint, so internally everything is still named `focalpoint`: the element is `com_focalpoint`, classes are prefixed `Focalpoint*`, language keys are `COM_FOCALPOINT_*`, and DB tables are `#__focalpoint_*`. "SLOC"/"Sloc" is the newer shorthand (`SLOC_*` constants, `JHtmlSloc*`, `jQuery.sloc`, `onSlocmap*` events). Requires PHP 7.4+. Supports Joomla 3.9 and later; recent work targets Joomla 4–6.

## Build

There is no test suite, linter config, or composer install step in this repo. Packages are built with Phing using the external **ShackBuilder** tool:

- Copy `build.dist.properties` to `build.properties` (it's gitignored) and set `builder.path` plus the paths to the sibling repos ShackInstaller, ShackDefaultFiles, ShackFramework and ShackExtensionSupport.
- Run `phing` from the repo root. The installable zip goes to `packages/` (gitignored).

The build pulls in pieces that aren't in this repo:
- `src/admin/library/Installer/` (ShackInstaller). `src/install.focalpoint.php` requires it and extends `Alledia\Installer\AbstractScript`.
- The Alledia/Shack framework library (`allediaframework`) and the license/support plugin, declared under `<relatedExtensions>` in the manifest.

The manifest's `<alledia>` block (in `src/focalpoint.xml`) controls the build and install: which scripts get minified, related extensions, and `<obsolete>` files and extensions to remove on update.

**Minified JS is committed.** `src/media/js/{sloc,googleMap,infobox}.min.js` are what actually load at runtime, so when you edit the non-minified source you must regenerate and commit the matching `.min.js` too.

## Architecture

Legacy (non-namespaced) Joomla MVC component. The layout follows the install structure: `src/admin` → `administrator/components/com_focalpoint`, `src/site` → `components/com_focalpoint`, `src/media` → `media/com_focalpoint`. `src/assets/markers` is moved to `images/markers` on a fresh install.

**Bootstrap.** Both entry points (`admin/focalpoint.php`, `site/focalpoint.php`) include `admin/include.php`, which:
- loads the Alledia framework (fails gracefully if it's missing);
- defines `SLOC_ADMIN`, `SLOC_SITE` and `SLOC_LIBRARY`;
- registers a camel-case autoloader for `Focalpoint*` classes from `admin/library/joomla`, so `FocalpointViewAdminList` resolves to `view/admin/list.php`;
- adds `admin/library/html` as an HTMLHelper include path;
- imports the `focalpoint` plugin group.

**Base classes** (`admin/library/joomla`): `FocalpointView`, `FocalpointViewSite`, `FocalpointViewAdminList`/`Form`, `FocalpointModel*`, `FocalpointTable` and `FocalpointPlugin`. Most of them extend Alledia framework abstractions such as `Alledia\Framework\Joomla\View\Admin\AbstractList`. Prefer `Alledia\Framework\Factory` over `Joomla\CMS\Factory` where the surrounding code does.

**Data model.** These items are edited in the admin as Maps, Legends, Location Types and Locations:
- A legend groups location types. Each location has a primary `type` and can have additional types through `#__focalpoint_location_type_xref`, which `admin/models/location.php::updateTypes()` maintains.
- Custom fields are defined per location type in the `customfields` JSON column. Their values are stored per location in the `customfieldsdata` JSON column. On the site they render through `site/layouts/custom/field/<datatype>.php`, either directly or by `{fieldname}` tokens in a location's description (`FocalpointViewSite::replaceFieldTokens`).
- Map tabs and styles are stored in the maps `tabsdata` JSON column.
- Several of these JSON formats changed over time. `install.focalpoint.php::customPostFlight()` migrates old data on update, so add any new data migration there.

**Map rendering (site).** `site/models/map.php::getMarkerData()` joins locations → types → legends. Then `FocalpointViewMap`:
- fires `onSlocmapPrepareRender`, `onSlocmapBeforeRender` and `onSlocmapAfterRender`;
- runs content plugins on the map and its tabs;
- renders `default.php`, which loads the sub-templates `default_<mapEngine>*.php` (`mapEngine` is `google`) and `default_legend_<position>.php`.

The templates call `HTMLHelper::_('slocgoogle.map', $id, $params, $center, $markers)` (`admin/library/html/slocgoogle.php`). That call:
- builds a JSON options object, including the infobox HTML rendered from `site/layouts/marker/infobox/*`;
- loads the Google Maps API once, with `callback=initGoogle`;
- loads `sloc.min.js` and `googleMap.min.js`;
- adds inline `initGoogle()`/`initMap()` functions. `initMap()` polls until `google.maps` and `InfoBox` are ready, then creates a `jQuery.sloc.map.google` instance and registers it with `jQuery.sloc.map.register(id, map)`.

All client-side map behavior lives in `media/js/googleMap.js`: markers, clustering, legend toggles, search radius, directions, KML overlay, and the list tab. `media/js/sloc.js` provides `jQuery.sloc` helpers (`loadScript`, `sprintf`, `tabs`, and the map registry). JS language strings must be registered with `Text::script()` in `slocgoogle.php`.

**Extension points.** Pro and add-on features (clusters, fullscreen, map styles, overlays, and so on) were split out of this free version. They hook in through `focalpoint` group plugins extending `FocalpointPlugin`, using these events:
- `onSlocmapBeforeLoad`
- `onSlocmapPrepareRender`, `onSlocmapBeforeRender`, `onSlocmapAfterRender`
- `onSlocmapBeforeSave`, `onSlocmapAfterSave`
- `onSlocmapTabs`

Keep these event signatures stable.

**Joomla 3 compatibility.** Admin views ship parallel templates: `tmpl/edit.php` alongside `tmpl/edit.j3.php`, and `default.php` alongside `default.j3.php`. When you change an admin layout, check whether the `.j3.php` variant needs the same change. Version-specific code branches on `Version::MAJOR_VERSION`.

**DB schema changes** go in two places: `admin/sql/install/mysql/install.sql`, and a new `admin/sql/updates/mysql/<version>.sql` file.

**Languages:** en-GB, fr-FR and nl-NL. The site view loads the admin language file as well, so most strings live in `admin/language/*/*.com_focalpoint.ini`.

## Conventions

- Every PHP file starts with the GPL docblock header (both John Pitchers' and Joomlashack's copyright lines) and `defined('_JEXEC') or die();`. Non-namespaced class files wrap these in the `phpcs:disable`/`enable` comments for PSR1 side effects and missing namespaces. Copy that pattern from an existing file.
- Releases bump `<version>` and `<creationDate>` in `src/focalpoint.xml` ("Release vX.Y.Z" commits).
