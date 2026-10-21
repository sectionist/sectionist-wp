import type { ComponentChildren } from 'preact';
import { Icon } from './Icon';

export interface TabDef {
  id: string;
  label: string;
  icon?: string;
}

export function Tabs(props: { tabs: TabDef[]; active: string; onChange: (id: string) => void; actions?: ComponentChildren; children: ComponentChildren }) {
  return (
    <div class="sectionist-tabs">
      <div class="sectionist-tabs__nav" role="tablist">
        {props.tabs.map((tab) => (
          <button
            type="button"
            role="tab"
            aria-selected={tab.id === props.active}
            class={'sectionist-tabs__tab' + (tab.id === props.active ? ' is-active' : '')}
            onClick={() => props.onChange(tab.id)}
          >
            {tab.icon && <Icon name={tab.icon} />}
            {tab.label}
          </button>
        ))}
        {props.actions && <div class="sectionist-tabs__actions">{props.actions}</div>}
      </div>
      <div class="sectionist-tabs__panel" role="tabpanel">
        {props.children}
      </div>
    </div>
  );
}
