import { useEffect, useState } from 'preact/hooks';
import type { Preview } from '../types';

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
      <label class="sectionist-cta-field__toggle">
        <input type="checkbox" checked={v.active} onChange={(e) => set({ active: e.currentTarget.checked })} /> {t.ctaActive}
      </label>
      {v.active && (
        <div class="sectionist-cta-field__grid">
          <div class="sectionist-field">
            <label for={`${id}-style`}>{t.ctaStyle}</label>
            <select id={`${id}-style`} value={v.style} onChange={(e) => set({ style: e.currentTarget.value as CtaValue['style'] })}>
              <option value="button">{t.ctaButton}</option>
              <option value="link">{t.ctaLink}</option>
            </select>
          </div>
          <div class="sectionist-field">
            <label for={`${id}-label`}>{t.ctaLabel}</label>
            <input id={`${id}-label`} type="text" value={v.label} onInput={(e) => set({ label: e.currentTarget.value })} />
          </div>
          <div class="sectionist-field">
            <label for={`${id}-page`}>{t.ctaPage}</label>
            <select id={`${id}-page`} value={String(v.page_id)} onChange={(e) => set({ page_id: Number(e.currentTarget.value) })}>
              <option value="0">{t.ctaPageNone}</option>
              {pages.map((p) => (
                <option value={String(p.id)}>{p.title}</option>
              ))}
            </select>
          </div>
          <div class="sectionist-field">
            <label for={`${id}-url`}>{t.ctaUrl}</label>
            <input id={`${id}-url`} type="text" placeholder="https://… / /offer" value={v.url} onInput={(e) => set({ url: e.currentTarget.value })} />
          </div>
          <label class="sectionist-cta-field__toggle sectionist-cta-field__newtab">
            <input type="checkbox" checked={v.new_tab} onChange={(e) => set({ new_tab: e.currentTarget.checked })} /> {t.ctaNewTab}
          </label>
        </div>
      )}
    </div>
  );
}
