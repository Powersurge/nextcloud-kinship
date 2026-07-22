# Kinship Development Guide

## Requirements

- Nextcloud development environment
- PHP 8.1+
- Node.js
- npm

## Project Structure
```
kinship/
├── appinfo/
├── lib/
├── src/
├── docs/
└── tests/
```

## Backend

Backend code follows:


Controller
|
Service
|
Mapper
|
Entity
|
Database


## Frontend

Frontend uses:

- Vue
- Nextcloud UI components
- REST API communication

## Contribution Guidelines

Developers should:

- Keep extensions independent
- Add documentation
- Add database migrations
- Avoid breaking APIs
- Submit focused pull requests
