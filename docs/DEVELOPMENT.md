# Shack Locations Developer Guide

This guide covers how Shack Locations is built, how the code is organized, and how to extend it. For a feature overview, see the [README](../README.md).

## Naming

Shack Locations is a fork of FocalPoint, and the original names are kept for backward compatibility with existing sites:

| Thing             | Name                                    |
|-------------------|-----------------------------------------|
| Joomla element    | `com_focalpoint`                        |
| PHP class prefix  | `Focalpoint*` (e.g. `FocalpointViewMap`) |
| Language keys     | `COM_FOCALPOINT_*`                      |
| Database tables   | `#__focalpoint_*`                       |
| Plugin group      | `focalpoint`                            |

Newer code uses **SLOC** as shorthand: `SLOC_*` constants, the `sloc`/`slocgoogle` HTMLHelper services, the `jQuery.sloc` JavaScript namespace, and the `onSlocmap*` plugin events.

## Requirements

- Joomla 3.9 or later
- PHP 7.4 or later, with the `json`, `simplexml` and `curl` extensions
- MySQL
- A Google Maps JavaScript API key, set in the component Options. Without one, maps don't render and a warning is shown.
- The Joomlashack framework library (`allediaframework`), which is bundled into the install package.

## Repository layout

```
src/
├── focalpoint.xml          Install manifest
├── install.focalpoint.php  Installer script (post-install / update migrations)
├── admin/                  → administrator/components/com_focalpoint
│   ├── include.php         Shared bootstrap for admin and site
│   ├── library/joomla/     Focalpoint* base classes (autoloaded)
│   ├── library/html/       HTMLHelper services: sloc, slocgoogle
│   ├── models/fields/      Custom form fields (custom fields editor, geocoder, …)
│   └── sql/                Install and update schemas
├── site/                   → components/com_focalpoint
│   ├── layouts/            JLayouts: custom fields, infobox, directions
│   └── views/              map and location views
├── media/                  → media/com_focalpoint (css, images, js)
└── assets/markers/         Moved to images/markers on first install
ShackLocations.mwb          MySQL Workbench model of the schema
```

## Building

