import fs from 'node:fs';
import path from 'node:path';

export const ensureOutputCopy = (sourcePath: string, outputPath: string): string => {
  if (!fs.existsSync(outputPath)) {
    fs.mkdirSync(path.dirname(outputPath), { recursive: true });
    fs.copyFileSync(sourcePath, outputPath);
  }

  return fs.readFileSync(outputPath, 'utf8');
};
