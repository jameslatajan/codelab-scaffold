/** Answer and template-data shapes for the PHP / CodeIgniter 4 generators. */

export type BasicManagementAnswers = {
  moduleName: string;
  routeSlug: string;
  tableName: string;
  primaryKey: string;
  mainField: string;
  iconClass: string;
};

export type BasicManagementTemplateData = BasicManagementAnswers & {
  controllerClass: string;
  fieldLabel: string;
};

export type NominationManagementAnswers = {
  moduleName: string;
  routeSlug: string;
  tableName: string;
  primaryKey: string;
  controllerClass: string;
  iconClass: string;
  uploadDir: string;
};

export type NominationManagementTemplateData = NominationManagementAnswers & {
  metadataKey: string;
};
