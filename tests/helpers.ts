import fs from 'node:fs';
import path from 'node:path';

import { REPO_ROOT } from '../src/core/paths';
import type { GeneratorCase } from './cases';

/** Relative POSIX-style paths of every file under `directory`. */
export const listFilesRecursively = (directory: string): string[] => {
  if (!fs.existsSync(directory)) {
    return [];
  }

  return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const entryPath = path.join(directory, entry.name);

    if (entry.isDirectory()) {
      return listFilesRecursively(entryPath).map((child) => `${entry.name}/${child}`);
    }

    return [entry.name];
  });
};

/** Map of relative path -> file contents, for whole-tree comparison. */
export const readTree = (directory: string): Map<string, string> => {
  const tree = new Map<string, string>();

  for (const relativePath of listFilesRecursively(directory)) {
    tree.set(relativePath, fs.readFileSync(path.join(directory, relativePath), 'utf8'));
  }

  return tree;
};

export type GeneratorRun = {
  changes: number;
  failures: string[];
};

/**
 * Run one generator with fixed answers, bypassing prompts.
 *
 * CI4_APP_ROOT is pointed at the committed fixture app so the metadata-patch
 * actions have a real file to read; without it they fail with ENOENT.
 */
export const runGenerator = async (testCase: GeneratorCase): Promise<GeneratorRun> => {
  process.env.CI4_APP_ROOT = path.join(REPO_ROOT, 'tests', 'fixtures', 'ci4-app');

  const { default: nodePlop } = await import('node-plop');
  const plop = await nodePlop(path.join(REPO_ROOT, 'plopfile.js'), {
    destBasePath: REPO_ROOT,
    force: false
  });

  const generator = plop.getGenerator(testCase.name);
  const result = await generator.runActions(testCase.answers);

  return {
    changes: result.changes.length,
    failures: result.failures.map(
      (failure: any) =>
        `${failure.path ?? failure.type}: ${failure.error?.stack ?? failure.error ?? JSON.stringify(failure)}`
    )
  };
};
