import type { Background, FieldDef, Preview, Section, TypeDef } from '../types';
import { ButtonGroup } from './ButtonGroup';
import { Collapsible } from './Collapsible';
import { FieldInput } from './FieldInput';
import { Icon, IconButton } from './Icon';
import { Panel } from './Panel';

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
  const background = section.background ?? '';
  const colorBackdrop = (section.data.media ?? 'none') === 'background';
  const swatches =
    props.backgrounds.length > 0 && (
        <div class="sectionist-field">
          <span class="sectionist-field__label">{t.background}</span>
          <div class="sectionist-swatches" role="radiogroup" aria-label={t.background}>
            {[{ key: '', label: t.backgroundNone }, ...props.backgrounds].map((bg) => (
              <button
                type="button"
                role="radio"
                aria-checked={background === bg.key}
                class={'sectionist-swatch' + (background === bg.key ? ' is-active' : '')}
                title={bg.label}
                onClick={() => props.onBackground(bg.key)}
              >
                <span class={'sectionist-swatch__color' + (bg.key ? ' section--bg-' + bg.key : '')} />
                {bg.label}
              </button>
            ))}
          </div>
        </div>
    );
  const fields = type?.fields ?? [];
  const isController = (field: FieldDef) =>
    (field.kind === 'choice' && field.options.some((o) => o.value === 'none')) || fields.some((other) => other.showIf?.field === field.name);
  const isVisible = (field: FieldDef) => !field.showIf || String(section.data[field.showIf.field] ?? '') === field.showIf.value;
  const content = fields.filter((field) => field.kind === 'richtext');
  const options = fields.filter((field) => field.kind !== 'richtext');
  const isPanel = (field: FieldDef) => !field.showIf && (isController(field) || field.kind === 'media' || (field.kind === 'cta' && !field.required));
  const loose = options.filter((field) => !field.showIf && !isPanel(field));
  const panels = options.filter(isPanel).sort((a, b) => a.label.localeCompare(b.label));
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
          <>
            <IconButton icon="arrow-up-alt2" label={t.moveUp} disabled={props.first} onClick={() => props.onMove(-1)} />
            <IconButton icon="arrow-down-alt2" label={t.moveDown} disabled={props.last} onClick={() => props.onMove(1)} />
          </>
        )
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
      {type ? (
        <>
          {content.map(renderField)}
          {loose.map(renderField)}
          {panels.map((controller) => {
            if (!isController(controller)) return renderField(controller);
            const choices = controller.kind === 'choice' ? controller.options.filter((o) => o.value !== 'none') : [];
            const current = String(section.data[controller.name] ?? 'none');
            const on = current !== 'none';
            const dependents = options.filter((field) => field.showIf?.field === controller.name && isVisible(field));
            const single = choices.length <= 1 && dependents.length === 0 && props.backgrounds.length <= 1;
            const only = props.backgrounds[0]?.key ?? '';
            return (
              <Panel
                title={controller.label}
                toggleLabel={t.toggle}
                active={on}
                headerOnly={single}
                activeLabel={`${t.toggleActive} (${on ? t.active : t.inactive})`}
                onActive={(value) => {
                  props.onUpdate(controller.name, value ? (choices[0]?.value ?? 'none') : 'none');
                  if (single && choices[0]?.value === 'background') props.onBackground(value ? only : '');
                }}
              >
                {choices.length > 1 && (
                  <ButtonGroup options={choices} value={current} label={controller.label} onInput={(value) => props.onUpdate(controller.name, value)} />
                )}
                {colorBackdrop && swatches}
                {dependents.map(renderField)}
              </Panel>
            );
          })}
        </>
      ) : (
        <p class="sectionist-note">{t.unknownType}</p>
      )}
    </Collapsible>
  );
}
