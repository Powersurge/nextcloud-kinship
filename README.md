# Kinship

Kinship is a family management and genealogy app for Nextcloud.

Preserve people, relationships, events, and evidence on your own server.

## Features

- **People and families** — manage individuals, family groups, and membership
- **Relationships** — store parent, partner, and other links; derived relationships are computed from the graph
- **Events and timelines** — record life events with participants; view a person timeline
- **Family trees** — build a nested tree from parent relationships
- **Places** — store locations; optional map lookup via Nominatim or Google Geocoding (admin-configurable)
- **Evidence** — sources, citations, and facts for research workflows
- **Media** — attach Nextcloud files and albums to genealogy records
- **Permissions** — role-based access (owner, editor, contributor, viewer)
- **Privacy controls** — living-person handling and an admin privacy threshold for unknown death dates

## Requirements

- Nextcloud **31–34**
- PHP **8.1+**
- A supported Nextcloud database (SQLite, MariaDB/MySQL, or PostgreSQL)

## Installation

### From the App Store (recommended)

1. In Nextcloud, open **Apps**
2. Search for **Kinship**
3. Install and enable the app

### Manual install

```bash
cd /path/to/nextcloud/apps
git clone https://github.com/Powersurge/nextcloud-kinship.git kinship
cd kinship
composer install --no-dev
npm ci
npm run build
php occ app:enable kinship
```

Then open **Kinship** from the Nextcloud navigation menu.

## Configuration

Admin settings are under **Administration settings → Kinship**:

- **Privacy threshold** — age used when a death date is unknown to decide living-person privacy (default 110)
- **Place lookup** — choose Nominatim or Google Geocoding, and set provider options

## Privacy

Genealogy data stays in your Nextcloud instance.

Optional place lookup can send search text or coordinates to an external geocoder:

- **Nominatim** (default base URL: OpenStreetMap Nominatim), or
- **Google Geocoding**, if configured with an API key

No geocoding traffic is sent unless an admin enables and uses place lookup. Media is stored as Nextcloud files; Kinship stores references, not a separate media silo.

## Development

```bash
git clone https://github.com/mahmudz/nextcloud-kinship.git kinship
cd kinship
composer install
npm install
npm run build
```

See [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for project structure and contribution notes.

### Documentation

| Doc | Topic |
| --- | --- |
| [docs/API.md](docs/API.md) | REST API |
| [docs/ROADMAP.md](docs/ROADMAP.md) | Planned work |

### API

REST base path (relative to the Kinship app): `/api/v1`

Main resources include people, families, relationships, events, places, facts, sources, citations, media, albums, tree, and timeline.

## Support

- Bugs and feature requests: [GitHub Issues](https://github.com/mahmudz/nextcloud-kinship/issues)
- Author: [Mahmudul Hasan](https://mahmudz.dev) (`hello@mahmudz.dev`)

## License

[AGPL-3.0-or-later](https://www.gnu.org/licenses/agpl-3.0.html)
