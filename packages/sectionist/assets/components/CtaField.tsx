import { useEffect, useState } from 'preact/hooks';
import type { Preview } from '../types';
import { Checkbox, Field, Select, TextInput } from './Field';

export interface CtaValue {
  active: boolean;
  label: string;
  style: 'button' | 'link';
  page_id: number;
  url: string;
  new_tab: boolean;
}

const EMPTY: CtaValue = { active: false, label: '', style: 'button', page_id: 0, url: '', new_tab: false };

interface Page {
  id: number;
  title: string;
}

async function fetchPages(rest: Preview): Promise<Page[]> {
  const url = new URL(rest.pagesUrl);
  url.searchParams.set('per_page', '100');
  url.searchParams.set('orderby', 'title');
  url.searchParams.set('order', 'asc');
  url.searchParams.set('_fields', 'id,title');
  url.searchParams.set('context', 'edit');
  const res = await fetch(url.toString(), { headers: { 'X-WP-Nonce': rest.nonce } });
  if (!res.ok) return [];
  const rows = (await res.json()) as { id: number; title: { raw?: string; rendered: string } }[];
  return rows.map((r) => ({ id: r.id, title: r.title.raw || r.title.rendered }));
}

export function CtaField(props: { id: string; value: unknown; rest: Preview; labels: Record<string, string>; onInput: (v: CtaValue) => void }) {
  const { id, rest, labels: t } = props;
  const v: CtaValue = { ...EMPTY, ...((props.value as Partial<CtaValue> | null) ?? {}) };
  const set = (patch: Partial<CtaValue>) => props.onInput({ ...v, ...patch });
  const [pages, setPages] = useState<Page[]>([]);

  useEffect(() => {
    fetchPages(rest).then(setPages).catch(() => setPages([]));
  }, []);

  return (
    <div class="sectionist-cta-field">
      <Checkbox class="sectionist-cta-field__toggle" checked={v.active} label={t.ctaActive} onInput={(active) => set({ active })} />
      {v.active && (
        <div class="sectionist-cta-field__grid">
          <Field id={`${id}-style`} label={t.ctaStyle}>
            <Select
              id={`${id}-style`}
              value={v.style}
              options={[
                { value: 'button', label: t.ctaButton },
                { value: 'link', label: t.ctaLink },
              ]}
              onInput={(style) => set({ style: style as CtaValue['style'] })}
            />
          </Field>
          <Field id={`${id}-label`} label={t.ctaLabel}>
            <TextInput id={`${id}-label`} value={v.label} onInput={(label) => set({ label })} />
          </Field>
          <Field id={`${id}-page`} label={t.ctaPage}>
            <Select
              id={`${id}-page`}
              value={String(v.page_id)}
              options={[{ value: '0', label: t.ctaPageNone }, ...pages.map((p) => ({ value: String(p.id), label: p.title }))]}
              onInput={(page) => set({ page_id: Number(page) })}
            />
          </Field>
          <Field id={`${id}-url`} label={t.ctaUrl}>
            <TextInput id={`${id}-url`} placeholder="https://… / /offer" value={v.url} onInput={(url) => set({ url })} />
          </Field>
          <Checkbox class="sectionist-cta-field__toggle sectionist-cta-field__newtab" checked={v.new_tab} label={t.ctaNewTab} onInput={(new_tab) => set({ new_tab })} />
        </div>
      )}
    </div>
  );
}
