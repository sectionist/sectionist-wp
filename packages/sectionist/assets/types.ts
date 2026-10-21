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
  active?: boolean;
  background?: string;
  data: Record<string, unknown>;
  [key: string]: unknown;
}

export interface Background {
  key: string;
  label: string;
  color: string;
}

export interface Preview {
  url: string;
  nonce: string;
  postId: number;
}

export interface Config {
  preview: Preview;
  types: TypeDef[];
  backgrounds: Background[];
  sections: Section[];
  i18n: Record<string, string>;
}
