import { render } from 'preact';
import { useEffect, useReducer, useState } from 'preact/hooks';
import { reducer } from './state';
import type { Config, FieldDef, Section, TypeDef } from './types';
import './style.css';

function FieldInput(props: { field: FieldDef; value: string; onInput: (v: string) => void }) {
  const { field, value, onInput } = props;
  const id = `sectionist-${field.name}-${Math.random().toString(36).slice(2, 7)}`;
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

function App({ config }: { config: Config }) {
  const [sections, dispatch] = useReducer(reducer, config.sections);
  const [error, setError] = useState('');
  const [toAdd, setToAdd] = useState(config.types[0]?.name ?? '');
  const t = config.i18n;
  const typeOf = (name: string): TypeDef | undefined => config.types.find((x) => x.name === name);

  useEffect(() => {
    const input = document.getElementById('sectionist-data') as HTMLInputElement | null;
    if (!input) return;
    try {
      input.value = JSON.stringify({ sections });
      setError('');
    } catch {
      setError(t.saveError);
    }
  }, [sections]);

  const renderSection = (section: Section, index: number) => {
    const type = typeOf(section.type);
    return (
      <div class="sectionist-section" key={section.id}>
        <div class="sectionist-section__bar">
          <strong>{type?.label ?? section.type}</strong>
          <span>
            <button type="button" class="button" disabled={index === 0} onClick={() => dispatch({ kind: 'move', id: section.id, offset: -1 })}>
              {t.moveUp}
            </button>{' '}
            <button type="button" class="button" disabled={index === sections.length - 1} onClick={() => dispatch({ kind: 'move', id: section.id, offset: 1 })}>
              {t.moveDown}
            </button>{' '}
            <button type="button" class="button" onClick={() => dispatch({ kind: 'remove', id: section.id })}>
              {t.remove}
            </button>
          </span>
        </div>
        {type ? (
          type.fields.map((field) => (
            <FieldInput
              field={field}
              value={String(section.data[field.name] ?? '')}
              onInput={(value) => dispatch({ kind: 'update', id: section.id, field: field.name, value })}
            />
          ))
        ) : (
          <p class="sectionist-note">{t.unknownType}</p>
        )}
      </div>
    );
  };

  return (
    <div class="sectionist">
      {error && <div class="notice notice-error inline"><p>{error}</p></div>}
      {sections.length === 0 && <p class="sectionist-note">{t.empty}</p>}
      {sections.map(renderSection)}
      <div class="sectionist-add">
        <select value={toAdd} onChange={(e) => setToAdd(e.currentTarget.value)}>
          {config.types.map((type) => (
            <option value={type.name}>{type.label}</option>
          ))}
        </select>{' '}
        <button
          type="button"
          class="button button-primary"
          onClick={() => {
            const type = typeOf(toAdd);
            if (type) dispatch({ kind: 'add', type });
          }}
        >
          {t.add}
        </button>
      </div>
    </div>
  );
}

const root = document.getElementById('sectionist-editor');
if (root) {
  render(<App config={JSON.parse(root.dataset.config ?? '{}') as Config} />, root);
}
