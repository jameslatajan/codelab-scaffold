import type { GeneratorDefinition } from '../../src/core/registry';

const generator: GeneratorDefinition = {
  name: 'sample:module',
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
};

export default generator;
