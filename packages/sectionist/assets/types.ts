export interface FieldDef {
  kind: 'text' | 'richtext' | 'select';
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

export interface Preview {
  url: string;
  nonce: string;
  postId: number;
}

export interface Config {
  preview: Preview;
  types: TypeDef[];
  sections: Section[];
  i18n: Record<string, string>;
}
