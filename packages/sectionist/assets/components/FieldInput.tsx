import type { FieldDef, Preview } from '../types';
import { CtaField } from './CtaField';
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
      <div class="sectionist-field">
        <span class="sectionist-field__label">{field.label}</span>
        <CtaField id={id} value={props.value} rest={props.rest} labels={props.labels} onInput={onInput} />
      </div>
    );
  }
  const value = String(props.value ?? '');
  return (
    <div class="sectionist-field">
      <label for={id}>{field.label}</label>
      {field.kind === 'richtext' ? (
        <RichTextEditor id={id} value={value} labels={props.labels} onInput={onInput} />
      ) : field.kind === 'select' ? (
        <select id={id} value={value} onChange={(e) => onInput(e.currentTarget.value)}>
          {field.options.map((o) => (
            <option value={o}>{o}</option>
          ))}
        </select>
      ) : (
        <input id={id} type="text" value={value} onInput={(e) => onInput(e.currentTarget.value)} />
      )}
    </div>
  );
}
