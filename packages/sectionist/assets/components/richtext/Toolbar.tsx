import type { Editor } from '@tiptap/core';
import { Icon } from '../Icon';
import { normalizeUrl } from './extensions';

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

const LEVELS = [1, 2, 3, 4, 5, 6] as const;

export function Toolbar(props: { editor: Editor; labels: Record<string, string> }) {
  const { editor, labels: t } = props;
  const chain = () => editor.chain().focus();
  const level = LEVELS.find((l) => editor.isActive('heading', { level: l })) ?? 0;
  const look = String(editor.getAttributes('paragraph').look ?? '');

  const setBlock = (value: string) => {
    if (value === 'p') chain().setParagraph().run();
    else chain().setHeading({ level: Number(value) as (typeof LEVELS)[number] }).run();
  };

  const setLink = () => {
    const url = window.prompt(t.rtLinkPrompt, String(editor.getAttributes('link').href ?? ''));
    if (url === null) return;
    if (url.trim() === '') chain().extendMarkRange('link').unsetLink().run();
    else chain().extendMarkRange('link').setLink({ href: normalizeUrl(url) }).run();
  };

  return (
    <div class="sectionist-rt__toolbar" role="toolbar">
      <select aria-label={t.rtBlock} value={level ? String(level) : 'p'} onChange={(e) => setBlock(e.currentTarget.value)}>
        <option value="p">{t.rtParagraph}</option>
        {LEVELS.map((l) => (
          <option value={String(l)}>{`${t.rtHeading} ${l}`}</option>
        ))}
      </select>
      <select
        aria-label={t.rtLook}
        disabled={level !== 0}
        value={level === 0 ? look : ''}
        onChange={(e) => chain().updateAttributes('paragraph', { look: e.currentTarget.value || null }).run()}
      >
        <option value="">{t.rtLookNone}</option>
        {LEVELS.map((l) => (
          <option value={String(l)}>{`${t.rtLook} h${l}`}</option>
        ))}
      </select>
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
  );
}
