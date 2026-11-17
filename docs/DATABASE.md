# Database files

- `database/schema.sql`: structure-only export derived from the provided current database; all INSERT statements removed.
- `database/seed_local.sql`: a minimal fictional technical account/profile for local startup.
- `database/migrations/`: historic incremental migrations kept for code archaeology. **Do not run them after importing schema.sql**.
- `database/repairs/`: historic repair scripts; do not run on a fresh installation.

Never commit a real phpMyAdmin dump, personal documents or local configuration.
