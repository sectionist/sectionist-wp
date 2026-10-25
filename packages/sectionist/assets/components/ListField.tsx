import { useRef, useState } from 'preact/hooks';
import { Collapsible } from './Collapsible';
import { Field, TextInput, Textarea } from './Field';
import { focusAfterRemove } from './focus';
import { IconButton } from './Icon';
import { MoveButtons } from './MoveButtons';
import { useDragReorder } from './useDragReorder';

interface Entry {
  title: string;
  text: string;
}

/** Dynamic list of entries with title and text. Each entry is a collapsible, sortable card. */
export function ListField(props: { id: string; value: unknown; labels: Record<string, string>; onInput: (v: Entry[]) => void }) {
  const t = props.labels;
  const entries: Entry[] = Array.isArray(props.value) ? (props.value as Entry[]) : [];
  // Stable client-side keys so open state and drag & drop survive reordering.
  const keys = useRef<string[]>([]);
  const counter = useRef(0);
  const root = useRef<HTMLDivElement>(null);
  while (keys.current.length < entries.length) keys.current.push(`e${counter.current++}`);
  keys.current.length = entries.length;
  const [open, setOpen] = useState<string[]>([]);

  const commit = (next: Entry[], nextKeys: string[]) => {
    keys.current = nextKeys;
    props.onInput(next);
  };
  const update = (index: number, patch: Partial<Entry>) => props.onInput(entries.map((e, i) => (i === index ? { ...e, ...patch } : e)));
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
  const moveBy = (index: number, offset: -1 | 1) => reorder(keys.current[index], keys.current[index + offset]);
  const drag = useDragReorder(reorder);
  const toggle = (key: string) => setOpen(open.includes(key) ? open.filter((k) => k !== key) : [...open, key]);

  return (
    <div class="sectionist-list" ref={root}>
      {entries.map((entry, index) => {
        const key = keys.current[index];
        return (
          <Collapsible
            key={key}
            id={key}
            open={open.includes(key)}
            onToggle={() => toggle(key)}
            title={entry.title.trim() !== '' ? entry.title : `${t.listEntry} ${index + 1}`}
            toggleLabel={t.toggle}
            name={entry.title.trim() !== '' ? entry.title : `${t.listEntry} ${index + 1}`}
            dragLabel={t.drag}
            dragging={drag.isDragging(key)}
            dropTarget={drag.isDropTarget(key)}
            {...drag.handlers(key)}
            moveActions={
              <MoveButtons
                id={key}
                upLabel={t.moveUp}
                downLabel={t.moveDown}
                first={index === 0}
                last={index === entries.length - 1}
                onMove={(offset) => moveBy(index, offset)}
              />
            }
            actions={
              <IconButton
                icon="trash"
                label={`${t.listRemove}: ${entry.title.trim() !== '' ? entry.title : `${t.listEntry} ${index + 1}`}`}
                danger
                onClick={() => {
                  commit(
                    entries.filter((_, i) => i !== index),
                    keys.current.filter((_, i) => i !== index),
                  );
                  focusAfterRemove(root.current, index, () => root.current?.querySelector('button.button'));
                }}
              />
            }
          >
            <Field id={`${props.id}-${key}-title`} label={t.listTitle}>
              <TextInput id={`${props.id}-${key}-title`} value={entry.title} onInput={(title) => update(index, { title })} />
            </Field>
            <Field id={`${props.id}-${key}-text`} label={t.listText}>
              <Textarea id={`${props.id}-${key}-text`} value={entry.text} onInput={(text) => update(index, { text })} />
            </Field>
          </Collapsible>
        );
      })}
      <button
        type="button"
        class="button"
        onClick={() => {
          setOpen([...open, `e${counter.current}`]);
          commit([...entries, { title: '', text: '' }], [...keys.current, `e${counter.current++}`]);
        }}
      >
        {t.listAdd}
      </button>
    </div>
  );
}
