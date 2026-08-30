# Scaffold

Initial Plop setup with TypeScript support.

## Scripts

- `npm run generate`
- `npm run plop`
- `npm run typecheck`

## First-time install

```bash
npm install
```

## Available generators

### `module`

```bash
npm run generate -- module
```

This writes a sample TypeScript module to `plop/output/`.

### `basic management`

```bash
npm run generate -- "basic management"
```

This scaffolds a full `Master Files` CRUD module into `plop/output/`:

- `output/app/Controllers/<Controller>.php`
- `output/app/Routes/<Controller>.php`
- `output/app/Views/modules/master_files/<route_slug>/...`
- `output/app/Views/modules/master_files/metadata.php` patch copy

Prompt values:

- `moduleName`
- `routeSlug`
- `tableName`
- `primaryKey`
- `mainField`
- `iconClass`

### `nomination management`

```bash
npm run generate -- "nomination management"
```

This scaffolds a trainings workflow module into `plop/output/`:

- `output/app/Controllers/<Controller>.php`
- `output/app/Routes/<Controller>.php`
- `output/app/Views/modules/trainings/<route_slug>/...`
- `output/app/Views/modules/trainings/metadata.php` patch copy

Generated views include:

- `show.php`
- `create.php`
- `edit.php`
- `view.php`
- `printlist.php`
- `replace_files.php`
- `multiple_div.php`
- `multiple_sec.php`
- `section_emp.php`
- `individual_emp.php`

Prompt values:

- `moduleName`
- `routeSlug`
- `tableName`
- `primaryKey`
- `controllerClass`
- `iconClass`
- `uploadDir`
