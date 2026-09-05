# Repository Guidelines

## Project Structure & Module Organization

This repository is `codelab-scaffold`, a Plop-based scaffolder intended to serve
every codelab project regardless of stack. The generators that exist today all
target PHP / CodeIgniter 4; that is the current state, not the intended scope.

`plopfile.js` loads `plopfile.ts`, which is an entry point only: it calls
`registerGenerators` and contains no generator logic. Generator output is written
to `output/`, which is git-ignored — treat anything there as disposable.

### Layout

```
plopfile.ts            # entry point; registers generators
src/core/              # stack-agnostic: paths, strings, fs, registry
generators/<stack>/    # one module per generator
templates/             # matching .hbs templates
```

Each generator module default-exports `{ name, description, prompts, actions }`
with a stack-namespaced `name` (`php:crud`, `php:nomination`, `sample:module`).
`src/core/registry.ts` walks `generators/` and registers what it finds, so adding
a stack means adding a folder — never editing shared code. Files named
`types.ts`, `paths.ts`, `normalize.ts`, or `index.ts` within a stack folder are
treated as shared helpers and skipped by discovery.

Stack-agnostic means casing, validation, `replaceLiteral`, and file-copy helpers.
Anything that knows about controllers, route slugs, upload directories, or
CodeIgniter's `metadata.php` belongs in `generators/php-ci4/`. Generators must
resolve paths from `REPO_ROOT` (`src/core/paths.ts`), never their own
`__dirname`, so a module can be moved without changing where it reads templates
from or writes output to.

### Known limitation

`APP_ROOT_DIR` (`generators/php-ci4/paths.ts`) still defaults to this
repository's parent directory, assuming a sibling CodeIgniter app. When that app
is absent, the metadata-patch action of both PHP generators fails while every
other file still generates. `CI4_APP_ROOT` overrides it. A per-run destination
prompt is the real fix and is not yet implemented.

## Build, Test, and Development Commands

Install dependencies once with `npm install`.

- `npm run check` runs the full gate: typecheck, lint, format check, tests. Run
  this before every commit.
- `npm run typecheck` runs `tsc --noEmit`.
- `npm run lint` / `npm run lint:fix` run ESLint.
- `npm run format` / `npm run format:check` run Prettier.
- `npm test` runs the generator snapshot tests.
- `npm run test:update` rewrites the committed snapshots from actual output —
  review the resulting diff before committing it.
- `npm run generate -- sample:module` starts the sample module generator.
- `npm run generate -- php:crud` starts the CRUD generator.
- `npm run generate -- php:nomination` starts the trainings workflow
  generator.

Use the generated files in `output/` to inspect template changes before copying
them into an application project.

## Coding Style & Naming Conventions

Write TypeScript in the existing style: two-space indentation, semicolons, single
quotes, explicit types for generator data, and small pure helper functions for
normalization or path construction. Keep generator names and template folders
lowercase and hyphenated (for example, `nomination-management`). Template files
use `.hbs`; preserve target-language formatting and keep placeholders such as
`{{routeSlug}}` consistent across related files.

Generator inputs are normalized in the stack's `normalize.ts`, not in templates.
Keep normalization centralized per stack.

## Templating an existing working module

Two supported patterns:

- **Placeholder** (`templates/basic-management/`) — `.hbs` files with real
  Handlebars tokens.
- **Copy-verbatim + literal replace** (`templates/nomination-management/`) —
  source copied byte-for-byte, identifiers swapped at generate time by
  `transformNominationTemplate` using `replaceLiteral`. Prefer this when
  templatizing a module that already works, since the source cannot be broken by
  a malformed token.

Verification for either: run the generator with the _original_ module's values and
confirm the output reproduces the source files exactly.

## Testing Guidelines

`tests/snapshot.test.ts` runs every generator with a fixed answer set and asserts
the generated tree matches a committed fixture byte-for-byte. Cases live in
`tests/cases.ts`; fixtures in `tests/fixtures/expected/<slug>/`.

Adding a generator means adding a case to `tests/cases.ts` and running
`npm run test:update` to record its fixtures. Changing a template means running
the same command and reading the diff — an unexpected diff is the test doing its
job.

`tests/fixtures/ci4-app/` is a minimal CodeIgniter metadata fixture; tests point
`CI4_APP_ROOT` at it so the metadata-patch actions are covered rather than
skipped. Its `START SUB MENU` marker uses four trailing asterisks because that is
what the patch regex requires — note that the generators _emit_ five, so a block
they write cannot be re-matched by their own regex. That inconsistency is
untouched pending a decision about which form the real application uses.

Generators write through `REPO_ROOT`-relative paths rather than a per-run
destination, so tests generate into `output/` and clear it before and after. Set
`KEEP_OUTPUT=1` to inspect what a run produced.

## Coding Standards

ESLint (flat config, `eslint.config.mjs`) and Prettier (`.prettierrc.json`)
enforce style; Prettier is authoritative for formatting and ESLint's stylistic
rules are disabled via `eslint-config-prettier`. `templates/`, `output/`, and
`tests/fixtures/` are excluded from both — template files must keep their
target-language formatting exactly, and fixtures must stay byte-exact.

## Pre-Commit Requirements

Run `npm run check` before every commit. It is the full gate — typecheck, lint,
format check, and snapshot tests — and it must pass. Do not commit with a failing
or skipped check.

The coding standard (`## Coding Standards`) and the testing standard
(`## Testing Guidelines`) are both part of this gate:

- **Coding standard** — ESLint and Prettier must be clean. Run `npm run lint:fix`
  and `npm run format` to resolve mechanical issues; fix real lint errors rather
  than silencing them. Never add a blanket rule disable to get a commit through.
- **Testing standard** — snapshot tests must pass. If a template change alters
  generated output, run `npm run test:update`, read the resulting fixture diff,
  and confirm every change is intended before staging it. An unreviewed snapshot
  update defeats the test.

If a change adds a generator, it must ship with its case in `tests/cases.ts` and
its recorded fixtures in the same commit.

## Commit & Pull Request Guidelines

### Allowed types

- `feat:` — a new feature.
- `fix:` — a bug fix.
- `chore:` — refactors and improvements.
- `doc:` — documentation.

### Rules

- Use exactly one type per commit. If a change needs two types, split the commit.
- Use lowercase for the type. Do not invent new types.
- Keep the subject line short and specific, and do not end it with a period.
- Add further detail in the body, separated from the subject by a blank line.
- Never include secrets, credentials, or decrypted identifiers in a commit
  message.

### Examples

```
feat: add kiosk queue ticket printing
fix: reject malformed encrypted clinic id
chore: extract shared ticket lookup into QueTicketModel
doc: document db_changes.sql workflow
```

### Pull requests

Pull requests should name the affected generator and templates, show a
representative generated path (for example, `output/app/Controllers/Example.php`),
list the validation run, and include a before/after snippet when generated markup
changes.
