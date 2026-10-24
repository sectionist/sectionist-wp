import type { FieldDef, Preview } from '../types';
import { ButtonGroup } from './ButtonGroup';
import { CtaField } from './CtaField';
import { Field, Select, TextInput } from './Field';
import { ImageField } from './ImageField';
import { MediaField } from './MediaField';
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
  if (field.kind === 'media') {
    return (
      <Field label={field.label}>
        <MediaField value={props.value} labels={props.labels} onInput={onInput} />
      </Field>
    );
  }
  if (field.kind === 'image') {
    return (
      <Field label={field.label}>
        <ImageField value={Number(props.value) || 0} labels={props.labels} onInput={onInput} />
      </Field>
    );
  }
  const value = String(props.value ?? '');
  return (
    <Field id={field.kind === 'choice' ? undefined : id} label={field.label}>
      {field.kind === 'richtext' ? (
        <RichTextEditor id={id} value={value} labels={props.labels} onInput={onInput} />
      ) : field.kind === 'choice' ? (
        <ButtonGroup options={field.options} value={value} label={field.label} onInput={onInput} />
      ) : field.kind === 'select' ? (
        <Select id={id} value={value} options={field.options} onInput={onInput} />
      ) : (
        <TextInput id={id} value={value} onInput={onInput} />
      )}
    </Field>
  );
}
