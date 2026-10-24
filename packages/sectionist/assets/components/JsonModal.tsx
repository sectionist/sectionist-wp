import { useState } from 'preact/hooks';
import { Modal } from './Modal';

export function JsonModal(props: {
  title: string;
  json: string;
  copyLabel: string;
  copiedLabel: string;
  closeLabel: string;
  onClose: () => void;
}) {
  const [copied, setCopied] = useState(false);
  const copy = () => {
    navigator.clipboard
      ?.writeText(props.json)
      .then(() => setCopied(true))
      .catch(() => setCopied(false));
  };

  return (
    <Modal title={props.title} closeLabel={props.closeLabel} onClose={props.onClose}>
      <pre class="sectionist-json" tabIndex={0}>{props.json}</pre>
      <div class="sectionist-modal__footer">
        <button type="button" class="button" onClick={copy}>{copied ? props.copiedLabel : props.copyLabel}</button>
        <button type="button" class="button button-primary" autofocus onClick={props.onClose}>{props.closeLabel}</button>
      </div>
    </Modal>
  );
}
