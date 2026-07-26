# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Kinship is a **Nextcloud app** (app id `kinship`) for family management and genealogy — people, relationships, events, and family trees. It is a PHP backend (Nextcloud App Framework) serving a JSON API, paired with a Vue 3 single-page frontend. Requires Nextcloud 31–34, PHP 8.1+, Node 24 / npm 11.

## Approach
- Read existing files before writing. Don't re-read unless changed.
- Thorough in reasoning, concise in output.
- Skip files over 100KB unless required.
- No sycophantic openers or closing fluff.
- No emojis or em-dashes.
- Do not guess APIs, versions, flags, commit SHAs, or package names. Verify by reading code or docs before asserting.

## Commands

Frontend (npm):
- `npm run build` — production build (output goes to `js/`)

## Architecture

### Backend (`lib/`, PSR-4 namespace `OCA\Kinship\`)

Layered per Nextcloud convention: **Controller → Service → Mapper → Entity (Db)**.

- `Controller/` — thin HTTP handlers returning `DataResponse`. One controller per domain (Person, Family, Relationship, Event, Tree, Import, Timeline). `PageController` serves the SPA shell; `ApiController` is a leftover template stub.
- `Service/` — business logic. `TreeService` recursively walks `parent`-type relationships to build the nested tree; other services are mostly CRUD passthroughs to their mappers.
- `Db/` — each domain has an `Entity` (implements `JsonSerializable`, defines the JSON shape sent to the frontend) and a `QBMapper`.
- `AppInfo/Application.php` — the DI container wiring. **Every mapper/service/importer is manually registered here** via `registerService()`; add new services to this file.
- `Import/` — GEDCOM import pipeline: `GedcomParser` (raw text → records) → `GedcomExtractor` (records → people/families data) → `GedcomImporter` orchestrates, with `GedcomFamilyImporter` / `GedcomRelationshipImporter` persisting.
- `Migration/` — versioned schema steps (`VersionNNNNDate*.php`), applied in order.
- `Extension/` — a plugin system (`ExtensionRegistry` + `ExtensionManager`) letting `IKinshipExtension` implementations register themselves. Scaffolding for third-party kinship extensions.

**Database tables are prefixed `kinship_` (e.g. `kinship_persons`), not `kinship_`** — the app id and table prefix intentionally differ. Keep using `kinship_` for new tables.

### Frontend (`src/`)

Vue 3 (`<script setup>`) SPA, built by `@nextcloud/vite-config`, mounted into `#kinship` (see `templates/index.php`).

- `main.ts` → `App.vue` → `vue-router` (hash history, `createWebHashHistory`). Routes are in `src/router/index.ts` with lazy-loaded `views/`.
- `views/` — page-level components (one per route). `components/` — reusable pieces (cards, forms, modals, `ui/`). Import aliases: `@` → `src/`, `@components` → `src/components/`.
- Uses `@nextcloud/vue` component library and `@nextcloud/axios` / `@nextcloud/router` for API calls.
- Use `@nextcloud/axios` instead of fetch and write recommended code based on NextCloud App development documentation

### Routing note
- `appinfo/routes.php` declares the canonical REST API under `/api/v1/...`.
