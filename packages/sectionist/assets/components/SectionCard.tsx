import type { Background, FieldDef, Preview, Section, TypeDef } from '../types';
import { BackdropPanel } from './BackdropPanel';
import { Collapsible } from './Collapsible';
import { FieldInput } from './FieldInput';
import { Icon, IconButton } from './Icon';
import { MoveButtons } from './MoveButtons';

const HEADING = /<h([1-6])[^>]*>([\s\S]*?)<\/h\1>/i;

function plain(html: string): string {
  return html.replace(/<[^>]*>/g, '').trim();
}

/** Title for the header (first heading, else first text) and the level of the first heading. */
function sectionSummary(type: TypeDef | undefined, section: Section): { title: string; level: number } {
  let title = '';
  let level = 0;
  for (const field of type?.fields ?? []) {
    const value = section.data[field.name];
    if (field.kind !== 'richtext' || typeof value !== 'string') continue;
    const html = value;
    const heading = HEADING.exec(html);
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
  pinned: boolean;
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
  onUpdate: (field: string, value: unknown) => void;
  rest: Preview;
}) {
  const { section, type, labels: t } = props;
  const { title, level } = sectionSummary(type, section);
  const active = section.active !== false;
  const name = [type?.label ?? section.type, title].filter(Boolean).join(' – ');
  const background = section.background ?? '';
  const fields = type?.fields ?? [];
  const isVisible = (field: FieldDef) => !field.showIf || String(section.data[field.showIf.field] ?? '') === field.showIf.value;
  const content = fields.filter((field) => field.kind === 'richtext');
  const options = fields.filter((field) => field.kind !== 'richtext');
  const loose = options.filter((field) => !field.showIf && !field.panel);
  const panels = options.filter((field) => field.panel).sort((a, b) => a.label.localeCompare(b.label));
  const renderField = (field: FieldDef) => (
    <FieldInput
      id={`sectionist-${section.id}-${field.name}`}
      field={field}
      labels={t}
      value={section.data[field.name]}
      rest={props.rest}
      onInput={(value) => props.onUpdate(field.name, value)}
    />
  );
  return (
    <Collapsible
      id={section.id}
      open={props.open}
      onToggle={props.onToggle}
      toggleLabel={t.toggle}
      name={name}
      title={
        <>
          <strong>{type?.label ?? section.type}</strong>
          {level > 0 && <span class="sectionist-badge">{`H${level}`}</span>}
          {title && <span class="sectionist-card__title">{title}</span>}
          {!type && (
            <span class="sectionist-badge sectionist-badge--warning" title={t.unknownType}>
              <Icon name="warning" />
              {t.unavailable}
            </span>
          )}
          {!active && <span class="sectionist-badge sectionist-badge--muted">{t.inactive}</span>}
        </>
      }
      dragLabel={t.drag}
      {...props.drag}
      dragDisabled={props.pinned}
      moveActions={
        props.pinned ? undefined : (
          <MoveButtons id={section.id} upLabel={t.moveUp} downLabel={t.moveDown} first={props.first} last={props.last} onMove={props.onMove} />
        )
      }
      actions={
        <>
          <IconButton
            icon={active ? 'visibility' : 'hidden'}
            label={`${t.toggleActive}: ${name}`}
            pressed={active}
            onClick={() => props.onActive(!active)}
          />
          <IconButton icon="trash" label={`${t.remove}: ${name}`} danger onClick={props.onRemove} />
        </>
      }
    >
      {type ? (
        <>
          {content.map(renderField)}
          {loose.map(renderField)}
          {panels.map((panel) => {
            if (panel.kind !== 'choice') return renderField(panel);
            const dependents = options.filter((field) => field.showIf?.field === panel.name);
            return (
              <BackdropPanel
                field={panel}
                value={String(section.data[panel.name] ?? 'none')}
                background={background}
                backgrounds={props.backgrounds}
                labels={t}
                hasDependents={dependents.length > 0}
                onValue={(value) => props.onUpdate(panel.name, value)}
                onBackground={props.onBackground}
              >
                {dependents.filter(isVisible).map(renderField)}
              </BackdropPanel>
            );
          })}
        </>
      ) : (
        <p class="sectionist-note">{t.unknownType}</p>
      )}
    </Collapsible>
  );
}
