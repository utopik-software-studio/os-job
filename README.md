# OS::JOB

**An experimental open-source project by UTOPIK® Software Studio**

*Organize your job search. Own your data. Explore other possibilities.*

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE) ![Status](https://img.shields.io/badge/status-experimental-orange)

> **Experimental / local-only.** No authentication or multi-user security boundary. **Do not deploy to a public server.** Development is currently paused; maintenance and response times are not guaranteed.

## Why OS::JOB exists

OS::JOB began during a real career transition. Tracking vacancies, applications, recruiters, interviews and supporting documents across different services became difficult. Instead of maintaining separate spreadsheets and notes, its creator started building a single, locally operated workspace. The project evolved through iterative releases and now lives under **UTOPIK® Software Studio**, where others can study it, adapt it and propose improvements.

## Features (experimental)

- Job-offer tracking, search spaces and application follow-up
- Career profile, CV/document tools and interview workflows
- Dashboards, statistics, reporting and historical roadmap
- Manual offer import and rule-based copy/paste extraction (v0.14.1)
- Optional local AI workflows (Ollama); core functions do not require it

Not all features have automated tests. Text extraction is heuristic; review every detected field before saving.

## Stack

PHP 8.1+ (original local environment: PHP 8.2), MariaDB/MySQL, Apache (XAMPP), HTML/CSS/JavaScript. No mandatory cloud service. See `composer.json` for declared PHP requirements.

## Installation

See [INSTALLATION.md](docs/INSTALLATION.md). The repository contains **schema only** and a minimal fictitious local bootstrap, not the original database or personal files.

## Project status and roadmap

Current code: **v0.14.1**, experimental. The roadmap is historical and aspirational; future features are **not commitments**. See [ROADMAP.md](docs/ROADMAP.md) and [KNOWN_LIMITATIONS.md](docs/KNOWN_LIMITATIONS.md).

## Contributing

Issues, documentation fixes, tests and pull requests are welcome. Read [CONTRIBUTING.md](CONTRIBUTING.md), [SECURITY.md](SECURITY.md) and [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## License

[MIT](LICENSE). Copyright notices must be retained. External assets and third-party code, if any, must be checked for their own licensing before redistribution.

---

**Français** — OS::JOB est né d'un besoin personnel pendant une recherche d'emploi : centraliser les offres, candidatures, documents et entretiens dans une application locale. Ce projet expérimental UTOPIK® est publié pour être étudié, adapté et éventuellement poursuivi par la communauté. Il n'est **pas sécurisé pour un hébergement public** et son développement est actuellement en pause.
