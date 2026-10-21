import { Modal } from './Modal';

export function ConfirmModal(props: {
  title: string;
  text: string;
  confirmLabel: string;
  cancelLabel: string;
  onConfirm: () => void;
  onCancel: () => void;
}) {
  return (
    <Modal title={props.title} closeLabel={props.cancelLabel} onClose={props.onCancel} small alert>
      <p class="sectionist-modal__body">{props.text}</p>
      <div class="sectionist-modal__footer">
        <button type="button" class="button" autofocus onClick={props.onCancel}>{props.cancelLabel}</button>
        <button type="button" class="button button-primary sectionist-button-danger" onClick={props.onConfirm}>
          {props.confirmLabel}
        </button>
      </div>
    </Modal>
  );
}
