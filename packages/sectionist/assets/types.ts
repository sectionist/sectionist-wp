export interface FieldDef {
  kind: 'text' | 'textarea' | 'select';
  name: string;
  label: string;
  options: string[];
}

export interface TypeDef {
  name: string;
  label: string;
  description: string;
  icon: string;
  defaults: Record<string, string>;
  fields: FieldDef[];
}

export interface Section {
  id: string;
  type: string;
  data: Record<string, unknown>;
  [key: string]: unknown;
}

export interface Config {
  types: TypeDef[];
  sections: Section[];
  i18n: Record<string, string>;
}
