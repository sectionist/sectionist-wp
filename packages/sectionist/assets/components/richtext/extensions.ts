import { Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';

export const ALIGNMENTS = ['left', 'center', 'right'];

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

/** Extensions mirroring the server-side kses whitelist (see `Field::ALLOWED_HTML`). */
export function createExtensions() {
  return [
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
  ];
}

/** Prepend https:// to bare hosts such as `www.example.com`; keep schemes, relative paths, anchors and queries. */
export function normalizeUrl(raw: string): string {
  const value = raw.trim();
  if (/^([a-z][a-z0-9+.-]*:|\/|#|\?)/i.test(value)) return value;
  return `https://${value}`;
}
