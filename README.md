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

### `module`

```bash
npm run generate -- module
```

Writes a sample TypeScript module to `output/`.

### `basic management` (PHP / CodeIgniter 4)

```bash
npm run generate -- "basic management"
```

Scaffolds a full `Master Files` CRUD module:

- `output/app/Controllers/<Controller>.php`
- `output/app/Routes/<Controller>.php`
- `output/app/Views/modules/master_files/<route_slug>/...`
- `output/app/Views/modules/master_files/metadata.php` patch copy

Prompt values: `moduleName`, `routeSlug`, `tableName`, `primaryKey`, `mainField`,
`iconClass`

### `nomination management` (PHP / CodeIgniter 4)

```bash
npm run generate -- "nomination management"
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

## Adding a stack

Current generators are PHP-specific and defined in `plopfile.ts`. Before adding a
second stack, the PHP-only helpers and paths should move out of the shared scope
into a per-stack generator folder — `tsconfig.json` already includes
`generators/**/*.ts` for this. See `AGENTS.md` for the intended layout.
