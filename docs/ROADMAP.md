# Kinship Roadmap

Kinship is a genealogy platform built on Nextcloud that emphasizes evidence-based research, collaboration, extensibility, and long-term data preservation.

**Status Legend:**
- `[x]` Implemented
- `[-]` In Progress / Partially Implemented
- `[ ]` Planned

---

## v0.1 — Foundation

### Core Genealogy Data Model
- [x] People
- [x] Families
- [x] Events
- [x] Participants
- [x] Places
- [x] Relationships
- [x] Facts
- [x] Claims — basic proposal/accept/reject workflow
- [x] Family membership derived from relationships

### Date Handling
- [x] Exact dates
- [x] Approximate dates
- [x] Date ranges
- [x] Unknown dates
- [x] Reusable date value model across genealogy entities

### Basic Source Model
- [x] Sources
- [x] Citations
- [-] Source/repository relationship — repository is currently free text
- [ ] Dedicated Repositories entity

### Media References
- [x] Store Nextcloud media/file IDs
- [x] Link media to genealogy objects
- [x] Media upload
- [x] Media metadata/captions
- [x] Albums
- [-] Complete media ownership/removal workflow

### Platform
- [x] REST API
- [x] Extension framework
- [x] Database migrations
- [x] Core role/permission model
- [x] Permission enforcement on core operations

---

## v0.2 — Data Management

### Menu / Application Structure
- [x] Dashboard
- [x] Genealogy
  - [x] People
  - [x] Families
  - [x] Relationships
  - [x] Events
  - [-] Places — available through API, awaiting dedicated management UI
  - [x] Sources
  - [-] Media — implemented via entity interfaces, awaiting standalone area
- [-] Visualization
  - [x] Family Tree
  - [-] Timeline
  - [ ] Maps
- [ ] Research
  - [ ] Tasks
  - [ ] Questions
  - [ ] Notes
- [-] Tools
  - [-] Search
  - [ ] Reports
  - [x] Import
  - [ ] Export
  - [ ] Activity

### GEDCOM
- [x] GEDCOM import
- [-] GEDCOM parsing/date handling
- [ ] GEDCOM validation
- [ ] Import preview
- [ ] Import review/approval workflow
- [ ] GEDCOM export

### Events
- [x] Core event types
- [x] Custom event type data model
- [x] Custom event API
- [ ] Custom event management UI

### Media Attachments
- [x] Store Nextcloud media IDs
- [x] Link media to people
- [x] Link media to families
- [x] Link media to events
- [x] Link media to sources
- [x] Albums
- [-] Complete shared-media ownership model
- [ ] Removal request workflow

### Settings
- [x] Privacy threshold setting
- [x] Configurable privacy threshold
- [x] Automatic living-status calculation
- [x] Scheduled privacy-threshold processing

### Search
- [-] Basic application search infrastructure
- [ ] People search
- [ ] Families search
- [ ] Events search
- [ ] Places search
- [ ] Sources search
- [ ] Notes search
- [ ] Unified genealogy search

### Sharing and Permissions
- [x] Owner role
- [x] Editor role
- [x] Contributor role
- [x] Viewer role
- [x] Permission storage
- [x] Permission checks
- [x] Permission management API
- [-] Complete sharing UI
- [ ] Object-level sharing
- [ ] Share filtering

### Privacy and Data Protection
- [x] Living-person detection
- [x] Privacy threshold
- [-] Privacy-aware living status
- [ ] Privacy profiles
- [ ] Restricted information controls
- [ ] Sensitivity levels
  - [ ] Public
  - [ ] Family
  - [ ] Private
  - [ ] Restricted
- [ ] Object-level visibility rules
- [ ] Share filtering
- [ ] Automatic protection of living-person sensitive data
- [ ] Person-link request/approval workflow

### Collaboration
- [ ] Version history
- [ ] Audit logging
- [-] Claims/proposed changes
- [ ] Complete claim workflow
- [ ] Change history per record

