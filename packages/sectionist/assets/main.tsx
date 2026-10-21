import { render } from 'preact';
import { useEffect, useReducer, useState } from 'preact/hooks';
import { reducer } from './state';
import type { Config, FieldDef, Section, TypeDef } from './types';
import { Collapsible } from './components/Collapsible';
import { Icon, IconButton } from './components/Icon';
import { Tabs } from './components/Tabs';
import './style.css';

function sectionTitle(type: TypeDef | undefined, section: Section): string {
  for (const field of type?.fields ?? []) {
    if (field.kind === 'select') continue;
    const text = String(section.data[field.name] ?? '').replace(/<[^>]*>/g, '').trim();
    if (text) return text.length > 60 ? text.slice(0, 60) + '…' : text;
  }
  return '';
}

function FieldInput(props: { id: string; field: FieldDef; value: string; onInput: (v: string) => void }) {
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

function App({ config }: { config: Config }) {
  const [sections, dispatch] = useReducer(reducer, config.sections);
  const [error, setError] = useState('');
  const [tab, setTab] = useState('sections');
  const [closed, setClosed] = useState<Set<string>>(new Set());
  const toggle = (id: string) =>
    setClosed((prev) => {
      const next = new Set(prev);
      if (!next.delete(id)) next.add(id);
      return next;
    });
  const [dragId, setDragId] = useState('');
  const [overId, setOverId] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [removeId, setRemoveId] = useState('');
  const allClosed = sections.length > 0 && closed.size >= sections.length;
  const toggleAll = () => setClosed(allClosed ? new Set() : new Set(sections.map((s) => s.id)));
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
    const title = sectionTitle(type, section);
    return (
      <Collapsible
        key={section.id}
        open={!closed.has(section.id)}
        onToggle={() => toggle(section.id)}
        toggleLabel={t.toggle}
        title={
          <>
            <strong>{type?.label ?? section.type}</strong>
            {title && <span class="sectionist-card__title">{title}</span>}
          </>
        }
        dragLabel={t.drag}
        dragging={dragId === section.id}
        dropTarget={!!dragId && overId === section.id && dragId !== section.id}
        onDragStart={(e) => {
          setDragId(section.id);
          const card = (e.currentTarget as HTMLElement).closest('.sectionist-card');
          if (card && e.dataTransfer) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', section.id);
            e.dataTransfer.setDragImage(card, 16, 16);
          }
        }}
        onDragOver={(e) => {
          if (!dragId) return;
          e.preventDefault();
          setOverId(section.id);
        }}
        onDrop={(e) => {
          e.preventDefault();
          if (dragId) dispatch({ kind: 'reorder', id: dragId, before: section.id });
          setDragId('');
          setOverId('');
        }}
        onDragEnd={() => {
          setDragId('');
          setOverId('');
        }}
        moveActions={
          <>
            <IconButton icon="arrow-up-alt2" label={t.moveUp} disabled={index === 0} onClick={() => dispatch({ kind: 'move', id: section.id, offset: -1 })} />
            <IconButton icon="arrow-down-alt2" label={t.moveDown} disabled={index === sections.length - 1} onClick={() => dispatch({ kind: 'move', id: section.id, offset: 1 })} />
          </>
        }
        actions={<IconButton icon="trash" label={t.remove} danger onClick={() => setRemoveId(section.id)} />}
      >
        {type ? (
          type.fields.map((field) => (
            <FieldInput
              id={`sectionist-${section.id}-${field.name}`}
              field={field}
              value={String(section.data[field.name] ?? '')}
              onInput={(value) => dispatch({ kind: 'update', id: section.id, field: field.name, value })}
            />
          ))
        ) : (
          <p class="sectionist-note">{t.unknownType}</p>
        )}
      </Collapsible>
    );
  };

  return (
    <div class="sectionist">
      {error && <div class="notice notice-error inline"><p>{error}</p></div>}
      <Tabs
        tabs={[
          { id: 'sections', label: t.tabSections, icon: 'layout' },
          { id: 'seo', label: t.tabSeo, icon: 'search' },
        ]}
        active={tab}
        onChange={setTab}
        actions={
          tab === 'sections' && (
            <IconButton
              icon={allClosed ? 'arrow-down-alt2' : 'arrow-up-alt2'}
              label={allClosed ? t.expandAll : t.closeAll}
              disabled={sections.length === 0}
              onClick={toggleAll}
            />
          )
        }
      >
      {tab === 'seo' ? <p class="sectionist-note">{t.seoSoon}</p> : (
      <>
      {sections.length === 0 && <p class="sectionist-note">{t.empty}</p>}
      {sections.map(renderSection)}
      <div class="sectionist-add">
        <button type="button" class="button button-primary" onClick={() => setModalOpen(true)}>
          {t.add}
        </button>
      </div>
      {removeId && (
        <div class="sectionist-modal" onClick={(e) => e.target === e.currentTarget && setRemoveId('')} onKeyDown={(e) => e.key === 'Escape' && setRemoveId('')}>
          <div class="sectionist-modal__dialog sectionist-modal__dialog--small" role="alertdialog" aria-modal="true" aria-label={t.confirmRemoveTitle}>
            <div class="sectionist-modal__head">
              <strong>{t.confirmRemoveTitle}</strong>
              <IconButton icon="no-alt" label={t.cancel} onClick={() => setRemoveId('')} />
            </div>
            <p class="sectionist-modal__body">{t.confirmRemoveText}</p>
            <div class="sectionist-modal__footer">
              <button type="button" class="button" autofocus onClick={() => setRemoveId('')}>{t.cancel}</button>
              <button
                type="button"
                class="button button-primary sectionist-button-danger"
                onClick={() => {
                  dispatch({ kind: 'remove', id: removeId });
                  setRemoveId('');
                }}
              >
                {t.confirmRemoveAction}
              </button>
            </div>
          </div>
        </div>
      )}
      {modalOpen && (
        <div class="sectionist-modal" onClick={(e) => e.target === e.currentTarget && setModalOpen(false)} onKeyDown={(e) => e.key === 'Escape' && setModalOpen(false)}>
          <div class="sectionist-modal__dialog" role="dialog" aria-modal="true" aria-label={t.chooseType}>
            <div class="sectionist-modal__head">
              <strong>{t.chooseType}</strong>
              <IconButton icon="no-alt" label={t.cancel} onClick={() => setModalOpen(false)} />
            </div>
            <ul class="sectionist-modal__list">
              {config.types.map((type) => (
                <li>
                  <button
                    type="button"
                    class="sectionist-type-card"
                    onClick={() => {
                      dispatch({ kind: 'add', type });
                      setModalOpen(false);
                    }}
                  >
                    <Icon name={type.icon} />
                    <span class="sectionist-type-card__text">
                      <strong>{type.label}</strong>
                      <span>{type.description}</span>
                    </span>
                  </button>
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}
      </>
      )}
      </Tabs>
    </div>
  );
}

const root = document.getElementById('sectionist-editor');
if (root) {
  render(<App config={JSON.parse(root.dataset.config ?? '{}') as Config} />, root);
}
