import type { FieldDef } from '../types';

export function FieldInput(props: { id: string; field: FieldDef; value: string; onInput: (v: string) => void }) {
  const { id, field, value, onInput } = props;
  return (
    <div class="sectionist-field">
      <label for={id}>{field.label}</label>
      {field.kind === 'textarea' ? (
        <textarea id={id} rows={8} value={value} onInput={(e) => onInput(e.currentTarget.value)} />
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
