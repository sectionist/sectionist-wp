/** Polite screen-reader announcement via a shared visually hidden live region. */
export function announce(message: string) {
  let region = document.getElementById('sectionist-live');
  if (!region) {
    region = document.createElement('div');
    region.id = 'sectionist-live';
    region.className = 'screen-reader-text';
    region.setAttribute('aria-live', 'polite');
    document.body.appendChild(region);
  }
  region.textContent = '';
  requestAnimationFrame(() => {
    if (region) region.textContent = message;
  });
}

/** After removing item `index`: focus the next item's actions, else the previous one, else the fallback. */
export function focusAfterRemove(container: Element | null, index: number, fallback?: () => HTMLElement | null | undefined) {
  requestAnimationFrame(() => {
    const cards = Array.from(container?.querySelectorAll<HTMLElement>(':scope > .sectionist-card, :scope > [data-flip-id]') ?? []);
    const card = cards[index] ?? cards[index - 1];
    const target = card?.querySelector<HTMLElement>('.sectionist-card__actions button, .sectionist-card__header button');
    (target ?? fallback?.())?.focus();
  });
}
