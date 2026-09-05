# codelab-scaffold

A Plop-based scaffolder for codelab projects. It generates working, project-shaped
code from templates so a new module starts from a known-good baseline instead of
copy-paste.

The generators shipping today target PHP / CodeIgniter 4, but the repository is
intended to host generators for any stack — see [Adding a stack](#adding-a-stack).

## Scripts

- `npm run generate` — run a generator interactively
- `npm run plop` — same, direct Plop invocation
- `npm run typecheck` — `tsc --noEmit`

## First-time install

```bash
npm install
```

## Available generators

### `sample:module`

```bash
npm run generate -- sample:module
```

Writes a sample TypeScript module to `output/`.

### `php:crud` (PHP / CodeIgniter 4)

```bash
npm run generate -- php:crud
```

Scaffolds a full `Master Files` CRUD module:

- `output/app/Controllers/<Controller>.php`
- `output/app/Routes/<Controller>.php`
- `output/app/Views/modules/master_files/<route_slug>/...`
- `output/app/Views/modules/master_files/metadata.php` patch copy

Prompt values: `moduleName`, `routeSlug`, `tableName`, `primaryKey`, `mainField`,
`iconClass`

### `php:nomination` (PHP / CodeIgniter 4)

```bash
npm run generate -- php:nomination
```

Scaffolds a trainings workflow module:

- `output/app/Controllers/<Controller>.php`
- `output/app/Routes/<Controller>.php`
- `output/app/Views/modules/trainings/<route_slug>/...`
- `output/app/Views/modules/trainings/metadata.php` patch copy

Generated views: `show.php`, `create.php`, `edit.php`, `view.php`,
`printlist.php`, `replace_files.php`, `multiple_div.php`, `multiple_sec.php`,
`section_emp.php`, `individual_emp.php`

Prompt values: `moduleName`, `routeSlug`, `tableName`, `primaryKey`,
`controllerClass`, `iconClass`, `uploadDir`

## Output

Generators write to `output/`, which is git-ignored. Inspect the result there,
then copy it into the target application.

## Turning a working module into a template

Two patterns are in use:

**Placeholder** (`templates/basic-management/`) — `.hbs` files carrying real
Handlebars tokens (`{{controllerClass}}`, `{{routeSlug}}`, …).

**Copy-verbatim + literal replace** (`templates/nomination-management/`) — the
source files are copied byte-for-byte with no tokens, and `plopfile.ts` swaps the
hardcoded identifiers at generate time via `replaceLiteral`. Lower risk when
templatizing an existing, working module.

## Layout

```
plopfile.ts            # entry point; registers generators, holds no logic
src/core/              # stack-agnostic: paths, strings, fs, registry
generators/<stack>/    # one module per generator
templates/             # matching .hbs templates
```

## Adding a stack

1. Create `generators/<stack>/<name>.ts` default-exporting
   `{ name, description, prompts, actions }` with a namespaced `name`
   (`react:component`).
2. Add templates under `templates/<stack>/`.
3. Nothing else — `src/core/registry.ts` discovers and registers it.

Files named `types.ts`, `paths.ts`, `normalize.ts`, and `index.ts` inside a stack
folder are treated as shared helpers and are not registered as generators.

## Known limitation

The metadata-patch action of both PHP generators reads
`app/Views/modules/*/metadata.php` from `APP_ROOT_DIR`, which defaults to this
repository's parent directory. When that sibling CodeIgniter app is absent the
action fails while all other files still generate. Set `CI4_APP_ROOT` to point at
a real CI4 application, or ignore the failure if you only need the module files.
