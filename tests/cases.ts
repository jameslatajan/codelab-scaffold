/**
 * Fixed answer sets driving the snapshot tests. Adding a generator means adding
 * a case here, then running `npm run test:update` to record its fixtures.
 */
export type GeneratorCase = {
  /** Registered generator name, e.g. 'php:crud'. */
  name: string;
  /** Directory name under tests/fixtures/expected/. */
  slug: string;
  answers: Record<string, string>;
};

export const GENERATOR_CASES: GeneratorCase[] = [
  {
    name: 'php:crud',
    slug: 'php-crud',
    answers: {
      moduleName: 'Cloud Attachments',
      routeSlug: 'cloud_attachments',
      tableName: 'cloud_attachments',
      primaryKey: 'id',
      mainField: 'name',
      iconClass: 'fa fa-paperclip'
    }
  },
  {
    name: 'php:nomination',
    slug: 'php-nomination',
    answers: {
      moduleName: 'Activity Design',
      routeSlug: 'activity_designs',
      tableName: 'activity_designs',
      primaryKey: 'id',
      controllerClass: 'Activity_designs',
      iconClass: 'fa fa-file',
      uploadDir: 'uploads/activity_design/'
    }
  },
  {
    name: 'sample:module',
    slug: 'sample-module',
    answers: {
      name: 'sample widget'
    }
  }
];
