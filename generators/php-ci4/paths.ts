import path from 'node:path';
import { DEFAULT_OUTPUT_DIR, REPO_ROOT } from '../../src/core/paths';

/**
 * Root of the CodeIgniter application these generators patch metadata in.
 * Historically this was hardcoded to the repository's parent directory, which
 * assumed codelab-scaffold lived inside one specific CI4 app. Override with
 * CI4_APP_ROOT until a per-run destination is supported.
 */
export const APP_ROOT_DIR = process.env.CI4_APP_ROOT
  ? path.resolve(process.env.CI4_APP_ROOT)
  : path.resolve(REPO_ROOT, '..');

export const OUTPUT_DIR = DEFAULT_OUTPUT_DIR;
export const OUTPUT_APP_DIR = path.join(OUTPUT_DIR, 'app');
export const MASTER_FILES_METADATA_SOURCE_PATH = path.join(
  APP_ROOT_DIR,
  'app',
  'Views',
  'modules',
  'master_files',
  'metadata.php'
);
export const TRAININGS_METADATA_SOURCE_PATH = path.join(
  APP_ROOT_DIR,
  'app',
  'Views',
  'modules',
  'trainings',
  'metadata.php'
);
export const MASTER_FILES_METADATA_OUTPUT_PATH = path.join(
  OUTPUT_APP_DIR,
  'Views',
  'modules',
  'master_files',
  'metadata.php'
);
export const TRAININGS_METADATA_OUTPUT_PATH = path.join(
  OUTPUT_APP_DIR,
  'Views',
  'modules',
  'trainings',
  'metadata.php'
);