Packages are built with [Phing](https://www.phing.info/) using Joomlashack's ShackBuilder. This repo's `build.xml` is a thin client that imports ShackBuilder's build targets.

1. Check out ShackBuilder and the repos it pulls from: ShackInstaller, ShackDefaultFiles, ShackFramework and ShackExtensionSupport.
2. Copy `build.dist.properties` to `build.properties` (gitignored) and set `builder.path` and each `project.*.path` to your local checkouts.
3. Run `phing` from the repository root.

The installable package is written to `packages/` (gitignored). Optional properties in `build.properties`:
- `builder.phplint=1` lints all PHP in the assembled package.
- `project.movePackage` and `project.movePackageTo` copy the finished package somewhere else.

### What the build adds

The `<alledia>` block in `src/focalpoint.xml` tells ShackBuilder and the installer what to do:

- `<relatedExtensions>`: bundles the framework library and the `osmylicensesmanager` system plugin.
- `<include>ShackDefaultFiles</include>`: copies shared files into the package. This includes `admin/library/Installer/`, which `install.focalpoint.php` requires. That folder doesn't exist in this repo.
- `<minify>`: the scripts to minify.
- `<obsolete>`: files, folders and extensions to remove when a site is updated. Add entries here when you delete or rename shipped files.

### Minified JavaScript

The site loads `sloc.min.js`, `googleMap.min.js` and `infobox.min.js`, and these minified files are committed to the repo. When you edit the source `.js` files, regenerate the matching `.min.js` and commit both.

## Architecture

### Bootstrap

Both entry points, `admin/focalpoint.php` and `site/focalpoint.php`, include `admin/include.php`. That file:

1. Loads the Joomlashack framework. If it's missing, the file shows an admin error and returns `false`.
2. Defines `SLOC_ADMIN`, `SLOC_SITE` and `SLOC_LIBRARY`.
3. Loads `FocalpointHelper` (`admin/helpers/focalpoint.php`).
4. Registers a camel-case autoloader for `Focalpoint*` classes rooted at `admin/library/joomla`, so `FocalpointViewAdminList` loads from `view/admin/list.php`.
5. Adds `admin/library/html` to the HTMLHelper include path.
6. Imports the `focalpoint` plugin group.
7. Loads per-client assets (`focalpoint.css` on the site, `admin.css` and Font Awesome in the admin).

### Base classes

`admin/library/joomla` contains the base classes the component's MVC classes extend. Most of them wrap Joomlashack framework abstractions:

| Class                                                  | Used by                                   |
|--------------------------------------------------------|-------------------------------------------|
| `FocalpointView`                                       | All views. Resolves component/menu params, metadata and page heading |
| `FocalpointViewSite`                                   | Site views. Custom field rendering and `{token}` replacement |
| `FocalpointViewAdminList` / `FocalpointViewAdminForm`  | Admin list and edit views, with standard toolbars |
| `FocalpointModelAdmin` / `List` / `Site`               | Models                                    |
| `FocalpointTable`                                      | Tables                                    |
| `FocalpointPlugin`                                     | Base for `focalpoint` group plugins       |

`admin/models/traits.php` (`FocalpointModelTraits`) handles title and alias de-duplication for Save as Copy, for items that don't have categories.

### Data model

```
legends 1───* locationtypes 1───* locations (primary type: locations.type)
                    *                 *
                    └──── location_type_xref ────┘   (all types of a location)
maps 1───* locations (locations.map_id)
```

- A **map** is a page that shows the locations assigned to it.
- A **legend** groups location types. Legends are the columns or sections in a map's legend.
- A **location type** defines a marker icon and the **custom fields** its locations can have.
- A **location** has one primary type (`locations.type`) and can have any number of additional types. Every type of a location, including the primary one, is stored in `#__focalpoint_location_type_xref`, which `FocalpointModelLocation::updateTypes()` maintains on save. On the site, the map query joins through the xref table, so a location appears under each of its types.

Several columns hold JSON:

| Column                            | Shape                                                                 |
|-----------------------------------|-----------------------------------------------------------------------|
| `locationtypes.customfields`      | `{ "<hash>": { "type": "textbox", "name": "...", "label": "...", …type-specific settings } }` |
| `locations.customfieldsdata`      | `{ "<hash>": { "<field name>": <value> } }`. Keyed by the same hash as the field definition |
| `maps.tabsdata`                   | `{ "tabs": { "<hash>": { "name": "...", "content": "..." } }, "mapstyle": … }` |
| `maps.params`, `locations.params` | Override component Options. Merged on top of the global settings at render time |
| `maps.metadata`                   | Page metadata (description, keywords, robots, …)                      |

Earlier versions stored these columns in different formats. `com_focalpointInstallerScript::customPostFlight()` converts old data when a site is updated. If you change a stored format, add a migration there and make it idempotent, because it runs on every update.

### Marker icon resolution

A marker's icon comes from the first of these that is set:
1. The location's own marker
2. The marker of the location's primary type
3. The default marker in the component Options

Marker paths are relative to the site root. The bundled icon sets are installed into `images/markers`.

### Site rendering pipeline

```
FocalpointModelMap::getData()
  ├─ loads the map, merges component params ← map params
  └─ getMarkerData(): locations ⨝ xref ⨝ types ⨝ legends (published only)
       ├─ merges params per location, resolves marker icon and link
       └─ formatCustomFields(): customfieldsdata → { name: {datatype,label,data} }
FocalpointViewMap::display()
  ├─ onSlocmapPrepareRender
  ├─ onContentPrepare on the map text and on each custom tab
  ├─ replaces {fieldname} tokens in location descriptions
  ├─ onSlocmapBeforeRender
  ├─ tmpl/default.php → default_google_tabs.php, default_legend_<position>.php
  │                    → default_google.php → HTMLHelper::_('slocgoogle.map', …)
  └─ onSlocmapAfterRender
```

`JHtmlSlocGoogle::map($id, $params, $center, $markerData)` in `admin/library/html/slocgoogle.php` connects PHP and JavaScript:

- It builds a JSON options object: map properties, search settings, which features are shown, and one entry per marker. Each marker entry includes its infobox HTML, rendered from `site/layouts/marker/infobox/*`.
- The first time it's called on a page, it:
  - loads the Google Maps API with `loading=async&callback=initGoogle`;
  - loads `sloc.min.js` and `googleMap.min.js`;
  - adds the inline functions `initGoogle()` and `initMap()`.

  `initGoogle()` imports the `maps` and `marker` libraries, then loads `infobox.min.js`.
- It adds an `initMap('<id>', options)` call for each map. `initMap` polls until `google.maps` and `InfoBox` are available, then creates a `jQuery.sloc.map.google` instance and registers it under the map id.

The location view (`site/views/location`) follows the same pattern with a single marker.

Strings used by the JavaScript must be registered with `Text::script()` in `slocgoogle.php`.

### JavaScript

| File            | Purpose                                                                          |
|-----------------|----------------------------------------------------------------------------------|
| `sloc.js`       | `jQuery.sloc` utilities: `loadScript()`, `sprintf()`, `tabs()`, and the map registry `jQuery.sloc.map.register/get/update` |
| `googleMap.js`  | `jQuery.sloc.map.google`: markers, infoboxes, clustering, legend and type toggles, address search with radius, directions, KML overlays, and the location list tab |
| `infobox.js`    | Third-party InfoBox library for Google Maps                                      |
| `jquery-ui.js`  | jQuery UI, loaded by `HTMLHelper::_('sloc.ui')` (used by the admin custom fields editor) |

Other scripts can get a map instance with `jQuery.sloc.map.get('<map id>')`.

### Routing

`site/router.php` (`FocalpointRouter`) works like this:
- A map or location that has its own menu item routes to that menu item.
- Otherwise the item's alias is appended as a URL segment.
- When parsing a URL, a trailing segment is looked up as a location alias. If the active menu item is a map, the lookup is limited to that map.
- Menu items store the target item's id in the `item_id` request parameter.

## Extending Shack Locations

### Plugins

Plugins in the `focalpoint` group are imported on every request and should extend `FocalpointPlugin`, which:
- loads the plugin's language files automatically;
- provides `isMapExcluded(int $mapId)`, which checks the map against an `excludemaps` plugin parameter.

The component fires these events:

| Event                     | Fired from                          | Arguments                     | Purpose |
|---------------------------|-------------------------------------|-------------------------------|---------|
| `onSlocmapBeforeLoad`     | Admin map edit view                 | `&$item`                      | Adjust the map before the edit form renders |
| `onSlocmapTabs`           | Admin map edit template             | `$form`                       | Return the name of a form fieldset to show as an extra tab in the map editor. The tab label comes from the fieldset's `label` |
| `onSlocmapBeforeSave`     | `FocalpointModelMap::save()`        | `&$data`                      | Modify the submitted map data before saving |
| `onSlocmapAfterSave`      | `FocalpointControllerMap::postSaveHook()` | `$validData` (the validated form data) | React after a map is saved |
| `onSlocmapPrepareRender`  | Site map and location views         | `&$item`                      | Change the item before params are merged and content plugins run |
| `onSlocmapBeforeRender`   | Site map view                       | `&$item`                      | Last chance to change the map before the templates render |
| `onSlocmapAfterRender`    | Site map view                       | `&$item`                      | Runs after the templates have rendered |

Content plugins get `onContentPrepare` with context `com_focalpoint.map` for the map text and for each custom tab.

Add-on plugins depend on these event names and signatures, so treat them as a public API.

To add fields to the map edit form, provide a form named `map.pro`. `FocalpointModelMap::preprocessForm()` merges it into the map form if it exists.

### Template overrides

Standard Joomla overrides work:

- Views: `templates/<template>/html/com_focalpoint/map/default_legend_left.php`, `.../location/default.php`, and so on.
- Layouts: `templates/<template>/html/layouts/com_focalpoint/marker/infobox/content.php`, `.../custom/field/<type>.php`, and so on.

### Adding a custom field type

1. Add the type name to the list of types in `admin/models/fields/customfields.php`.
2. If the type needs settings in the location type editor, add `renderSubfield<Type>()` to `admin/models/fields/customfields.php`.
3. Add `renderSubField<Type>()` to `admin/models/fields/customfieldsdata.php` to render the input on the location form. Both classes find these methods by name with `ucfirst($type)`.
4. Add a site layout at `site/layouts/custom/field/<type>.php`. It receives `label`, `data` and `showlabel`.
5. Add language strings to every language under `admin/language`.

## Database changes

Make every schema change in two places:

1. `src/admin/sql/install/mysql/install.sql`, for new installs.
2. A new `src/admin/sql/updates/mysql/<version>.sql`, named for the release that introduces the change, for existing sites.

If you can, also update `ShackLocations.mwb`. Changes to data, as opposed to schema, belong in the installer script (see [Data model](#data-model)).

## Joomla version compatibility

The extension supports Joomla 3 alongside Joomla 4 and later:

- Each admin view has two templates: the default (`edit.php`, `default.php`) and a Joomla 3 variant (`edit.j3.php`, `default.j3.php`). When you change one, check whether the other needs the same change.
- Version-specific PHP branches on `Joomla\CMS\Version::MAJOR_VERSION`.
- Prefer the framework's `Alledia\Framework\Factory` where surrounding code uses it. It smooths over API differences between Joomla versions.

## Coding conventions

- Use the PSR-12 style already in the code.
- Every PHP file starts with the GPL docblock and both copyright lines (John Pitchers and Joomlashack), then `defined('_JEXEC') or die();`.
- Files with legacy, non-namespaced classes wrap that header in `phpcs:disable`/`enable` comments for PSR1 side effects and missing namespaces. Copy the pattern from an existing file.
- Translations: en-GB is the source. fr-FR and nl-NL should get the new keys too.

## Releases

Changes reach `main` through pull requests. A release is a single commit, titled `Release vX.Y.Z`, that updates `<version>` and `<creationDate>` in `src/focalpoint.xml`. The commit is tagged `vX.Y.Z`. Sites get updates from the update server listed in the manifest.
