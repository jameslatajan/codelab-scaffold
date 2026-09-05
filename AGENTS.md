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

- `npm run typecheck` runs `tsc --noEmit` against `plopfile.ts` and any files
  under `generators/`.
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

Verification for either: run the generator with the *original* module's values and
confirm the output reproduces the source files exactly.

## Testing Guidelines

There is no test framework configured. Run `npm run typecheck` for every
TypeScript change, then run the affected generator and inspect its output. Note in
the pull request which generators you smoke-tested.

Generators are now separate modules, so each should get a snapshot test:
generate into a temp directory and diff against a committed fixture. Not yet
implemented — this is the next structural piece, and it is what makes a
multi-stack scaffolder safe to refactor.

## Commit & Pull Request Guidelines

Use concise imperative subjects with Conventional Commit prefixes such as
`feat: add nomination template`. Keep each commit focused.

Pull requests should name the affected generator and templates, show a
representative generated path (for example, `output/app/Controllers/Example.php`),
list the validation run, and include a before/after snippet when generated markup
changes.
