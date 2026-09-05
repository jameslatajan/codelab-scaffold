import fs from 'node:fs';
import path from 'node:path';
import { replaceLiteral, requireValue } from '../../src/core/strings';
import { ensureOutputCopy } from '../../src/core/fs';
import { REPO_ROOT, TEMPLATES_DIR } from '../../src/core/paths';
import type { GeneratorDefinition } from '../../src/core/registry';
import type {
  NominationManagementAnswers,
  NominationManagementTemplateData
} from './types';
import {
  TRAININGS_METADATA_OUTPUT_PATH,
  TRAININGS_METADATA_SOURCE_PATH
} from './paths';
import {
  buildControllerClass,
  buildFieldLabel,
  normalizeControllerClass,
  normalizeIdentifier,
  normalizeModuleName,
  normalizeRouteSlug,
  normalizeUploadDir
} from './normalize';

const buildNominationTemplateData = (
  answers: NominationManagementAnswers
): NominationManagementTemplateData => {
  const moduleName = normalizeModuleName(answers.moduleName);
  const routeSlug = normalizeRouteSlug(answers.routeSlug);
  const tableName = normalizeRouteSlug(answers.tableName);
  const primaryKey = normalizeIdentifier(answers.primaryKey);
  const controllerClass = normalizeControllerClass(
    answers.controllerClass || buildControllerClass(routeSlug)
  );
  const iconClass = answers.iconClass.trim();
  const uploadDir = normalizeUploadDir(answers.uploadDir);

  return {
    moduleName,
    routeSlug,
    tableName,
    primaryKey,
    controllerClass,
    iconClass,
    uploadDir,
    metadataKey: moduleName
  };
};

const buildTrainingsMetadataSubmoduleLine = (
  data: NominationManagementTemplateData,
  eol: string
): string => `$subModules['${data.metadataKey}'] = '${data.moduleName}';${eol}`;

const buildTrainingsMetadataBlock = (
  data: NominationManagementTemplateData,
  eol: string
): string =>
  [
    '/* ****START SUB MENU***** */',
    `$sub1[$subModules['${data.metadataKey}']]['system']      = $systemName;`,
    `$sub1[$subModules['${data.metadataKey}']]['main_module'] = $module['main']['title'];`,
    `$sub1[$subModules['${data.metadataKey}']]['menu_level1'] = $subModules['${data.metadataKey}'];`,
    `$sub1[$subModules['${data.metadataKey}']]['url']         = site_url('${data.routeSlug}');`,
    `$sub1[$subModules['${data.metadataKey}']]['icon']        = '${data.iconClass}';`,
    `$sub1[$subModules['${data.metadataKey}']]['subitem']     = array();`,
    `$sub1[$subModules['${data.metadataKey}']]['type']        = 'level1';`,
    '',
    '/* ',
    '\t* Building menu ',
    '*/',
    `$module['sub'][$subModules['${data.metadataKey}']] = array(`,
    "\t'system'       => $systemName,",
    "\t'sub_level2'   => '',",
    `\t'sub_level1'   => $sub1[$subModules['${data.metadataKey}']]['menu_level1'],`,
    "\t'module_label' => $module['main']['title'],",
    `\t'menu_label'   => $subModules['${data.metadataKey}'],`,
    `\t'description'  => 'Manage All ' . $subModules['${data.metadataKey}'],`,
    `\t'icon'         => '${data.iconClass}',`,
    "\t'roles'        => array(",
    `\t\t$systemName . ' View ' . $subModules['${data.metadataKey}'],`,
    `\t\t$systemName . ' Create ' . $subModules['${data.metadataKey}'],`,
    `\t\t$systemName . ' Edit ' . $subModules['${data.metadataKey}'],`,
    `\t\t$systemName . ' Review ' . $subModules['${data.metadataKey}'],`,
    `\t\t$systemName . ' Approve ' . $subModules['${data.metadataKey}'],`,
    `\t\t$systemName . ' Cancel ' . $subModules['${data.metadataKey}'],`,
    `\t\t$systemName . ' Decline ' . $subModules['${data.metadataKey}'],`,
    `\t\t$systemName . ' Export ' . $subModules['${data.metadataKey}'],`,
    '\t)',
    ');',
    '/* ****END SUB MENU***** */',
    ''
  ].join(eol);

