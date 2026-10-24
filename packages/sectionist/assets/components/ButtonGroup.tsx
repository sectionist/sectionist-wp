export function ButtonGroup(props: {
  options: { value: string; label: string }[];
  value: string;
  onInput: (value: string) => void;
  label?: string;
  labelledBy?: string;
}) {
  return (
    <div class="sectionist-btn-group" role="group" aria-label={props.label} aria-labelledby={props.labelledBy}>
      {props.options.map((o) => (
        <button type="button" class={'button' + (o.value === props.value ? ' is-active' : '')} aria-pressed={o.value === props.value} onClick={() => props.onInput(o.value)}>
          {o.label}
        </button>
      ))}
    </div>
  );
}