### Data Quality
- [ ] Basic duplicate detection
- [ ] Duplicate review
- [ ] Merge workflow

### Names
- [ ] Separate names table
- [ ] Multiple names per person
- [ ] Name types
  - [ ] Birth name
  - [ ] Married name
  - [ ] Alternate name
  - [ ] Legal name
  - [ ] Other
- [ ] Name date ranges
- [ ] Historical names
- [ ] Preferred/current name handling

---

## v0.3 — Research & Evidence

### Source Management
- [x] Sources
- [x] Citations
- [ ] Repositories
- [ ] Repository/source relationships
- [ ] Advanced citation structure

### Evidence Management
- [x] Facts
- [x] Claims
- [-] Evidence attached to claims
- [ ] Evidence records
- [ ] Conflicting evidence model

### Research Notes
- [ ] Research questions
- [ ] Research links
- [ ] Research tasks
- [ ] Research comments
- [ ] Research revisions
- [ ] Research notes

### Claim Resolution
- [-] Proposed claims
- [-] Pending review
- [x] Accepted claims
- [x] Rejected claims
- [ ] Superseded claims
- [ ] Archived claims
- [-] Confidence levels
- [ ] Confidence weighting
- [ ] Conflicting evidence
- [ ] Automatic application of accepted claims
- [ ] Full claim resolution workflow

### Research Workflow
- [ ] Research workspace
- [ ] Open research questions
- [ ] Research task management
- [ ] Evidence review workflow
- [ ] Research conclusions

---

## v0.4 — Collaboration & Visualization

- [ ] Comments
- [ ] Notifications
- [-] Timeline views
- [ ] Calendar integration
- [ ] Maps integration
- [-] Relationship visualization
- [ ] Advanced duplicate detection
- [ ] Merge tools
- [ ] Conflict resolution
- [ ] Interactive family tree editor
- [ ] Collaborative editing improvements

---

## v0.5 — Reporting & Export

- [ ] Family reports
- [ ] Individual reports
- [ ] Timeline reports
- [ ] Source reports
- [ ] Research reports
- [ ] GEDCOM enhancements
- [ ] Additional export formats
  - [ ] JSON
  - [ ] CSV
  - [ ] PDF
- [ ] Printable family trees
- [ ] Printable pedigree charts
- [ ] Descendant charts

---

## v1.0 — Stable Platform

- [ ] Stable REST API
- [ ] Stable extension API
- [ ] Extension marketplace
- [ ] Third-party developer tools
- [ ] Complete documentation
- [ ] Performance optimization
- [ ] Backup/restore tools
- [ ] Migration tools

---

## Current Development Priority

Based on the implementation currently present in v0.1.1, the next major development priorities are:

1. **Separate Names from People:**
   - Introduce a `names` database table.
   - Support multiple historical names per person.
   - Add name dates and types.
   - Preserve a clear preferred/current name handling logic.

2. **Finish v0.2 Data Management:**
   - Complete GEDCOM validation, preview/review, and export.
   - Build unified genealogy search.
   - Finish dedicated Places and Media management UIs.
   - Add Custom Event management UI.

3. **Finish Privacy and Collaboration:**
   - Enforce living-person privacy across API responses, exports, reports, and shared views.
   - Add object-level visibility and privacy profiles.
   - Add audit/history logging infrastructure.
   - Complete sharing UI and workflows.

4. **Strengthen Evidence Architecture:**
   - Introduce dedicated Repositories entity.
   - Improve source/citation relationships.
   - Expand claim resolution and confidence weighting.
   - Add research notes, questions, tasks, and evidence workflows.

5. **Data Quality:**
   - Build duplicate detection, review, and safe-merge tools.

6. **Visualization and Collaboration:**
   - Complete interactive timeline and map integrations.
   - Implement comment streams and Nextcloud notifications.
   - Build advanced family-tree editing tools.
