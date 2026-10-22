import type { ComponentChildren } from 'preact';

/** Labelled form row. Without `id` the label is a plain caption (for composite controls). */
export function Field(props: { label: string; id?: string; children: ComponentChildren }) {
  return (
    <div class="sectionist-field">
      {props.id ? <label for={props.id}>{props.label}</label> : <span class="sectionist-field__label">{props.label}</span>}
      {props.children}
    </div>
  );
}

export function TextInput(props: { id: string; value: string; placeholder?: string; onInput: (v: string) => void }) {
  return <input id={props.id} type="text" value={props.value} placeholder={props.placeholder} onInput={(e) => props.onInput(e.currentTarget.value)} />;
}

export function Select(props: { id: string; value: string; options: { value: string; label: string }[]; onInput: (v: string) => void }) {
  return (
    <select id={props.id} value={props.value} onChange={(e) => props.onInput(e.currentTarget.value)}>
      {props.options.map((o) => (
        <option value={o.value}>{o.label}</option>
      ))}
    </select>
  );
}

export function Checkbox(props: { checked: boolean; label: string; class?: string; onInput: (v: boolean) => void }) {
  return (
    <label class={props.class}>
      <input type="checkbox" checked={props.checked} onChange={(e) => props.onInput(e.currentTarget.checked)} /> {props.label}
    </label>
  );
}
