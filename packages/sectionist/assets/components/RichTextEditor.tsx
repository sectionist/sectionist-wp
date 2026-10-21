import { Editor, Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import { useEffect, useRef, useState } from 'preact/hooks';
import { Icon } from './Icon';

const ALIGNMENTS = ['left', 'center', 'right'];

/** Alignment is stored as a class, never as an inline style (the server whitelist drops styles). */
const Align = TextAlign.extend({
  addGlobalAttributes() {
    return [
      {
        types: this.options.types,
        attributes: {
          textAlign: {
            default: null,
            parseHTML: (el: HTMLElement) => ALIGNMENTS.find((a) => el.classList.contains(`has-text-align-${a}`)) ?? null,
            renderHTML: (attrs: Record<string, unknown>) => (attrs.textAlign ? { class: `has-text-align-${attrs.textAlign}` } : {}),
          },
        },
      },
    ];
  },
});

/** A paragraph that looks like heading N (class `as-hN`) without being a heading. */
const ParagraphLook = Extension.create({
  name: 'paragraphLook',
  addGlobalAttributes() {
    return [
      {
        types: ['paragraph'],
        attributes: {
          look: {
            default: null,
            parseHTML: (el: HTMLElement) => /\bas-h([1-6])\b/.exec(el.className)?.[1] ?? null,
            renderHTML: (attrs: Record<string, unknown>) => (attrs.look ? { class: `as-h${attrs.look}` } : {}),
          },
        },
      },
    ];
  },
});

function ToolButton(props: { icon: string; label: string; active?: boolean; disabled?: boolean; onClick: () => void }) {
  return (
    <button
      type="button"
      class={'sectionist-rt__button' + (props.active ? ' is-active' : '')}
      title={props.label}
      aria-label={props.label}
      aria-pressed={props.active ?? false}
      disabled={props.disabled}
      onClick={props.onClick}
    >
      <Icon name={props.icon} />
    </button>
  );
}

export function RichTextEditor(props: { id: string; value: string; labels: Record<string, string>; onInput: (html: string) => void }) {
  const host = useRef<HTMLDivElement>(null);
  const editorRef = useRef<Editor | null>(null);
  const onInput = useRef(props.onInput);
  onInput.current = props.onInput;
  const [, refresh] = useState(0);
  const t = props.labels;

  useEffect(() => {
    const editor = new Editor({
      element: host.current!,
      content: props.value,
      extensions: [
        StarterKit.configure({
          underline: false,
          blockquote: false,
          code: false,
          codeBlock: false,
          horizontalRule: false,
          hardBreak: false,
          link: { openOnClick: false, autolink: false, HTMLAttributes: { target: null, rel: null, class: null } },
        }),
        Align.configure({ types: ['heading', 'paragraph'], alignments: ALIGNMENTS }),
        ParagraphLook,
      ],
      editorProps: { attributes: { id: props.id, class: 'sectionist-rt__content' } },
      onUpdate: ({ editor: e }) => onInput.current(e.isEmpty ? '' : e.getHTML()),
      onTransaction: () => refresh((n) => n + 1),
    });
    editorRef.current = editor;
    refresh((n) => n + 1);
    return () => {
      editor.destroy();
      editorRef.current = null;
    };
  }, []);

  const editor = editorRef.current;
  const chain = () => editor!.chain().focus();
  const level = editor ? ([1, 2, 3, 4, 5, 6].find((l) => editor.isActive('heading', { level: l })) ?? 0) : 0;
  const look = editor ? String(editor.getAttributes('paragraph').look ?? '') : '';

  const setBlock = (value: string) => {
    if (value === 'p') chain().setParagraph().run();
    else chain().setHeading({ level: Number(value) as 1 | 2 | 3 | 4 | 5 | 6 }).run();
  };

  // Prepend https:// to bare hosts such as `www.example.com`; keep schemes, relative paths, anchors and queries.
  const normalizeUrl = (raw: string): string => {
    const value = raw.trim();
    if (/^([a-z][a-z0-9+.-]*:|\/|#|\?)/i.test(value)) return value;
    return `https://${value}`;
  };

  const setLink = () => {
    const url = window.prompt(t.rtLinkPrompt, String(editor!.getAttributes('link').href ?? ''));
    if (url === null) return;
    if (url.trim() === '') chain().extendMarkRange('link').unsetLink().run();
    else chain().extendMarkRange('link').setLink({ href: normalizeUrl(url) }).run();
  };

  return (
    <div class="sectionist-rt">
      {editor && (
        <div class="sectionist-rt__toolbar" role="toolbar">
          <select aria-label={t.rtBlock} value={level ? String(level) : 'p'} onChange={(e) => setBlock(e.currentTarget.value)}>
            <option value="p">{t.rtParagraph}</option>
            {[1, 2, 3, 4, 5, 6].map((l) => (
              <option value={String(l)}>{`${t.rtHeading} ${l}`}</option>
            ))}
          </select>
          {level === 0 && (
            <select
              aria-label={t.rtLook}
              value={look}
              onChange={(e) => chain().updateAttributes('paragraph', { look: e.currentTarget.value || null }).run()}
            >
              <option value="">{t.rtLookNone}</option>
              {[1, 2, 3, 4, 5, 6].map((l) => (
                <option value={String(l)}>{`${t.rtLook} ${t.rtHeading} ${l}`}</option>
              ))}
            </select>
          )}
          <ToolButton icon="editor-bold" label={t.rtBold} active={editor.isActive('bold')} onClick={() => chain().toggleBold().run()} />
          <ToolButton icon="editor-italic" label={t.rtItalic} active={editor.isActive('italic')} onClick={() => chain().toggleItalic().run()} />
          <ToolButton icon="editor-strikethrough" label={t.rtStrike} active={editor.isActive('strike')} onClick={() => chain().toggleStrike().run()} />
          <ToolButton icon="editor-ul" label={t.rtBulletList} active={editor.isActive('bulletList')} onClick={() => chain().toggleBulletList().run()} />
          <ToolButton icon="editor-ol" label={t.rtOrderedList} active={editor.isActive('orderedList')} onClick={() => chain().toggleOrderedList().run()} />
          <ToolButton icon="editor-alignleft" label={t.rtAlignLeft} active={editor.isActive({ textAlign: 'left' })} onClick={() => chain().toggleTextAlign('left').run()} />
          <ToolButton icon="editor-aligncenter" label={t.rtAlignCenter} active={editor.isActive({ textAlign: 'center' })} onClick={() => chain().toggleTextAlign('center').run()} />
          <ToolButton icon="editor-alignright" label={t.rtAlignRight} active={editor.isActive({ textAlign: 'right' })} onClick={() => chain().toggleTextAlign('right').run()} />
          <ToolButton icon="admin-links" label={t.rtLink} active={editor.isActive('link')} onClick={setLink} />
        </div>
      )}
      <div ref={host} />
    </div>
  );
}
