import type { NodePlopAPI } from 'plop';
import { registerGenerators } from './src/core/registry';

/**
 * codelab-scaffold entry point.
 *
 * Generators live in generators/<stack>/<name>.ts and are discovered
 * automatically; this file intentionally contains no generator logic.
 */
const configurePlop = (plop: NodePlopAPI): void => {
  registerGenerators(plop);
};

export default configurePlop;
