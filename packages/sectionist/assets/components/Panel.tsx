import type { ComponentChildren } from 'preact';
import { useState } from 'preact/hooks';
import { IconButton } from './Icon';

export function Panel(props: {
  title: string;
  toggleLabel: string;
  defaultOpen?: boolean;
  active?: boolean;
  activeLabel?: string;
  onActive?: (value: boolean) => void;
  children: ComponentChildren;
}) {
  const [open, setOpen] = useState((props.defaultOpen ?? true) && (props.active ?? true));
  const active = props.active ?? true;
  const expanded = open && active;
  const toggle = () => active && setOpen(!open);
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
            label={props.activeLabel ?? props.title}
            onClick={() => {
              setOpen(false);
              props.onActive?.(!active);
            }}
          />
        )}
        <IconButton icon={expanded ? 'arrow-up-alt2' : 'arrow-down-alt2'} label={props.toggleLabel} expanded={expanded} disabled={!active} onClick={toggle} />
      </div>
      <div class="sectionist-panel__body" hidden={!expanded}>
        {active && props.children}
      </div>
    </div>
  );
}
