/** Stack-agnostic string helpers shared by all generators. */

export const requireValue = (label: string, value: string): true | string =>
  value.trim() !== '' || `${label} is required.`;

export const replaceLiteral = (contents: string, search: string, replace: string): string =>
  contents.split(search).join(replace);
