import fs from 'node:fs';
import { requireValue } from '../../src/core/strings';
import { ensureOutputCopy } from '../../src/core/fs';
import type { GeneratorDefinition } from '../../src/core/registry';
import type { BasicManagementAnswers, BasicManagementTemplateData } from './types';
import {
  MASTER_FILES_METADATA_OUTPUT_PATH,
  MASTER_FILES_METADATA_SOURCE_PATH
} from './paths';
import {
  buildControllerClass,
  buildFieldLabel,
  normalizeIdentifier,
  normalizeModuleName,
  normalizeRouteSlug
} from './normalize';

const buildTemplateData = (answers: BasicManagementAnswers): BasicManagementTemplateData => {
  const moduleName = normalizeModuleName(answers.moduleName);
  const routeSlug = normalizeRouteSlug(answers.routeSlug);
  const tableName = normalizeRouteSlug(answers.tableName);
  const primaryKey = normalizeIdentifier(answers.primaryKey);
  const mainField = normalizeIdentifier(answers.mainField);
  const iconClass = answers.iconClass.trim();

  return {
    moduleName,
    routeSlug,
    tableName,
    primaryKey,
    mainField,
    iconClass,
    controllerClass: buildControllerClass(routeSlug),
    fieldLabel: buildFieldLabel(mainField)
  };
};

const buildMetadataSubmoduleLine = (data: BasicManagementTemplateData, eol: string): string =>
  `$subModules['${data.routeSlug}'] = '${data.moduleName}';${eol}`;

const buildMetadataBlock = (data: BasicManagementTemplateData, eol: string): string =>
  [
    '/* ****START SUB MENU***** */',
    `$sub1[$subModules['${data.routeSlug}']]['system']      = $systemName;`,
    `$sub1[$subModules['${data.routeSlug}']]['main_module'] = $module['main']['title'];`,
    `$sub1[$subModules['${data.routeSlug}']]['menu_level1'] = $subModules['${data.routeSlug}'];`,
    `$sub1[$subModules['${data.routeSlug}']]['url']         = site_url('${data.routeSlug}');`,
    `$sub1[$subModules['${data.routeSlug}']]['icon']        = '${data.iconClass}';`,
    `$sub1[$subModules['${data.routeSlug}']]['subitem']     = array();`,
    `$sub1[$subModules['${data.routeSlug}']]['type']        = 'level1';`,
    '',
    '/* ',
    '\t* Building menu ',
    '*/',
    `$module['sub'][$subModules['${data.routeSlug}']] = array(`,
    "\t'system'       => $systemName,",
    "\t'sub_level2'   => '',",
    `\t'sub_level1'   => $sub1[$subModules['${data.routeSlug}']]['menu_level1'],`,
    "\t'module_label' => $module['main']['title'],",
    `\t'menu_label'   => $subModules['${data.routeSlug}'],`,
    `\t'description'  => 'Manage All ' . $subModules['${data.routeSlug}'],`,
    `\t'icon'         => '${data.iconClass}',`,
    "\t'roles'        => array(",
    `\t\t$systemName . ' View ' . $subModules['${data.routeSlug}'],`,
    `\t\t$systemName . ' Create ' . $subModules['${data.routeSlug}'],`,
    `\t\t$systemName . ' Edit ' . $subModules['${data.routeSlug}'],`,
    `\t\t$systemName . ' Export ' . $subModules['${data.routeSlug}'],`,
    '\t)',
    ');',
    '/* ****END SUB MENU***** */',
    ''
  ].join(eol);

