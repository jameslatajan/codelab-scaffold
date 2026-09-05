import path from 'node:path';

/**
 * Repository root. Every generator resolves paths from here rather than from
 * its own __dirname, so moving a generator between folders cannot change where
 * its templates are read from or its output is written to.
 */
export const REPO_ROOT = path.resolve(__dirname, '..', '..');

export const TEMPLATES_DIR = path.join(REPO_ROOT, 'templates');

/** Default destination for generated files. */
export const DEFAULT_OUTPUT_DIR = path.join(REPO_ROOT, 'output');
