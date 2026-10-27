import type { FieldDef, Preview } from '../types';
import { ButtonGroup } from './ButtonGroup';
import { CtaField } from './CtaField';
import { FaqField } from './FaqField';
import { Field, Select, TextInput } from './Field';
import { GalleryField } from './GalleryField';
import { ImageField } from './ImageField';
import { ListField } from './ListField';
import { MediaField } from './MediaField';
import { Panel } from './Panel';
import { RichTextEditor } from './richtext/RichTextEditor';
import { ShortcodesField } from './ShortcodesField';

export function FieldInput(props: {
  id: string;
  field: FieldDef;
  value: unknown;
  rest: Preview;
  labels: Record<string, string>;
  onInput: (v: unknown) => void;
}) {
  const { id, field, onInput } = props;
  const t = props.labels;
  const toggleable = field.kind === 'cta' || field.kind === 'media';
  const object = toggleable ? ((props.value as { active?: boolean } | null) ?? {}) : {};
  const active = Boolean((object as { active?: boolean }).active);
  const panel = {
    title: field.label,
    toggleLabel: t.toggle,
    active,
    activeLabel: `${t.toggleActive} (${active ? t.active : t.inactive})`,
    onActive: (value: boolean) => onInput({ ...object, active: value }),
  };
  if (field.kind === 'cta' && field.required) {
    return <CtaField id={id} value={props.value} rest={props.rest} labels={props.labels} onInput={onInput} />;
  }
  if (field.kind === 'cta') {
    return (
      <Panel {...panel}>
        <CtaField id={id} value={props.value} rest={props.rest} labels={props.labels} onInput={onInput} />
      </Panel>
    );
  }
  if (field.kind === 'media') {
    return (
      <Panel {...panel}>
        <MediaField value={props.value} labels={props.labels} onInput={onInput} />
      </Panel>
    );
  }
  if (field.kind === 'list') {
    return (
      <Panel title={field.label} toggleLabel={t.toggle}>
        <ListField id={id} value={props.value} labels={props.labels} onInput={onInput} />
      </Panel>
    );
  }
  if (field.kind === 'shortcodes') {
    return (
      <Panel title={field.label} toggleLabel={t.toggle}>
        <ShortcodesField id={id} value={props.value} labels={props.labels} onInput={onInput} />
      </Panel>
    );
  }
  if (field.kind === 'gallery') {
    return (
      <Panel title={field.label} toggleLabel={t.toggle}>
        <GalleryField id={id} value={props.value} labels={props.labels} onInput={onInput} />
      </Panel>
    );
  }
  if (field.kind === 'faq') {
    return (
      <Panel title={field.label} toggleLabel={t.toggle}>
        <FaqField id={id} value={props.value} rest={props.rest} labels={props.labels} onInput={onInput} />
      </Panel>
    );
  }
  if (field.kind === 'image') {
    return (
      <Field label={field.label}>
        <ImageField value={Number(props.value) || 0} labels={props.labels} onInput={onInput} />
      </Field>
    );
  }
  const value = String(props.value ?? '');
  return (
    <Field id={field.kind === 'choice' ? undefined : id} label={field.label}>
      {field.kind === 'richtext' ? (
        <RichTextEditor id={id} label={field.label} value={value} labels={props.labels} onInput={onInput} />
      ) : field.kind === 'choice' ? (
        <ButtonGroup options={field.options} value={value} label={field.label} onInput={onInput} />
      ) : field.kind === 'select' ? (
        <Select id={id} value={value} options={field.options} onInput={onInput} />
      ) : (
        <TextInput id={id} inputMode={field.kind === 'url' ? 'url' : undefined} placeholder={field.kind === 'url' ? t.urlPlaceholder : undefined} value={value} onInput={onInput} />
      )}
    </Field>
  );
}
