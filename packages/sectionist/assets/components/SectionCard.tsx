import type { Background, Section, TypeDef } from '../types';
import { Collapsible } from './Collapsible';
import { FieldInput } from './FieldInput';
import { IconButton } from './Icon';

const HEADING = /<h([1-6])[^>]*>([\s\S]*?)<\/h\1>/i;

function plain(html: string): string {
  return html.replace(/<[^>]*>/g, '').trim();
}

/** Title for the header (first heading, else first text) and the level of the first heading. */
function sectionSummary(type: TypeDef | undefined, section: Section): { title: string; level: number } {
  let title = '';
  let level = 0;
  for (const field of type?.fields ?? []) {
    if (field.kind === 'select') continue;
    const html = String(section.data[field.name] ?? '');
    const heading = field.kind === 'richtext' ? HEADING.exec(html) : null;
    if (heading && !level) {
      level = Number(heading[1]);
      title = plain(heading[2]);
    }
    if (!title) title = plain(html);
  }
  return { title: title.length > 60 ? title.slice(0, 60) + '…' : title, level };
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
  backgrounds: Background[];
  drag: DragProps;
  onToggle: () => void;
  onMove: (offset: -1 | 1) => void;
  onRemove: () => void;
  onActive: (value: boolean) => void;
  onBackground: (value: string) => void;
  onUpdate: (field: string, value: string) => void;
}) {
  const { section, type, labels: t } = props;
  const { title, level } = sectionSummary(type, section);
  const active = section.active !== false;
  const background = section.background ?? '';
  return (
    <Collapsible
      id={section.id}
      open={props.open}
      onToggle={props.onToggle}
      toggleLabel={t.toggle}
      title={
        <>
          <strong>{type?.label ?? section.type}</strong>
          {level > 0 && <span class="sectionist-badge">{`H${level}`}</span>}
          {title && <span class="sectionist-card__title">{title}</span>}
          {!active && <span class="sectionist-badge sectionist-badge--muted">{t.inactive}</span>}
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
      actions={
        <>
          <IconButton
            icon={active ? 'visibility' : 'hidden'}
            label={`${t.toggleActive} (${active ? t.active : t.inactive})`}
            onClick={() => props.onActive(!active)}
          />
          <IconButton icon="trash" label={t.remove} danger onClick={props.onRemove} />
        </>
      }
    >
      {props.backgrounds.length > 0 && (
        <div class="sectionist-field">
          <span class="sectionist-field__label">{t.background}</span>
          <div class="sectionist-swatches" role="radiogroup" aria-label={t.background}>
            {[{ key: '', label: t.backgroundNone, color: '' }, ...props.backgrounds].map((bg) => (
              <button
                type="button"
                role="radio"
                aria-checked={background === bg.key}
                class={'sectionist-swatch' + (background === bg.key ? ' is-active' : '')}
                title={bg.label}
                onClick={() => props.onBackground(bg.key)}
              >
                <span class="sectionist-swatch__color" style={bg.color ? { background: bg.color } : undefined} />
                {bg.label}
              </button>
            ))}
          </div>
        </div>
      )}
      {type ? (
        type.fields.map((field) => (
          <FieldInput
            id={`sectionist-${section.id}-${field.name}`}
            field={field}
            labels={t}
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
