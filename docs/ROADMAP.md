# Kinship Roadmap
Kinship is a genealogy platform built on Nextcloud that emphasizes evidence-based research, collaboration, extensibility, and long-term data preservation.

## v0.1 — Foundation
- Core genealogy data model
  - People
  - Families
  - Events
  - Participants
  - Places
  - Relationships
  - Facts
  - Claims
- Date handling
  - Exact dates
  - Approximate dates
  - Date ranges
  - Unknown dates
- Basic source model
  - Sources
  - Citations
- Media references
- API
- Extension framework
- Database migrations
- Core permissions model

## v0.2 — Data Management
- GEDCOM import/export
- GEDCOM validation
- Import preview and review
- Custom event types
- Media attachments
  - Store Nextcloud media IDs
  - Link media to any genealogy object
- Settings
  - Privacy threshold (controls how long people with an unknown death date are treated as living for privacy purposes. Default 110)
- Search
  - People
  - Families
  - Events
  - Places
  - Sources
  - Notes
- Sharing
- Permissions

  |Action              |Owner| Editor |               Contributor               |Viewer|
  |--------------------|-----|--------|-----------------------------------------|------|
  |Create people       |	✓  |	  ✓   |   	               ✓                    |	  ✗  |
  |Edit names/dates    |	✓  |	  ✓   |✓(proposes claim for substantive changes)|	  ✗  |
  |Add sources         |	✓  |	  ✓   |	                   ✓                    |	  ✗  |
  |Add research notes  |	✓  |	  ✓   |	                   ✓                    |  	✗  |
  |Add claims          |	✓  |	  ✓   |	                   ✓                    |	  ✗  |
  |Accept/reject claims|	✓  |Optional|	                   ✗                    |	  ✗  |
  |Merge duplicates    |	✓  |	  ✓   |	                   ✗                    |  	✗  |
  |Delete records      |	✓  |Optional|	                   ✗                    |  	✗  |
  |Manage Custom Events|  ✓  |    ✓   |                    ✗                    |   ✗  |
  |Manage Places       |  ✓  |    ✓   |                    ✗                    |   ✗  |
  |Manage Participants |  ✓  |    ✓   |                    ✓                    |   ✗  |
- Privacy & Data Protection
  - Media references
  - Media permissions
  - Living person detection
    - Calculate if user is still alive (living, deceased, unknown if age exceeds privacy threshold & user is not deceased)
    - If user is still living, prevent their exact birthday, email, phone, address, photos, & notes from being exposed in shared trees, public links, exports, API's, or reports
  - Privacy profiles
  - Restricted information controls
    - Sensitivity levels (public, family, private, & restricted)
  - Object-level visibility rules
  - Share filtering
- Version history
- Audit logging
- Basic duplicate detection

## v0.3 — Research & Evidence
- Source management
  - Sources
  - Repositories
  - Citations
- Evidence management
- Research notes
  - Research questions
  - Research links
  - Research tasks
  - Research comments
  - Research revisions
- Claim resolution
  - Confidence levels
  - Accepted/rejected claims
  - Conflicting evidence
- Research workflow

## v0.4 — Collaboration & Visualization
- Interactive family tree editor
- Tree navigation
- Relationship visualization
- Timeline views
- Calendar integration
- Maps integration
- Notifications
- Comments
- Advanced duplicate detection
- Merge tools
- Conflict resolution

## v0.5 — Reporting & Export
- Family reports
- Individual reports
- Timeline reports
- Source reports
- Research reports
- GEDCOM enhancements
- Additional export formats
  - JSON
  - CSV
  - PDF
- Printable family trees
- Printable pedigree charts
- Descendant charts

## v1.0 — Stable Platform
- Stable REST API
- Stable extension API
- Extension marketplace
- Third-party developer tools
- Documentation
- Performance optimization
- Backup/restore tools
- Migration tools


