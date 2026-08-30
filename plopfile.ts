import fs from 'node:fs';
import path from 'node:path';
import type { NodePlopAPI } from 'plop';

type BasicManagementAnswers = {
  moduleName: string;
  routeSlug: string;
  tableName: string;
  primaryKey: string;
  mainField: string;
  iconClass: string;
};

type BasicManagementTemplateData = BasicManagementAnswers & {
  controllerClass: string;
  fieldLabel: string;
};

type NominationManagementAnswers = {
  moduleName: string;
  routeSlug: string;
  tableName: string;
  primaryKey: string;
  controllerClass: string;
  iconClass: string;
  uploadDir: string;
};

type NominationManagementTemplateData = NominationManagementAnswers & {
  metadataKey: string;
};

const APP_ROOT_DIR = path.resolve(__dirname, '..');
const OUTPUT_DIR = path.join(__dirname, 'output');
const OUTPUT_APP_DIR = path.join(OUTPUT_DIR, 'app');
const MASTER_FILES_METADATA_SOURCE_PATH = path.join(
  APP_ROOT_DIR,
  'app',
  'Views',
  'modules',
  'master_files',
  'metadata.php'
);
const TRAININGS_METADATA_SOURCE_PATH = path.join(
  APP_ROOT_DIR,
  'app',
  'Views',
  'modules',
  'trainings',
  'metadata.php'
);
const MASTER_FILES_METADATA_OUTPUT_PATH = path.join(
  OUTPUT_APP_DIR,
  'Views',
  'modules',
  'master_files',
  'metadata.php'
);
const TRAININGS_METADATA_OUTPUT_PATH = path.join(
  OUTPUT_APP_DIR,
  'Views',
  'modules',
  'trainings',
  'metadata.php'
);

const requireValue = (label: string, value: string): true | string =>
  value.trim() !== '' || `${label} is required.`;

const normalizeModuleName = (value: string): string =>
  value
    .trim()
    .replace(/\s+/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase());

const normalizeRouteSlug = (value: string): string =>
  value
    .trim()
    .toLowerCase()
    .replace(/[\s-]+/g, '_')
    .replace(/[^a-z0-9_]/g, '');

const normalizeIdentifier = (value: string): string =>
  value.trim().replace(/\s+/g, '');

const normalizeControllerClass = (value: string): string =>
  value
    .trim()
    .replace(/\s+/g, '_')
    .replace(/[^A-Za-z0-9_]/g, '')
    .replace(/^[a-z]/, (character) => character.toUpperCase());

const normalizeUploadDir = (value: string): string => {
  const normalized = value.trim().replace(/\\/g, '/').replace(/^\/+/, '');

  if (normalized === '') {
    return 'uploads/activity_design/';
  }

  return normalized.endsWith('/') ? normalized : `${normalized}/`;
};

const buildControllerClass = (routeSlug: string): string =>
  routeSlug.charAt(0).toUpperCase() + routeSlug.slice(1);

const buildFieldLabel = (fieldName: string): string =>
  fieldName
    .trim()
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase());

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

const replaceLiteral = (contents: string, search: string, replace: string): string =>
  contents.split(search).join(replace);

const ensureOutputCopy = (sourcePath: string, outputPath: string): string => {
  if (!fs.existsSync(outputPath)) {
    fs.mkdirSync(path.dirname(outputPath), { recursive: true });
    fs.copyFileSync(sourcePath, outputPath);
  }

  return fs.readFileSync(outputPath, 'utf8');
};

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
  const sourcePath = path.join(
    __dirname,
    'templates',
    'nomination-management',
    sourceFileName
  );
  const absoluteDestinationPath = path.resolve(__dirname, destinationPath);
  const contents = fs.readFileSync(sourcePath, 'utf8');
  const transformed = transformNominationTemplate(contents, sourceFileName, data);

  fs.mkdirSync(path.dirname(absoluteDestinationPath), { recursive: true });
  fs.writeFileSync(absoluteDestinationPath, transformed, 'utf8');

  return `Created ${destinationPath}`;
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

const configurePlop = (plop: NodePlopAPI): void => {
  plop.setGenerator('module', {
    description: 'Create a TypeScript module scaffold',
    prompts: [
      {
        type: 'input',
        name: 'name',
        message: 'Module name:',
        validate: (value: string) => value.trim() !== '' || 'Module name is required.'
      }
    ],
    actions: [
      {
        type: 'add',
        path: 'output/{{kebabCase name}}/{{pascalCase name}}.ts',
        templateFile: 'templates/module/module.ts.hbs'
      },
      {
        type: 'add',
        path: 'output/{{kebabCase name}}/index.ts',
        templateFile: 'templates/module/index.ts.hbs'
      }
    ]
  });

  plop.setGenerator('basic management', {
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
  });

  plop.setGenerator('nomination management', {
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
  });
};

export default configurePlop;
