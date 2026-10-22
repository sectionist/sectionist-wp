import type { ComponentChildren } from 'preact';
import { useEffect, useRef } from 'preact/hooks';
import { IconButton } from './Icon';

export function Modal(props: {
  title: string;
  closeLabel: string;
  onClose: () => void;
  small?: boolean;
  alert?: boolean;
  children: ComponentChildren;
}) {
  const dialog = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const previous = document.activeElement as HTMLElement | null;
    if (dialog.current && !dialog.current.contains(document.activeElement)) {
      dialog.current.focus();
    }
    return () => previous?.focus?.();
  }, []);

  const onKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      e.stopPropagation();
      props.onClose();
    }
  };

  return (
    <div class="sectionist-modal" onClick={(e) => e.target === e.currentTarget && props.onClose()} onKeyDown={onKeyDown}>
      <div
        ref={dialog}
        tabIndex={-1}
        class={'sectionist-modal__dialog' + (props.small ? ' sectionist-modal__dialog--small' : '')}
        role={props.alert ? 'alertdialog' : 'dialog'}
        aria-modal="true"
        aria-label={props.title}
      >
        <div class="sectionist-modal__head">
          <strong>{props.title}</strong>
          <IconButton icon="no-alt" label={props.closeLabel} onClick={props.onClose} />
        </div>
        {props.children}
      </div>
    </div>
  );
}
