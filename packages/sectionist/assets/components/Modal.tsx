import type { ComponentChildren } from 'preact';
import { IconButton } from './Icon';

export function Modal(props: {
  title: string;
  closeLabel: string;
  onClose: () => void;
  small?: boolean;
  alert?: boolean;
  children: ComponentChildren;
}) {
  return (
    <div class="sectionist-modal" onClick={(e) => e.target === e.currentTarget && props.onClose()} onKeyDown={(e) => e.key === 'Escape' && props.onClose()}>
      <div
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
