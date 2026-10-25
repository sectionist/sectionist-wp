export function Icon(props: { name: string }) {
  return <span class={'dashicons dashicons-' + props.name} aria-hidden="true" />;
}

export function IconButton(props: {
  icon: string;
  label: string;
  onClick: () => void;
  disabled?: boolean;
  danger?: boolean;
  expanded?: boolean;
  move?: 'up' | 'down';
  controls?: string;
  pressed?: boolean;
}) {
  return (
    <button
      type="button"
      class={'sectionist-icon-button' + (props.danger ? ' is-danger' : '')}
      title={props.label}
      aria-label={props.label}
      aria-expanded={props.expanded}
      aria-controls={props.controls}
      aria-pressed={props.pressed}
      data-move={props.move}
      disabled={props.disabled}
      onClick={props.onClick}
    >
      <Icon name={props.icon} />
    </button>
  );
}
