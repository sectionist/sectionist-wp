import type { Section, TypeDef } from '../types';
import { Collapsible } from './Collapsible';
import { FieldInput } from './FieldInput';
import { IconButton } from './Icon';

function sectionTitle(type: TypeDef | undefined, section: Section): string {
  for (const field of type?.fields ?? []) {
    if (field.kind === 'select') continue;
    const text = String(section.data[field.name] ?? '').replace(/<[^>]*>/g, '').trim();
    if (text) return text.length > 60 ? text.slice(0, 60) + '…' : text;
  }
  return '';
}

export interface DragProps {
  dragging: boolean;
  dropTarget: boolean;
  onDragStart: (e: DragEvent) => void;
  onDragOver: (e: DragEvent) => void;
  onDrop: (e: DragEvent) => void;
  onDragEnd: () => void;
}

export function SectionCard(props: {
  section: Section;
  type: TypeDef | undefined;
  open: boolean;
  first: boolean;
  last: boolean;
  labels: Record<string, string>;
  drag: DragProps;
  onToggle: () => void;
  onMove: (offset: -1 | 1) => void;
  onRemove: () => void;
  onUpdate: (field: string, value: string) => void;
}) {
  const { section, type, labels: t } = props;
  const title = sectionTitle(type, section);
  return (
    <Collapsible
      id={section.id}
      open={props.open}
      onToggle={props.onToggle}
      toggleLabel={t.toggle}
      title={
        <>
          <strong>{type?.label ?? section.type}</strong>
          {title && <span class="sectionist-card__title">{title}</span>}
        </>
      }
      dragLabel={t.drag}
      {...props.drag}
      moveActions={
        <>
          <IconButton icon="arrow-up-alt2" label={t.moveUp} disabled={props.first} onClick={() => props.onMove(-1)} />
          <IconButton icon="arrow-down-alt2" label={t.moveDown} disabled={props.last} onClick={() => props.onMove(1)} />
        </>
      }
      actions={<IconButton icon="trash" label={t.remove} danger onClick={props.onRemove} />}
    >
      {type ? (
        type.fields.map((field) => (
          <FieldInput
            id={`sectionist-${section.id}-${field.name}`}
            field={field}
            value={String(section.data[field.name] ?? '')}
            onInput={(value) => props.onUpdate(field.name, value)}
          />
        ))
      ) : (
        <p class="sectionist-note">{t.unknownType}</p>
      )}
    </Collapsible>
  );
}
