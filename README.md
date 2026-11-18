[![Joomlashack](https://www.joomlashack.com/images/logo_circle_small.png)](https://www.joomlashack.com)

Shack Locations
===============

#### Advanced mapping extension for Joomla CMS

### Important
**Shack Locations is a fork of FocalPoint by John Pitchers, with his blessing.
We're actively maintaining it and have enjoyed the privilege of building upon
John's foundation.**

------

This is the repository for the free version of Shack Locations, a mapping extension for Joomla.

Use this extension to create advanced, map-based directories with custom legends, markers, infoboxes and hundreds of locations. Define the content for each location using CCK functionality, and give your users the ability to search and filter using familiar UI elements.

## Features

- **Maps** built on Google Maps, with optional custom tabs, a location list, and a legend above, below, left or right of the map.
- **Legends and location types** to group locations, each type with its own marker icon. A location can belong to several types.
- **Custom fields** defined per location type (text, textarea/editor, image, link, email, select list, multi-select). They can be shown in infoboxes and location pages, or placed in a description with `{fieldname}` tokens.
- **Search** by address with a configurable result radius, plus directions and KML overlays.
- **Location pages**, links to other maps, menu items or external URLs, and SEF URLs.
- A set of marker icons, installed to `images/markers`.

## Requirements

- Joomla 3.9 or later
- PHP 7.4 or later
- A [Google Maps JavaScript API key](https://developers.google.com/maps/documentation/javascript/get-api-key), entered in the component's Options

## Installation

Install the package zip with Joomla's extension installer. The package includes the Joomlashack framework library the component needs.

After installing, open **Components → Shack Locations**. You'll be asked for your Google Maps API key first; see the [tutorial](https://www.joomlashack.com/docs/shack-locations/getting-google-maps-javascript-api-key/) for how to get one. Then:

1. Create a map.
2. Create legends and location types.
3. Add locations.

## Development

See [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for:
- building the package from source
- the architecture and data model
- the plugin events available for extending Shack Locations
- coding conventions and the release process

## License

GNU General Public License v2 or later. See [LICENSE.txt](LICENSE.txt).
