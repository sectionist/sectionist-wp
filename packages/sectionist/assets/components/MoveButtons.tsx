import { announce } from './focus';
import { IconButton } from './Icon';

/** Keyboard alternative to drag & drop. Keeps the focus on the moved item afterwards. */
export function MoveButtons(props: {
  id: string;
  upLabel: string;
  downLabel: string;
  first: boolean;
  last: boolean;
  onMove: (offset: -1 | 1) => void;
}) {
  const move = (offset: -1 | 1) => {
    props.onMove(offset);
    announce(offset === -1 ? props.upLabel : props.downLabel);
    const want = offset === -1 ? 'up' : 'down';
    requestAnimationFrame(() => {
      const card = document.querySelector(`[data-flip-id="${CSS.escape(props.id)}"]`);
      const pick = (dir: string) => card?.querySelector<HTMLButtonElement>(`:scope > .sectionist-card__header [data-move="${dir}"]`);
      const target = pick(want);
      (target && !target.disabled ? target : pick(want === 'up' ? 'down' : 'up'))?.focus();
    });
  };
  return (
    <>
      <IconButton icon="arrow-up-alt2" label={props.upLabel} disabled={props.first} onClick={() => move(-1)} move="up" />
      <IconButton icon="arrow-down-alt2" label={props.downLabel} disabled={props.last} onClick={() => move(1)} move="down" />
    </>
  );
}
