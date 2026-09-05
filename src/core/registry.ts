import fs from 'node:fs';
import path from 'node:path';
import type { NodePlopAPI } from 'plop';
import { REPO_ROOT } from './paths';

/**
 * Shape every generator module must default-export. Adding a stack means adding
 * a folder under generators/ — never editing shared code.
 */
export type GeneratorDefinition = {
  /** Namespaced name, e.g. 'php:crud'. */
  name: string;
  description: string;
  prompts: any[];
  actions: any;
};

const GENERATORS_DIR = path.join(REPO_ROOT, 'generators');

/** Recursively collect generator modules, skipping shared files. */
const collectGeneratorFiles = (directory: string): string[] => {
  if (!fs.existsSync(directory)) {
    return [];
  }

  const shared = new Set(['types', 'paths', 'normalize', 'index']);

  return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const entryPath = path.join(directory, entry.name);

    if (entry.isDirectory()) {
      return collectGeneratorFiles(entryPath);
    }

    if (!entry.name.endsWith('.ts') || entry.name.endsWith('.d.ts')) {
      return [];
    }

    if (shared.has(path.basename(entry.name, '.ts'))) {
      return [];
    }

    return [entryPath];
  });
};

export const registerGenerators = (plop: NodePlopAPI): void => {
  const files = collectGeneratorFiles(GENERATORS_DIR).sort();

  for (const file of files) {
    const module = require(file);
    const generator: GeneratorDefinition | undefined = module.default ?? module;

    if (!generator?.name) {
      throw new Error(`Generator module ${file} has no default export with a name.`);
    }

    plop.setGenerator(generator.name, {
      description: generator.description,
      prompts: generator.prompts,
      actions: generator.actions
    });
  }
};
