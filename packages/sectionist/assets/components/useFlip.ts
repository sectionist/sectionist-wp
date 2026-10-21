import { useLayoutEffect, useRef } from 'preact/hooks';

/**
 * Animates children carrying `data-flip-id` from their previous to their new position
 * whenever `orderKey` changes (FLIP technique).
 */
export function useFlip(orderKey: string) {
  const container = useRef<HTMLDivElement>(null);
  const previous = useRef(new Map<string, number>());
  const lastKey = useRef(orderKey);

  useLayoutEffect(() => {
    const root = container.current;
    if (!root) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const next = new Map<string, number>();
    root.querySelectorAll<HTMLElement>('[data-flip-id]').forEach((el) => {
      const id = el.dataset.flipId ?? '';
      const top = el.getBoundingClientRect().top;
      next.set(id, top);
      const before = previous.current.get(id);
      if (reduced || lastKey.current === orderKey || before === undefined || before === top) return;
      el.animate([{ transform: `translateY(${before - top}px)` }, { transform: 'none' }], { duration: 200, easing: 'ease-out' });
    });
    previous.current = next;
    lastKey.current = orderKey;
  });

  return container;
}
