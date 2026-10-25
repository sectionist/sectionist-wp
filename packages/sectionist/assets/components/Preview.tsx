import { useEffect, useState } from 'preact/hooks';
import type { Preview as PreviewConfig, Section } from '../types';

export function Preview(props: { sections: Section[]; config: PreviewConfig; loading: string; error: string; empty: string }) {
  const { sections, config } = props;
  const [html, setHtml] = useState('');
  const [status, setStatus] = useState<'loading' | 'ready' | 'error'>('loading');

  useEffect(() => {
    const controller = new AbortController();
    setStatus('loading');
    fetch(config.url, {
      method: 'POST',
      signal: controller.signal,
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce },
      body: JSON.stringify({ post_id: config.postId, sections }),
    })
      .then((res) => (res.ok ? res.json() : Promise.reject(new Error(String(res.status)))))
      .then((body: { html: string }) => {
        setHtml(body.html);
        setStatus('ready');
      })
      .catch((e: unknown) => {
        if ((e as Error).name !== 'AbortError') setStatus('error');
      });
    return () => controller.abort();
  }, []);

  if (status === 'loading') return <p class="sectionist-note" role="status">{props.loading}</p>;
  if (status === 'error') return <div class="notice notice-error inline" role="alert"><p>{props.error}</p></div>;
  if (!html.trim()) return <p class="sectionist-note">{props.empty}</p>;
  return <div class="sectionist-preview" dangerouslySetInnerHTML={{ __html: html }} />;
}
