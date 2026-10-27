export interface FieldDef {
  kind: 'text' | 'url' | 'richtext' | 'select' | 'choice' | 'cta' | 'image' | 'media' | 'list' | 'faq' | 'gallery' | 'shortcodes';
  name: string;
  label: string;
  options: { value: string; label: string }[];
  showIf: { field: string; value: string } | null;
  required?: boolean;
  panel: boolean;
}

export interface TypeDef {
  name: string;
  label: string;
  description: string;
  icon: string;
  defaults: Record<string, unknown>;
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
}

export interface Preview {
  url: string;
  nonce: string;
  postId: number;
  pagesUrl: string;
  faqUrl: string;
  topicsUrl: string;
}

export interface Config {
  preview: Preview;
  types: TypeDef[];
  allowedTypes: string[];
  backgrounds: Background[];
  sections: Section[];
  i18n: Record<string, string>;
}
