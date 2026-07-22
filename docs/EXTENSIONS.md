# Kinship Extensions

Kinship is designed as a core genealogy platform with an extension system.

The core application manages:

- People
- Families
- Relationships
- Events

Extensions add optional functionality without modifying the Kinship core.

## Extension Concept

An extension implements:


IKinshipExtension


Example:

```php
class DnaExtension implements IKinshipExtension
{
    public function getId(): string
    {
        return 'dna';
    }

    public function getName(): string
    {
        return 'DNA Analysis';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function register(): void
    {
        // Register services
    }
}
```
##  Possible Extensions

Examples:

- DNA matching
- GEDCOM import/export
- Photo archives
- Family tree visualization
- Historical records
- Research notes
- AI genealogy assistant
- Design Principles

Extensions should:

- Avoid modifying Kinship core files
- Use public APIs
- Store their own data
Provide clear documentation
Remain independently maintainable
