import { render } from 'preact';
import { useEffect, useReducer, useState } from 'preact/hooks';
import { reducer } from './state';
import type { Config, TypeDef } from './types';
import { ConfirmModal } from './components/ConfirmModal';
import { IconButton } from './components/Icon';
import { Preview } from './components/Preview';
import { SectionCard } from './components/SectionCard';
import { Tabs } from './components/Tabs';
import { TypeModal } from './components/TypeModal';
import { useDragReorder } from './components/useDragReorder';
import { useFlip } from './components/useFlip';
import './style.css';

function App({ config }: { config: Config }) {
  const [sections, dispatch] = useReducer(reducer, config.sections);
  const [error, setError] = useState('');
  const [tab, setTab] = useState('sections');
  const [closed, setClosed] = useState<Set<string>>(() => new Set(config.sections.map((s) => s.id)));
  const [modalOpen, setModalOpen] = useState(false);
  const [removeId, setRemoveId] = useState('');
  const t = config.i18n;
  const listRef = useFlip(sections.map((s) => s.id).join(','));
  const drag = useDragReorder((id, before) => dispatch({ kind: 'reorder', id, before }));
  const allClosed = sections.length > 0 && closed.size >= sections.length;
  const toggleAll = () => setClosed(allClosed ? new Set() : new Set(sections.map((s) => s.id)));
  const toggle = (id: string) =>
    setClosed((prev) => {
      const next = new Set(prev);
      if (!next.delete(id)) next.add(id);
      return next;
    });
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

  return (
    <div class="sectionist">
      {error && <div class="notice notice-error inline"><p>{error}</p></div>}
      <Tabs
        tabs={[
          { id: 'sections', label: t.tabSections, icon: 'layout' },
          { id: 'preview', label: t.tabPreview, icon: 'visibility' },
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
        {tab === 'preview' ? (
          <Preview sections={sections} config={config.preview} loading={t.previewLoading} error={t.previewError} empty={t.previewEmpty} />
        ) : tab === 'seo' ? (
          <p class="sectionist-note">{t.seoSoon}</p>
        ) : (
          <>
            {sections.length === 0 && <p class="sectionist-note">{t.empty}</p>}
            <div ref={listRef}>
              {sections.map((section, index) => (
                <SectionCard
                  key={section.id}
                  section={section}
                  type={typeOf(section.type)}
                  open={!closed.has(section.id)}
                  first={index === 0}
                  last={index === sections.length - 1}
                  labels={t}
                  backgrounds={config.backgrounds ?? []}
                  rest={config.preview}
                  drag={{ dragging: drag.isDragging(section.id), dropTarget: drag.isDropTarget(section.id), ...drag.handlers(section.id) }}
                  onToggle={() => toggle(section.id)}
                  onMove={(offset) => dispatch({ kind: 'move', id: section.id, offset })}
                  onRemove={() => setRemoveId(section.id)}
                  onActive={(value) => dispatch({ kind: 'active', id: section.id, value })}
                  onBackground={(value) => dispatch({ kind: 'background', id: section.id, value })}
                  onUpdate={(field, value) => dispatch({ kind: 'update', id: section.id, field, value })}
                />
              ))}
            </div>
            <div class="sectionist-add">
              <button type="button" class="button button-primary" onClick={() => setModalOpen(true)}>
                {t.add}
              </button>
            </div>
            {removeId && (
              <ConfirmModal
                title={t.confirmRemoveTitle}
                text={t.confirmRemoveText}
                confirmLabel={t.confirmRemoveAction}
                cancelLabel={t.cancel}
                onCancel={() => setRemoveId('')}
                onConfirm={() => {
                  dispatch({ kind: 'remove', id: removeId });
                  setRemoveId('');
                }}
              />
            )}
            {modalOpen && (
              <TypeModal
                title={t.chooseType}
                closeLabel={t.cancel}
                types={config.types}
                onClose={() => setModalOpen(false)}
                onPick={(type) => {
                  dispatch({ kind: 'add', type });
                  setModalOpen(false);
                }}
              />
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
