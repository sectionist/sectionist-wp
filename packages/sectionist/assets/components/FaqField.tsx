import { useEffect, useState } from 'preact/hooks';
import type { Preview } from '../types';
import { ButtonGroup } from './ButtonGroup';
import { Checkbox, Field } from './Field';

export interface FaqValue {
  mode: 'topics' | 'selected';
  topics: number[];
  entries: number[];
  semantic: boolean;
}

interface Row {
  id: number;
  title: string;
}

const EMPTY: FaqValue = { mode: 'topics', topics: [], entries: [], semantic: true };

async function fetchRows(base: string, nonce: string, titleKey: 'title' | 'name'): Promise<Row[]> {
  const url = new URL(base);
  url.searchParams.set('per_page', '100');
  url.searchParams.set('context', 'edit');
  if (titleKey === 'title') {
    url.searchParams.set('orderby', 'menu_order');
    url.searchParams.set('order', 'asc');
  }
  const res = await fetch(url.toString(), { headers: { 'X-WP-Nonce': nonce } });
  if (!res.ok) return [];
  const rows = (await res.json()) as Record<string, unknown>[];
  return rows.map((r) => {
    const raw = r[titleKey];
    const title = typeof raw === 'string' ? raw : ((raw as { raw?: string; rendered?: string }).raw ?? (raw as { rendered?: string }).rendered ?? '');
    return { id: Number(r.id), title };
  });
}

/** Selection of central FAQ entries: by topics or hand-picked. The order always comes from the central FAQ. */
export function FaqField(props: { id: string; value: unknown; rest: Preview; labels: Record<string, string>; onInput: (v: FaqValue) => void }) {
  const t = props.labels;
  const v: FaqValue = { ...EMPTY, ...((props.value as Partial<FaqValue> | null) ?? {}) };
  const set = (patch: Partial<FaqValue>) => props.onInput({ ...v, ...patch });
  const [questions, setQuestions] = useState<Row[]>([]);
  const [topics, setTopics] = useState<Row[]>([]);

  useEffect(() => {
    fetchRows(props.rest.faqUrl, props.rest.nonce, 'title').then(setQuestions).catch(() => setQuestions([]));
    fetchRows(props.rest.topicsUrl, props.rest.nonce, 'name').then(setTopics).catch(() => setTopics([]));
  }, []);

  return (
    <div class="sectionist-faq-field">
      <Field label={t.faqSemantic}>
        <Checkbox checked={v.semantic} label={t.faqSemanticLabel} onInput={(on) => set({ semantic: on })} />
        <p class="description">{t.faqSemanticHint}</p>
      </Field>
      <Field label={t.faqMode}>
        <ButtonGroup
          label={t.faqMode}
          value={v.mode}
          options={[
            { value: 'topics', label: t.faqModeTopics },
            { value: 'selected', label: t.faqModeSelected },
          ]}
          onInput={(mode) => set({ mode: mode as FaqValue['mode'] })}
        />
      </Field>
      {v.mode === 'topics' ? (
        <Field label={t.faqTopics}>
          {topics.length === 0 ? (
            <p class="description">{t.faqNoTopics}</p>
          ) : (
            <div class="sectionist-faq-field__topics">
              {topics.map((topic) => (
                <Checkbox
                  checked={v.topics.includes(topic.id)}
                  label={topic.title}
                  onInput={(on) => set({ topics: on ? [...v.topics, topic.id] : v.topics.filter((id) => id !== topic.id) })}
                />
              ))}
              <p class="description">{t.faqTopicsHint}</p>
            </div>
          )}
        </Field>
      ) : (
        <Field label={t.faqQuestions}>
          {questions.length === 0 ? (
            <p class="description">{t.faqNoQuestions}</p>
          ) : (
            <div class="sectionist-faq-field__selected">
              <div class="sectionist-faq-field__bulk">
                <button type="button" class="button-link" onClick={() => set({ entries: questions.map((q) => q.id) })}>
                  {t.faqSelectAll}
                </button>
                <button type="button" class="button-link" onClick={() => set({ entries: [] })}>
                  {t.faqSelectNone}
                </button>
              </div>
              {questions.map((q) => (
                <Checkbox
                  checked={v.entries.includes(q.id)}
                  label={q.title}
                  onInput={(on) => set({ entries: on ? [...v.entries, q.id] : v.entries.filter((id) => id !== q.id) })}
                />
              ))}
              <p class="description">{t.faqOrderHint}</p>
            </div>
          )}
        </Field>
      )}
    </div>
  );
}
