import { useState } from 'preact/hooks';
import { Modal } from '../Modal';
import { LINK_TYPES, buildHref, splitHref, type LinkType } from './extensions';

const PREFIX: Record<LinkType, string> = { https: 'https://', mailto: 'mailto:', tel: 'tel:' };

export function LinkModal(props: {
  initialType: LinkType;
  initialRest: string;
  initialText: string;
  hasSelection: boolean;
  isEdit: boolean;
  labels: Record<string, string>;
  onApply: (result: { href: string; text: string }) => void;
  onRemove: () => void;
  onClose: () => void;
}) {
  const t = props.labels;
  const [type, setType] = useState<LinkType>(props.initialType);
  const [rest, setRest] = useState(props.initialRest);
  const [text, setText] = useState(props.initialText);

  const href = buildHref(type, rest);
  const valid = href !== '' && (props.hasSelection || text.trim() !== '');

  const onAddress = (value: string) => {
    if (/^(https?:\/\/|mailto:|tel:)/i.test(value.trim())) {
      const parts = splitHref(value);
      setType(parts.type);
      setRest(parts.rest);
    } else {
      setRest(value);
    }
  };

  const submit = () => {
    if (valid) props.onApply({ href, text: props.hasSelection ? text : text.trim() });
  };

  const onEnter = (e: KeyboardEvent) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    submit();
  };

  return (
    <Modal title={t.rtLinkTitle} closeLabel={t.cancel} onClose={props.onClose} small>
      <div onKeyDown={onEnter}>
        <div class="sectionist-modal__body">
          <div class="sectionist-field">
            <span class="sectionist-field__label" id="sectionist-link-type">{t.rtLinkType}</span>
            <div class="sectionist-btn-group" role="group" aria-labelledby="sectionist-link-type">
              {LINK_TYPES.map((l) => (
                <button type="button" class={'button' + (l === type ? ' is-active' : '')} aria-pressed={l === type} onClick={() => setType(l)}>
                  {l}
                </button>
              ))}
            </div>
          </div>
          <div class="sectionist-field">
            <label for="sectionist-link-address">{t.rtLinkAddress}</label>
            <div class="sectionist-input-group">
              <span class="sectionist-input-group__prefix">{PREFIX[type]}</span>
              <input id="sectionist-link-address" type="text" value={rest} onInput={(e) => onAddress(e.currentTarget.value)} />
            </div>
          </div>
          <div class="sectionist-field">
            <label for="sectionist-link-text">{t.rtLinkText}</label>
            <input id="sectionist-link-text" type="text" value={text} onInput={(e) => setText(e.currentTarget.value)} />
          </div>
        </div>
        <div class="sectionist-modal__footer sectionist-modal__footer--split">
          {props.isEdit && (
            <button type="button" class="button sectionist-modal__remove" onClick={props.onRemove}>
              {t.rtLinkRemove}
            </button>
          )}
          <button type="button" class="button" onClick={props.onClose}>
            {t.cancel}
          </button>
          <button type="button" class="button button-primary" disabled={!valid} onClick={submit}>
            {t.rtLinkApply}
          </button>
        </div>
      </div>
    </Modal>
  );
}
