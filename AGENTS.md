# Repository Guidelines

## Project Structure & Module Organization

This repository is `codelab-scaffold`, a Plop-based scaffolder intended to serve
every codelab project regardless of stack. The generators that exist today all
target PHP / CodeIgniter 4; that is the current state, not the intended scope.

`plopfile.js` loads the TypeScript implementation in `plopfile.ts`, which defines
prompts, normalization rules, and output actions. Handlebars templates are grouped
by generator in `templates/basic-management/`, `templates/nomination-management/`,
and `templates/module/`. Generator output is written to `output/`, which is
git-ignored — treat anything there as a disposable artifact.

### Intended layout

`plopfile.ts` is currently a single ~700-line file holding every generator's
types, helpers, prompts, and actions. New stacks should not be added to it. The
target layout, which `tsconfig.json` already anticipates via
`"include": ["plopfile.ts", "generators/**/*.ts"]`:

```
plopfile.ts          # discover and register generators
src/core/            # stack-agnostic helpers only
generators/<stack>/  # one module per generator, exporting { description, prompts, actions }
templates/<stack>/   # matching .hbs templates
```

Stack-agnostic means: casing, validation, `replaceLiteral`, file-copy helpers.
Anything that knows about controllers, route slugs, upload directories, or
CodeIgniter's `metadata.php` belongs under `generators/php-ci4/`, not in shared
scope. Note that `APP_ROOT_DIR` (`plopfile.ts:33`) currently resolves to this
repository's parent directory and assumes a sibling CodeIgniter app — that
coupling must be removed before the repo can be used from anywhere else.

Generator names should be namespaced by stack (`php:crud`, `react:component`)
once more than one stack exists.

## Build, Test, and Development Commands

Install dependencies once with `npm install`.

- `npm run typecheck` runs `tsc --noEmit` against `plopfile.ts` and any files
  under `generators/`.
- `npm run generate -- module` starts the sample module generator.
- `npm run generate -- "basic management"` starts the CRUD generator.
- `npm run generate -- "nomination management"` starts the trainings workflow
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

Generator inputs are normalized centrally in `plopfile.ts`, not in templates. Keep
it that way — when normalization moves into `generators/<stack>/`, it stays
centralized per stack.

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

Once generators are split into separate modules, each should get a snapshot test:
generate into a temp directory and diff against a committed fixture. This is what
makes a multi-stack scaffolder safe to refactor.

## Commit & Pull Request Guidelines

Use concise imperative subjects with Conventional Commit prefixes such as
`feat: add nomination template`. Keep each commit focused.

Pull requests should name the affected generator and templates, show a
representative generated path (for example, `output/app/Controllers/Example.php`),
list the validation run, and include a before/after snippet when generated markup
changes.
