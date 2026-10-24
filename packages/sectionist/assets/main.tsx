import { render } from 'preact';
import { useEffect, useReducer, useRef, useState } from 'preact/hooks';
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
  const [leaveUrl, setLeaveUrl] = useState('');
  const [backdropIds, setBackdropIds] = useState<string[]>([]);
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

  const initial = useRef(JSON.stringify({ sections: config.sections }));
  const dirty = useRef(false);
  const saving = useRef(false);
  const sectionsRef = useRef(sections);
  sectionsRef.current = sections;
  const resubmit = useRef<HTMLElement | null | undefined>(undefined);
  const hasBackgrounds = (config.backgrounds ?? []).length > 0;
  const unsetBackdrops = (list: typeof sections) =>
    list
      .filter((s) => {
        if (s.type !== 'hero') return false;
        if (s.data.media === 'image') return !Number(s.data.image);
        return s.data.media === 'background' && hasBackgrounds && !s.background;
      })
      .map((s) => s.id);

  useEffect(() => {
    const form = document.getElementById('post');
    const onSubmit = (e: Event) => {
      const ids = unsetBackdrops(sectionsRef.current);
      if (ids.length > 0) {
        e.preventDefault();
        e.stopImmediatePropagation();
        resubmit.current = (e as SubmitEvent).submitter;
        setBackdropIds(ids);
        return;
      }
      saving.current = true;
    };
    const onUnload = (e: BeforeUnloadEvent) => {
      if (!dirty.current || saving.current) return;
      e.preventDefault();
      e.returnValue = '';
    };
    const onClick = (e: MouseEvent) => {
      if (!dirty.current || saving.current || e.defaultPrevented || e.button !== 0) return;
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const link = (e.target as Element | null)?.closest?.('a[href]') as HTMLAnchorElement | null;
      if (!link || (link.target && link.target !== '_self') || link.hasAttribute('download')) return;
      const url = new URL(link.href, window.location.href);
      if (!/^https?:$/.test(url.protocol)) return;
      if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash !== '') return;
      e.preventDefault();
      setLeaveUrl(url.href);
    };
    form?.addEventListener('submit', onSubmit, true);
    window.addEventListener('beforeunload', onUnload);
    document.addEventListener('click', onClick, true);
    return () => {
      document.removeEventListener('click', onClick, true);
      form?.removeEventListener('submit', onSubmit, true);
      window.removeEventListener('beforeunload', onUnload);
    };
  }, []);

  useEffect(() => {
    const input = document.getElementById('sectionist-data') as HTMLInputElement | null;
    if (!input) return;
    try {
      input.value = JSON.stringify({ sections });
      dirty.current = input.value !== initial.current;
      setError('');
    } catch {
      setError(t.saveError);
    }
    if (resubmit.current !== undefined && backdropIds.length === 0) {
      const submitter = resubmit.current;
      resubmit.current = undefined;
      (document.getElementById('post') as HTMLFormElement | null)?.requestSubmit(submitter as HTMLElement | null);
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
      {backdropIds.length > 0 && (
        <ConfirmModal
          title={t.backdropTitle}
          text={t.backdropText}
          confirmLabel={t.backdropConfirm}
          cancelLabel={t.cancel}
          onCancel={() => {
            resubmit.current = undefined;
            setBackdropIds([]);
          }}
          onConfirm={() => {
            backdropIds.forEach((id) => dispatch({ kind: 'update', id, field: 'media', value: 'none' }));
            setBackdropIds([]);
          }}
        />
      )}
      {leaveUrl && (
        <ConfirmModal
          title={t.unsavedTitle}
          text={t.unsavedText}
          confirmLabel={t.unsavedLeave}
          cancelLabel={t.unsavedStay}
          onCancel={() => setLeaveUrl('')}
          onConfirm={() => {
            saving.current = true;
            window.location.href = leaveUrl;
          }}
        />
      )}
    </div>
  );
}

const root = document.getElementById('sectionist-editor');
if (root) {
  render(<App config={JSON.parse(root.dataset.config ?? '{}') as Config} />, root);
}
