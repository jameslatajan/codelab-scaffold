import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { after, before, describe, it } from 'node:test';

import { REPO_ROOT } from '../src/core/paths';
import { GENERATOR_CASES } from './cases';
import { listFilesRecursively, readTree, runGenerator } from './helpers';

const EXPECTED_DIR = path.join(REPO_ROOT, 'tests', 'fixtures', 'expected');
const OUTPUT_DIR = path.join(REPO_ROOT, 'output');

/**
 * Set UPDATE_SNAPSHOTS=1 to rewrite the committed fixtures from actual output.
 * Review the resulting diff before committing it.
 */
const UPDATE = process.env.UPDATE_SNAPSHOTS === '1';

/**
 * Generators write through REPO_ROOT-relative paths rather than a per-run
 * destination, so output/ is the only place they can currently emit. It is
 * git-ignored and cleared before each run.
 */
before(() => {
  fs.rmSync(OUTPUT_DIR, { recursive: true, force: true });
});

after(() => {
  if (!process.env.KEEP_OUTPUT) {
    fs.rmSync(OUTPUT_DIR, { recursive: true, force: true });
  }
});

describe('generator snapshots', () => {
  for (const testCase of GENERATOR_CASES) {
    describe(testCase.name, () => {
      const expectedDir = path.join(EXPECTED_DIR, testCase.slug);
      let actual: Map<string, string>;
      let failures: string[];

      before(async () => {
        fs.rmSync(OUTPUT_DIR, { recursive: true, force: true });
        const result = await runGenerator(testCase);
        failures = result.failures;
        actual = readTree(OUTPUT_DIR);

        if (UPDATE) {
          fs.rmSync(expectedDir, { recursive: true, force: true });
          for (const [relativePath, contents] of actual) {
            const target = path.join(expectedDir, relativePath);
            fs.mkdirSync(path.dirname(target), { recursive: true });
            fs.writeFileSync(target, contents, 'utf8');
          }
        }
      });

      it('completes every action without failures', () => {
        assert.deepEqual(failures, [], `actions failed:\n${failures.join('\n')}`);
      });

      it('produces exactly the expected set of files', () => {
        const expected = listFilesRecursively(expectedDir).sort();
        assert.deepEqual([...actual.keys()].sort(), expected);
      });

      it('produces byte-identical file contents', () => {
        for (const [relativePath, contents] of actual) {
          const expectedPath = path.join(expectedDir, relativePath);
          const expectedContents = fs.readFileSync(expectedPath, 'utf8');
          assert.equal(
            contents,
            expectedContents,
            `${testCase.slug}/${relativePath} differs from its snapshot`
          );
        }
      });
    });
  }
});
