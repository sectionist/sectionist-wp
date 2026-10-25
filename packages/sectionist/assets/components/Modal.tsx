import type { ComponentChildren } from 'preact';
import { useEffect, useId, useRef } from 'preact/hooks';
import { IconButton } from './Icon';

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [contenteditable="true"], [tabindex]:not([tabindex="-1"])';

export function Modal(props: {
  title: string;
  closeLabel: string;
  onClose: () => void;
  small?: boolean;
  alert?: boolean;
  children: ComponentChildren;
}) {
  const dialog = useRef<HTMLDivElement>(null);
  const titleId = useId();

  useEffect(() => {
    const previous = document.activeElement as HTMLElement | null;
    if (dialog.current && !dialog.current.contains(document.activeElement)) {
      dialog.current.focus();
    }
    return () => {
      if (previous?.isConnected) previous.focus();
      else document.querySelector<HTMLElement>('.sectionist .sectionist-add button, .sectionist button')?.focus();
    };
  }, []);

  const onKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      e.stopPropagation();
      props.onClose();
      return;
    }
    if (e.key !== 'Tab' || !dialog.current) return;
    const items = Array.from(dialog.current.querySelectorAll<HTMLElement>(FOCUSABLE));
    if (items.length === 0) {
      e.preventDefault();
      dialog.current.focus();
      return;
    }
    const first = items[0];
    const last = items[items.length - 1];
    const current = document.activeElement;
    if (e.shiftKey && (current === first || current === dialog.current)) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && current === last) {
      e.preventDefault();
      first.focus();
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
        aria-labelledby={titleId}
      >
        <div class="sectionist-modal__head">
          <strong id={titleId}>{props.title}</strong>
          <IconButton icon="no-alt" label={props.closeLabel} onClick={props.onClose} />
        </div>
        {props.children}
      </div>
    </div>
  );
}
