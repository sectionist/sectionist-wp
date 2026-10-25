import type { ComponentChildren } from 'preact';
import { useId, useState } from 'preact/hooks';
import { IconButton } from './Icon';

export function Panel(props: {
  title: string;
  toggleLabel: string;
  active?: boolean;
  headerOnly?: boolean;
  activeLabel?: string;
  onActive?: (value: boolean) => void;
  children: ComponentChildren;
}) {
  const [open, setOpen] = useState(false);
  const bodyId = useId();
  const active = props.active ?? true;
  const expanded = open && active && !props.headerOnly;
  const toggle = () => active && !props.headerOnly && setOpen(!open);
  return (
    <div class={'sectionist-panel' + (expanded ? ' is-open' : '') + (active ? '' : ' is-inactive')}>
      <div
        class="sectionist-panel__header"
        onClick={(e) => {
          if (!(e.target as HTMLElement).closest('button')) toggle();
        }}
      >
        <span class="sectionist-panel__title">{props.title}</span>
        {props.onActive && (
          <IconButton
            icon={active ? 'visibility' : 'hidden'}
            label={`${props.activeLabel ?? props.title}: ${props.title}`}
            pressed={active}
            onClick={() => {
              setOpen(false);
              props.onActive?.(!active);
            }}
          />
        )}
        {!props.headerOnly && (
          <IconButton
            icon={expanded ? 'arrow-up-alt2' : 'arrow-down-alt2'}
            label={`${props.toggleLabel}: ${props.title}`}
            expanded={expanded}
            controls={bodyId}
            disabled={!active}
            onClick={toggle}
          />
        )}
      </div>
      {!props.headerOnly && (
        <div class="sectionist-panel__body" id={bodyId} hidden={!expanded}>
          {active && props.children}
        </div>
      )}
    </div>
  );
}
