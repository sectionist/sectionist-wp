import type { FieldDef, Preview } from '../types';
import { CtaField } from './CtaField';
import { Field, Select, TextInput } from './Field';
import { RichTextEditor } from './richtext/RichTextEditor';

export function FieldInput(props: {
  id: string;
  field: FieldDef;
  value: unknown;
  rest: Preview;
  labels: Record<string, string>;
  onInput: (v: unknown) => void;
}) {
  const { id, field, onInput } = props;
  if (field.kind === 'cta') {
    return (
      <Field label={field.label}>
        <CtaField id={id} value={props.value} rest={props.rest} labels={props.labels} onInput={onInput} />
      </Field>
    );
  }
  const value = String(props.value ?? '');
  return (
    <Field id={id} label={field.label}>
      {field.kind === 'richtext' ? (
        <RichTextEditor id={id} value={value} labels={props.labels} onInput={onInput} />
      ) : field.kind === 'select' ? (
        <Select id={id} value={value} options={field.options.map((o) => ({ value: o, label: o }))} onInput={onInput} />
      ) : (
        <TextInput id={id} value={value} onInput={onInput} />
      )}
    </Field>
  );
}
