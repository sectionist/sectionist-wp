import type { ComponentChildren } from 'preact';
import { useId } from 'preact/hooks';
import { Icon } from './Icon';

export interface TabDef {
  id: string;
  label: string;
  icon?: string;
}

export function Tabs(props: { tabs: TabDef[]; active: string; onChange: (id: string) => void; actions?: ComponentChildren; children: ComponentChildren }) {
  const base = useId();
  const tabId = (id: string) => `${base}-tab-${id}`;
  const panelId = `${base}-panel`;
  const onKeyDown = (e: KeyboardEvent) => {
    const index = props.tabs.findIndex((tab) => tab.id === props.active);
    let next = -1;
    if (e.key === 'ArrowRight') next = (index + 1) % props.tabs.length;
    else if (e.key === 'ArrowLeft') next = (index - 1 + props.tabs.length) % props.tabs.length;
    else if (e.key === 'Home') next = 0;
    else if (e.key === 'End') next = props.tabs.length - 1;
    if (next < 0) return;
    e.preventDefault();
    const id = props.tabs[next].id;
    props.onChange(id);
    requestAnimationFrame(() => document.getElementById(tabId(id))?.focus());
  };
  return (
    <div class="sectionist-tabs">
      <div class="sectionist-tabs__nav">
      <div class="sectionist-tabs__list" role="tablist" onKeyDown={onKeyDown}>
        {props.tabs.map((tab) => (
          <button
            type="button"
            role="tab"
            id={tabId(tab.id)}
            aria-selected={tab.id === props.active}
            aria-controls={panelId}
            tabIndex={tab.id === props.active ? 0 : -1}
            class={'sectionist-tabs__tab' + (tab.id === props.active ? ' is-active' : '')}
            onClick={() => props.onChange(tab.id)}
          >
            {tab.icon && <Icon name={tab.icon} />}
            {tab.label}
          </button>
        ))}
      </div>
        {props.actions && <div class="sectionist-tabs__actions">{props.actions}</div>}
      </div>
      <div class="sectionist-tabs__panel" role="tabpanel" id={panelId} aria-labelledby={tabId(props.active)}>
        {props.children}
      </div>
    </div>
  );
}
