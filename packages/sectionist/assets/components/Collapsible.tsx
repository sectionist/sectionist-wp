import type { ComponentChildren } from 'preact';
import { Icon, IconButton } from './Icon';

export function Collapsible(props: {
  open: boolean;
  onToggle: () => void;
  title: ComponentChildren;
  actions?: ComponentChildren;
  moveActions?: ComponentChildren;
  toggleLabel: string;
  dragLabel: string;
  dragging?: boolean;
  dropTarget?: boolean;
  onDragStart: (e: DragEvent) => void;
  onDragOver: (e: DragEvent) => void;
  onDrop: (e: DragEvent) => void;
  onDragEnd: () => void;
  children: ComponentChildren;
}) {
  const cls = 'sectionist-card' + (props.open ? ' is-open' : '') + (props.dragging ? ' is-dragging' : '') + (props.dropTarget ? ' is-drop-target' : '');
  return (
    <div class={cls} onDragOver={props.onDragOver} onDrop={props.onDrop}>
      <div
        class="sectionist-card__header"
        onClick={(e) => {
          if (!(e.target as HTMLElement).closest('button, .sectionist-card__handle')) props.onToggle();
        }}
      >
        <span
          class="sectionist-icon-button sectionist-card__handle"
          draggable
          title={props.dragLabel}
          aria-label={props.dragLabel}
          onDragStart={props.onDragStart}
          onDragEnd={props.onDragEnd}
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