const patchMasterFilesMetadata = (answers?: Record<string, any>): string => {
  if (!answers) {
    throw new Error('Missing generator answers for metadata patch.');
  }

  const data = buildTemplateData(answers as BasicManagementAnswers);
  const metadataContents = ensureOutputCopy(
    MASTER_FILES_METADATA_SOURCE_PATH,
    MASTER_FILES_METADATA_OUTPUT_PATH
  );

  if (metadataContents.includes(`$subModules['${data.routeSlug}']`)) {
    return `Skipped metadata update for ${data.moduleName}; route slug already exists.`;
  }

  const eol = metadataContents.includes('\r\n') ? '\r\n' : '\n';
  const submoduleLine = buildMetadataSubmoduleLine(data, eol);
  const metadataBlock = buildMetadataBlock(data, eol);

  const submoduleMarker = /(\$subModules\[[^\n]+\]\s*=\s*'[^']+';\r?\n)(\r?\n\/\* \*\*\*\*START SUB MENU\*\*\*\* \*\/)/;
  if (!submoduleMarker.test(metadataContents)) {
    throw new Error('Unable to find the submodule list insertion point in master_files metadata.');
  }

  const withSubmoduleLine = metadataContents.replace(
    submoduleMarker,
    `$1${submoduleLine}$2`
  );

  const checkRolesMarker = /\/\*\s*\r?\n\s*\* CHECK ROLES\s*\r?\n\*\//;
  if (!checkRolesMarker.test(withSubmoduleLine)) {
    throw new Error('Unable to find the CHECK ROLES section in master_files metadata.');
  }

  const updatedContents = withSubmoduleLine.replace(
    checkRolesMarker,
    `${metadataBlock}${eol}$&`
  );

  fs.writeFileSync(MASTER_FILES_METADATA_OUTPUT_PATH, updatedContents, 'utf8');

  return `Created output/app/Views/modules/master_files/metadata.php for ${data.moduleName}.`;
};

const buildBasicManagementActions = (answers?: Record<string, any>) => {
  if (!answers) {
    return [];
  }

  const data = buildTemplateData(answers as BasicManagementAnswers);
  const moduleViewBasePath = `output/app/Views/modules/master_files/${data.routeSlug}`;

  return [
    {
      type: 'add',
      path: `output/app/Controllers/${data.controllerClass}.php`,
      templateFile: 'templates/basic-management/controller.php.hbs',
      data
    },
    {
      type: 'add',
      path: `output/app/Routes/${data.controllerClass}.php`,
      templateFile: 'templates/basic-management/route.php.hbs',
      data
    },
    {
      type: 'add',
      path: `${moduleViewBasePath}/show.php`,
      templateFile: 'templates/basic-management/show.php.hbs',
      data
    },
    {
      type: 'add',
      path: `${moduleViewBasePath}/create.php`,
      templateFile: 'templates/basic-management/create.php.hbs',
      data
    },
    {
      type: 'add',
      path: `${moduleViewBasePath}/edit.php`,
      templateFile: 'templates/basic-management/edit.php.hbs',
      data
    },
    {
      type: 'add',
      path: `${moduleViewBasePath}/view.php`,
      templateFile: 'templates/basic-management/view.php.hbs',
      data
    },
    {
      type: 'add',
      path: `${moduleViewBasePath}/printlist.php`,
      templateFile: 'templates/basic-management/printlist.php.hbs',
      data
    },
    patchMasterFilesMetadata
  ];
};

const generator: GeneratorDefinition = {
  name: 'php:crud',
description: 'Scaffold a Master Files CRUD module based on the Skills reference',
prompts: [
    {
      type: 'input',
      name: 'moduleName',
      message: 'Module name (example: Categories):',
      validate: (value: string) => requireValue('Module name', value)
    },
    {
      type: 'input',
      name: 'routeSlug',
      message: 'Route slug (example: categories):',
      validate: (value: string) => requireValue('Route slug', value)
    },
    {
      type: 'input',
      name: 'tableName',
      message: 'Table name suffix without PREFIX (example: categories):',
      validate: (value: string) => requireValue('Table name', value)
    },
    {
      type: 'input',
      name: 'primaryKey',
      message: 'Primary key field (example: categoryID):',
      validate: (value: string) => requireValue('Primary key', value)
    },
    {
      type: 'input',
      name: 'mainField',
      message: 'Main text field (example: category):',
      validate: (value: string) => requireValue('Main field', value)
    },
    {
      type: 'input',
      name: 'iconClass',
      message: 'Icon class (example: fas fa-chart-line):',
      default: 'fas fa-chart-line',
      validate: (value: string) => requireValue('Icon class', value)
    }
  ],
  actions: buildBasicManagementActions
};

export default generator;
