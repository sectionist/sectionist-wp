import type { TypeDef } from '../types';
import { Icon } from './Icon';
import { Modal } from './Modal';

export function TypeModal(props: { title: string; closeLabel: string; types: TypeDef[]; onPick: (type: TypeDef) => void; onClose: () => void }) {
  return (
    <Modal title={props.title} closeLabel={props.closeLabel} onClose={props.onClose}>
      <ul class="sectionist-modal__list">
        {[...props.types].sort((a, b) => Number(b.name === 'hero') - Number(a.name === 'hero')).map((type) => (
          <li>
            <button type="button" class="sectionist-type-card" onClick={() => props.onPick(type)}>
              <Icon name={type.icon} />
              <span class="sectionist-type-card__text">
                <strong>{type.label}</strong>
                <span>{type.description}</span>
              </span>
            </button>
          </li>
        ))}
      </ul>
    </Modal>
  );
}
