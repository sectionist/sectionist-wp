import type { FieldDef } from '../types';
import { RichTextEditor } from './richtext/RichTextEditor';

export function FieldInput(props: {
  id: string;
  field: FieldDef;
  value: string;
  labels: Record<string, string>;
  onInput: (v: string) => void;
}) {
  const { id, field, value, onInput } = props;
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