const patchTrainingsMetadata = (answers?: Record<string, any>): string => {
  if (!answers) {
    throw new Error('Missing generator answers for trainings metadata patch.');
  }

  const data = buildNominationTemplateData(answers as NominationManagementAnswers);
  const metadataContents = ensureOutputCopy(
    TRAININGS_METADATA_SOURCE_PATH,
    TRAININGS_METADATA_OUTPUT_PATH
  );

  if (
    metadataContents.includes(`$subModules['${data.metadataKey}']`) ||
    metadataContents.includes(`site_url('${data.routeSlug}')`)
  ) {
    return `Skipped trainings metadata update for ${data.moduleName}; module already exists.`;
  }

  const eol = metadataContents.includes('\r\n') ? '\r\n' : '\n';
  const submoduleLine = buildTrainingsMetadataSubmoduleLine(data, eol);
  const metadataBlock = buildTrainingsMetadataBlock(data, eol);

  const submoduleMarker =
    /(\$subModules\[[^\n]+\]\s*=\s*'[^']+';\r?\n)(\r?\n\/\* \*\*\*\*START SUB MENU\*\*\*\* \*\/)/;
  if (!submoduleMarker.test(metadataContents)) {
    throw new Error('Unable to find the submodule list insertion point in trainings metadata.');
  }

  const withSubmoduleLine = metadataContents.replace(
    submoduleMarker,
    `$1${submoduleLine}$2`
  );

  const checkRolesMarker = /\/\*\s*\r?\n\s*\* CHECK ROLES\s*\r?\n\*\//;
  if (!checkRolesMarker.test(withSubmoduleLine)) {
    throw new Error('Unable to find the CHECK ROLES section in trainings metadata.');
  }

  const updatedContents = withSubmoduleLine.replace(
    checkRolesMarker,
    `${metadataBlock}${eol}$&`
  );

  fs.writeFileSync(TRAININGS_METADATA_OUTPUT_PATH, updatedContents, 'utf8');

  return `Created output/app/Views/modules/trainings/metadata.php for ${data.moduleName}.`;
};

const transformNominationTemplate = (
  contents: string,
  sourceFileName: string,
  data: NominationManagementTemplateData
): string => {
  let updatedContents = contents;

  updatedContents = replaceLiteral(
    updatedContents,
    "modules/trainings/nominations/",
    `modules/trainings/${data.routeSlug}/`
  );

  if (sourceFileName === 'controller.php.hbs') {
    updatedContents = replaceLiteral(
      updatedContents,
      'class Nominations extends BaseController',
      `class ${data.controllerClass} extends BaseController`
    );
    updatedContents = replaceLiteral(
      updatedContents,
      "$this->module = 'Trainigs';",
      "$this->module = 'Trainings';"
    );
    updatedContents = replaceLiteral(
      updatedContents,
      "$this->sub = 'Nominations';",
      `$this->sub = '${data.moduleName}';`
    );
    updatedContents = replaceLiteral(
      updatedContents,
      "PREFIX . 'nominations'",
      `PREFIX . '${data.tableName}'`
    );
    updatedContents = replaceLiteral(
      updatedContents,
      "'nomID'",
      `'${data.primaryKey}'`
    );
    updatedContents = replaceLiteral(
      updatedContents,
      "site_url('nominations')",
      `site_url('${data.routeSlug}')`
    );
    updatedContents = replaceLiteral(
      updatedContents,
      "'uploads/activity_design/'",
      `'${data.uploadDir}'`
    );
  }

  if (sourceFileName === 'route.php.hbs') {
    updatedContents = replaceLiteral(
      updatedContents,
      "$routes->group('nominations'",
      `$routes->group('${data.routeSlug}'`
    );
    updatedContents = replaceLiteral(
      updatedContents,
      '$controller = "Nominations";',
      `$controller = "${data.controllerClass}";`
    );
  }

  return updatedContents;
};

