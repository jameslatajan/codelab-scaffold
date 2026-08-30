# Repository Guidelines

## Project Structure & Module Organization

This repository is a Plop-based generator for PHP CRUD modules. `plopfile.js`
loads the TypeScript implementation in `plopfile.ts`, which defines prompts,
normalization rules, and output actions. Handlebars templates are grouped by
generator in `templates/basic-management/`, `templates/nomination-management/`,
and `templates/module/`. Generated examples and generator output are written to
`output/app/`; treat these as generated artifacts unless a change specifically
requires updating an example. `README.md` documents the supported generators.

## Build, Test, and Development Commands

Install dependencies once with `npm install`.

- `npm run typecheck` runs `tsc --noEmit` against `plopfile.ts` and any future
  files under `generators/`.
- `npm run generate -- module` starts the sample module generator.
- `npm run generate -- "basic management"` starts the CRUD generator.
- `npm run generate -- "nomination management"` starts the trainings workflow
  generator.

Use the generated files in `output/` to inspect template changes before copying
them into an application project.

## Coding Style & Naming Conventions

Write TypeScript in the existing style: two-space indentation, semicolons,
single quotes, explicit types for generator data, and small pure helper
functions for normalization or path construction. Keep generator names and
template folders lowercase and hyphenated (for example,
`nomination-management`). Template files use `.hbs`; preserve PHP formatting
and placeholders such as `{{routeSlug}}` consistently across related views.

Generator inputs are normalized to underscore-separated route and table names.
Add or change this behavior centrally in `plopfile.ts`, rather than duplicating
normalization in templates.

## Testing Guidelines

There is no test framework or coverage target configured. Run `npm run
typecheck` for every TypeScript change, then run the affected generator and
inspect its expected controller, route, views, and metadata output. Keep a
manual smoke-test note in the pull request when generation behavior changes.

## Commit & Pull Request Guidelines

The repository has no commit history yet, so no project-specific convention can
be inferred. Use concise imperative subjects, preferably Conventional Commit
prefixes such as `feat: add nomination template`. Keep each commit focused.

Pull requests should explain the affected generator and templates, show a
representative generated path (for example,
`output/app/Controllers/Example.php`), list validation run, and include a
before/after snippet or screenshot when generated UI markup changes.
