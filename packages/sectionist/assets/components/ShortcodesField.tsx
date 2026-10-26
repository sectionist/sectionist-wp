import { useRef } from 'preact/hooks';
import { TextInput } from './Field';
import { focusAfterRemove } from './focus';
import { Icon, IconButton } from './Icon';
import { useDragReorder } from './useDragReorder';

interface Entry {
  code: string;
}

/** List of single shortcodes, one input per entry. Entries are reordered by drag & drop or with up/down buttons. */
export function ShortcodesField(props: { id: string; value: unknown; labels: Record<string, string>; onInput: (v: Entry[]) => void }) {
  const t = props.labels;
  const entries: Entry[] = Array.isArray(props.value) ? (props.value as Entry[]) : [];
  // Stable client-side keys so focus and drag state survive reordering.
  const keys = useRef<string[]>([]);
  const counter = useRef(0);
  const root = useRef<HTMLDivElement>(null);
  while (keys.current.length < entries.length) keys.current.push(`s${counter.current++}`);
  keys.current.length = entries.length;

  const commit = (next: Entry[], nextKeys: string[]) => {
    keys.current = nextKeys;
    props.onInput(next);
  };
  const update = (index: number, code: string) => props.onInput(entries.map((e, i) => (i === index ? { code } : e)));
  const reorder = (id: string, before: string) => {
    const from = keys.current.indexOf(id);
    const target = keys.current.indexOf(before);
    if (from < 0 || target < 0 || from === target) return;
    const next = [...entries];
    const nextKeys = [...keys.current];
    const [entry] = next.splice(from, 1);
    const [key] = nextKeys.splice(from, 1);
    next.splice(target, 0, entry);
    nextKeys.splice(target, 0, key);
    commit(next, nextKeys);
  };
  const move = (index: number, offset: -1 | 1) => reorder(keys.current[index], keys.current[index + offset]);
  const drag = useDragReorder(reorder);

  return (
    <div class="sectionist-shortcodes" ref={root}>
      {entries.map((entry, index) => {
        const key = keys.current[index];
        const h = drag.handlers(key);
        return (
          <div
            class={'sectionist-shortcodes__row' + (drag.isDragging(key) ? ' is-dragging' : '') + (drag.isDropTarget(key) ? ' is-drop-target' : '')}
            key={key}
            data-drag-item
            onDragOver={h.onDragOver}
            onDrop={h.onDrop}
          >
            <span class="sectionist-icon-button sectionist-card__handle" draggable title={t.drag} aria-hidden="true" onDragStart={h.onDragStart} onDragEnd={h.onDragEnd}>
              <Icon name="menu" />
            </span>
            <TextInput id={`${props.id}-${key}`} value={entry.code} onInput={(code) => update(index, code)} placeholder={t.shortcodePlaceholder} />
            <IconButton icon="arrow-up-alt2" label={`${t.moveUp}: ${index + 1}`} disabled={index === 0} onClick={() => move(index, -1)} />
            <IconButton
              icon="arrow-down-alt2"
              label={`${t.moveDown}: ${index + 1}`}
              disabled={index === entries.length - 1}
              onClick={() => move(index, 1)}
            />
            <IconButton
              icon="trash"
              label={`${t.shortcodeRemove}: ${index + 1}`}
              danger
              onClick={() => {
                commit(
                  entries.filter((_, i) => i !== index),
                  keys.current.filter((_, i) => i !== index),
                );
                focusAfterRemove(root.current, index, () => root.current?.querySelector('button.button'));
              }}
            />
          </div>
        );
      })}
      <button type="button" class="button" onClick={() => commit([...entries, { code: '' }], [...keys.current, `s${counter.current++}`])}>
        {t.shortcodeAdd}
      </button>
    </div>
  );
}
