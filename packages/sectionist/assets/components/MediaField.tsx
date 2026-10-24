import { ButtonGroup } from './ButtonGroup';
import { Checkbox, Field } from './Field';
import { ImageField } from './ImageField';

export interface MediaValue {
  active: boolean;
  id: number;
  position: 'left' | 'right' | 'top' | 'bottom';
}

const POSITIONS: MediaValue['position'][] = ['left', 'right', 'top', 'bottom'];

const EMPTY: MediaValue = { active: false, id: 0, position: 'right' };

export function MediaField(props: { value: unknown; labels: Record<string, string>; onInput: (v: MediaValue) => void }) {
  const { labels: t } = props;
  const v: MediaValue = { ...EMPTY, ...((props.value as Partial<MediaValue> | null) ?? {}) };
  const set = (patch: Partial<MediaValue>) => props.onInput({ ...v, ...patch });

  return (
    <div class="sectionist-media-field">
      <Checkbox class="sectionist-media-field__toggle" checked={v.active} label={t.mediaActive} onInput={(active) => set({ active })} />
      {v.active && (
        <div class="sectionist-media-field__grid">
          <ImageField value={Number(v.id) || 0} labels={t} onInput={(imageId) => set({ id: imageId })} />
          <Field label={t.mediaPosition}>
            <ButtonGroup
              options={POSITIONS.map((p) => ({ value: p, label: t['media' + p[0].toUpperCase() + p.slice(1)] }))}
              value={v.position}
              label={t.mediaPosition}
              onInput={(position) => set({ position: position as MediaValue['position'] })}
            />
          </Field>
        </div>
      )}
    </div>
  );
}
