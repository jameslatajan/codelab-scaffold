/** Normalization rules specific to PHP / CodeIgniter 4 naming. */

export const normalizeModuleName = (value: string): string =>
  value
    .trim()
    .replace(/\s+/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase());

export const normalizeRouteSlug = (value: string): string =>
  value
    .trim()
    .toLowerCase()
    .replace(/[\s-]+/g, '_')
    .replace(/[^a-z0-9_]/g, '');

export const normalizeIdentifier = (value: string): string =>
  value.trim().replace(/\s+/g, '');

export const normalizeControllerClass = (value: string): string =>
  value
    .trim()
    .replace(/\s+/g, '_')
    .replace(/[^A-Za-z0-9_]/g, '')
    .replace(/^[a-z]/, (character) => character.toUpperCase());

export const normalizeUploadDir = (value: string): string => {
  const normalized = value.trim().replace(/\\/g, '/').replace(/^\/+/, '');

  if (normalized === '') {
    return 'uploads/activity_design/';
  }

  return normalized.endsWith('/') ? normalized : `${normalized}/`;
};

export const buildControllerClass = (routeSlug: string): string =>
  routeSlug.charAt(0).toUpperCase() + routeSlug.slice(1);

export const buildFieldLabel = (fieldName: string): string =>
  fieldName
    .trim()
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (character) => character.toUpperCase());
