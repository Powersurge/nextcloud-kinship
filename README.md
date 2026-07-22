# Kinship

Kinship is a genealogy and family history platform for Nextcloud.

It provides a foundation for managing:

- People
- Families
- Relationships
- Events
- Family timelines
- Family trees

Kinship is designed as a core platform that developers can extend with additional modules.

## Features

### Core

- Person management
- Family groups
- Relationship tracking
- Historical events
- Timeline views
- Family tree visualization

### Import

- GEDCOM import foundation

### Developer Platform

Kinship includes an extension framework for:

- DNA analysis
- Photo archives
- Historical records
- Research tools
- AI genealogy assistants

## Requirements

- Nextcloud
- PHP 8+
- MariaDB / PostgreSQL / SQLite

## Installation

Clone into:


nextcloud/apps/kinship


Enable the app:


Settings → Apps → Kinship


## Development

Install dependencies:

```
composer install
npm install
```

Build frontend:

```
npm run build
```

## API

API base:


/api/v1


Available resources:


- /people
- /families
- /relationships
- /events
- /tree
- /import/gedcom


## Extension Development

See:


docs/EXTENSIONS.md


## License

AGPL-3.0
# Kinship

A modern family management and genealogy platform for Nextcloud.

## Overview

Kinship is an open-source Nextcloud application for managing family information, genealogy, events, and shared memories.

Unlike traditional genealogy software, Kinship is designed as a platform. The core application provides the fundamental building blocks for family management while allowing additional functionality to be added through extensions.

## Features (v0.1)

- People management
- Family management
- Relationship management
- Family events
- Timeline
- Media attachments
- Search
- REST API
- Role-based permissions

## Planned Extensions

- GEDCOM Import/Export
- Interactive Family Tree
- Calendar Synchronization
- AI-assisted Record Extraction
- DNA Integration
- Historical Maps
- Family Recipes
- Memorial Pages
- Reports
- Dashboard Widgets

## Architecture

```
People
    │
Relationships
    │
Families
    │
Events
    │
Media
```

The core remains intentionally lightweight. Advanced functionality is implemented as optional extensions.

## Technology

- PHP 8.2+
- Nextcloud App Framework
- Vue 3
- Vite
- Composer
- PHPUnit

## Development

Clone the repository:

```bash
git clone https://github.com/Powersurge/Kinship.git
cd kinship
```

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm install
```

Build assets:

```bash
npm run build
```

## Roadmap

### v0.1
- Project foundation
- People
- Families
- Relationships
- Events
- Timeline

### v0.2
- Search
- Notifications
- Dashboard
- Plugin SDK

### v1.0
- Stable Extension API
- Complete Documentation
- Production Release

## Contributing

Contributions are welcome.

Please submit pull requests for bug fixes, documentation improvements, and new extensions.

## License

GNU Affero General Public License v3.0