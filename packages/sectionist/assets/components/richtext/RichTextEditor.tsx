import { Editor } from '@tiptap/core';
import { useEffect, useRef, useState } from 'preact/hooks';
import { createExtensions } from './extensions';
import { Toolbar } from './Toolbar';

/**
 * Self-contained rich-text field. Knows nothing about sections: give it an HTML string
 * and the `rt*` labels, get sanitised-by-whitelist HTML back through `onInput`.
 */
export function RichTextEditor(props: { id: string; value: string; label?: string; labels: Record<string, string>; onInput: (html: string) => void }) {
  const host = useRef<HTMLDivElement>(null);
  const [editor, setEditor] = useState<Editor | null>(null);
  const [, refresh] = useState(0);
  const onInput = useRef(props.onInput);
  onInput.current = props.onInput;

  useEffect(() => {
    const instance = new Editor({
      element: host.current!,
      content: props.value,
      extensions: createExtensions(),
      editorProps: {
        attributes: {
          id: props.id,
          class: 'sectionist-rt__content',
          role: 'textbox',
          'aria-multiline': 'true',
          'aria-label': props.label ?? props.labels.rtLabel ?? 'Rich text',
        },
      },
      onUpdate: ({ editor: e }) => onInput.current(e.isEmpty ? '' : e.getHTML()),
      onTransaction: () => refresh((n) => n + 1),
    });
    setEditor(instance);
    return () => {
      instance.destroy();
      setEditor(null);
    };
  }, []);

  return (
    <div class="sectionist-rt">
      {editor && <Toolbar editor={editor} labels={props.labels} />}
      <div ref={host} />
    </div>
  );
}