const copyNominationTemplate = (
  sourceFileName: string,
  destinationPath: string,
  answers?: NominationManagementAnswers
): string => {
  if (!answers) {
    throw new Error(`Missing generator answers for ${sourceFileName}.`);
  }

  const data = buildNominationTemplateData(answers);
  const sourcePath = path.join(TEMPLATES_DIR, 'nomination-management', sourceFileName);
  const absoluteDestinationPath = path.resolve(REPO_ROOT, destinationPath);
  const contents = fs.readFileSync(sourcePath, 'utf8');
  const transformed = transformNominationTemplate(contents, sourceFileName, data);

  fs.mkdirSync(path.dirname(absoluteDestinationPath), { recursive: true });
  fs.writeFileSync(absoluteDestinationPath, transformed, 'utf8');

  return `Created ${destinationPath}`;
};

const buildNominationManagementActions = (answers?: Record<string, any>) => {
  if (!answers) {
    return [];
  }

  const data = buildNominationTemplateData(answers as NominationManagementAnswers);
  const moduleViewBasePath = `output/app/Views/modules/trainings/${data.routeSlug}`;

  return [
    () =>
      copyNominationTemplate(
        'controller.php.hbs',
        `output/app/Controllers/${data.controllerClass}.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'route.php.hbs',
        `output/app/Routes/${data.controllerClass}.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'show.php.hbs',
        `${moduleViewBasePath}/show.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'create.php.hbs',
        `${moduleViewBasePath}/create.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'edit.php.hbs',
        `${moduleViewBasePath}/edit.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'view.php.hbs',
        `${moduleViewBasePath}/view.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'printlist.php.hbs',
        `${moduleViewBasePath}/printlist.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'replace_files.php.hbs',
        `${moduleViewBasePath}/replace_files.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'multiple_div.php.hbs',
        `${moduleViewBasePath}/multiple_div.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'multiple_sec.php.hbs',
        `${moduleViewBasePath}/multiple_sec.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'section_emp.php.hbs',
        `${moduleViewBasePath}/section_emp.php`,
        answers as NominationManagementAnswers
      ),
    () =>
      copyNominationTemplate(
        'individual_emp.php.hbs',
        `${moduleViewBasePath}/individual_emp.php`,
        answers as NominationManagementAnswers
      ),
    patchTrainingsMetadata
  ];
};

const generator: GeneratorDefinition = {
  name: 'php:nomination',
description: 'Scaffold a Trainings workflow module based on the Nominations reference',
prompts: [
    {
      type: 'input',
      name: 'moduleName',
      message: 'Module name (example: Special Nominations):',
      validate: (value: string) => requireValue('Module name', value)
    },
    {
      type: 'input',
      name: 'routeSlug',
      message: 'Route slug (example: special_nominations):',
      validate: (value: string) => requireValue('Route slug', value)
    },
    {
      type: 'input',
      name: 'tableName',
      message: 'Table name suffix without PREFIX (example: nominations):',
      default: 'nominations',
      validate: (value: string) => requireValue('Table name', value)
    },
    {
      type: 'input',
      name: 'primaryKey',
      message: 'Primary key field (example: nomID):',
      default: 'nomID',
      validate: (value: string) => requireValue('Primary key', value)
    },
    {
      type: 'input',
      name: 'controllerClass',
      message: 'Controller class (leave default to derive from route slug):',
      default: (answers: { routeSlug?: string }) =>
        buildControllerClass(normalizeRouteSlug(answers.routeSlug ?? 'nominations')),
      validate: (value: string) => requireValue('Controller class', value)
    },
    {
      type: 'input',
      name: 'iconClass',
      message: 'Icon class (example: fas fa-newspaper):',
      default: 'fas fa-newspaper',
      validate: (value: string) => requireValue('Icon class', value)
    },
    {
      type: 'input',
      name: 'uploadDir',
      message: 'Upload directory (example: uploads/activity_design/):',
      default: 'uploads/activity_design/',
      validate: (value: string) => requireValue('Upload directory', value)
    }
  ],
  actions: buildNominationManagementActions
};

export default generator;
