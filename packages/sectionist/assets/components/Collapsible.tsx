import type { ComponentChildren } from 'preact';
import { Icon, IconButton } from './Icon';

export function Collapsible(props: {
  id: string;
  open: boolean;
  onToggle: () => void;
  title: ComponentChildren;
  actions?: ComponentChildren;
  moveActions?: ComponentChildren;
  toggleLabel: string;
  dragLabel: string;
  dragging?: boolean;
  dragDisabled?: boolean;
  dropTarget?: boolean;
  onDragStart: (e: DragEvent) => void;
  onDragOver: (e: DragEvent) => void;
  onDrop: (e: DragEvent) => void;
  onDragEnd: () => void;
  children: ComponentChildren;
}) {
  const locked = props.dragDisabled;
  const cls = 'sectionist-card' + (props.open ? ' is-open' : '') + (props.dragging ? ' is-dragging' : '') + (props.dropTarget ? ' is-drop-target' : '');
  return (
    <div class={cls} data-flip-id={props.id} onDragOver={locked ? undefined : props.onDragOver} onDrop={locked ? undefined : props.onDrop}>
      <div
        class="sectionist-card__header"
        onClick={(e) => {
          if (!(e.target as HTMLElement).closest('button, .sectionist-card__handle')) props.onToggle();
        }}
      >
        <span
          class={'sectionist-icon-button sectionist-card__handle' + (locked ? ' is-disabled' : '')}
          draggable={!locked}
          title={props.dragLabel}
          aria-label={props.dragLabel}
          aria-disabled={locked}
          onDragStart={locked ? undefined : props.onDragStart}
          onDragEnd={locked ? undefined : props.onDragEnd}
        >
          <Icon name="menu" />
        </span>
        <div class="sectionist-card__toggle">{props.title}</div>
        {props.moveActions && <span class="sectionist-card__group">{props.moveActions}</span>}
        {props.actions && <span class="sectionist-card__actions">{props.actions}</span>}
        <IconButton
          icon={props.open ? 'arrow-up-alt2' : 'arrow-down-alt2'}
          label={props.toggleLabel}
          expanded={props.open}
          onClick={props.onToggle}
        />
      </div>
      <div class="sectionist-card__body" hidden={!props.open}>
        {props.children}
      </div>
    </div>
  );
}
