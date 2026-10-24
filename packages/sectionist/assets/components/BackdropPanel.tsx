import type { ComponentChildren } from 'preact';
import type { Background, FieldDef } from '../types';
import { ButtonGroup } from './ButtonGroup';
import { Panel } from './Panel';

/**
 * Backdrop of a section: a panel switched on and off in its header.
 * "none" is the off state; the first other option is the on state.
 * With a single color and nothing else to choose, the header alone is enough.
 */
export function BackdropPanel(props: {
  field: FieldDef;
  value: string;
  background: string;
  backgrounds: Background[];
  labels: Record<string, string>;
  hasDependents: boolean;
  onValue: (value: string) => void;
  onBackground: (value: string) => void;
  children?: ComponentChildren;
}) {
  const { field, value, labels: t } = props;
  const choices = field.options.filter((o) => o.value !== 'none');
  const on = value !== 'none';
  const single = choices.length <= 1 && !props.hasDependents && props.backgrounds.length <= 1;
  const only = props.backgrounds[0]?.key ?? '';
  const colors = value === 'background' && props.backgrounds.length > 0;

  return (
    <Panel
      title={field.label}
      toggleLabel={t.toggle}
      active={on}
      headerOnly={single}
      activeLabel={`${t.toggleActive} (${on ? t.active : t.inactive})`}
      onActive={(next) => {
        props.onValue(next ? (choices[0]?.value ?? 'none') : 'none');
        if (single && choices[0]?.value === 'background') props.onBackground(next ? only : '');
      }}
    >
      {choices.length > 1 && <ButtonGroup options={choices} value={value} label={field.label} onInput={props.onValue} />}
      {colors && (
        <div class="sectionist-field">
          <span class="sectionist-field__label">{t.background}</span>
          <div class="sectionist-swatches" role="radiogroup" aria-label={t.background}>
            {[{ key: '', label: t.backgroundNone }, ...props.backgrounds].map((bg) => (
              <button
                type="button"
                role="radio"
                aria-checked={props.background === bg.key}
                class={'sectionist-swatch' + (props.background === bg.key ? ' is-active' : '')}
                title={bg.label}
                onClick={() => props.onBackground(bg.key)}
              >
                <span class={'sectionist-swatch__color' + (bg.key ? ' section--bg-' + bg.key : '')} />
                {bg.label}
              </button>
            ))}
          </div>
        </div>
      )}
      {props.children}
    </Panel>
  );
}
