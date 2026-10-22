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
      link: {
        openOnClick: false,
        autolink: false,
        defaultProtocol: 'https',
        protocols: ['mailto', 'tel'],
        HTMLAttributes: { target: null, rel: null, class: null },
      },
    }),
    Align.configure({ types: ['heading', 'paragraph'], alignments: ALIGNMENTS }),
    ParagraphLook,
  ];
}

/** Supported link types. Plain `http` is intentionally not offered. */
export const LINK_TYPES = ['https', 'mailto', 'tel'] as const;
export type LinkType = (typeof LINK_TYPES)[number];

const SCHEME = /^(https?:\/\/|mailto:|tel:)/i;

/** Split an href into link type and the part after the prefix. `http:` shows as https; relative links are kept raw. */
export function splitHref(href: string): { type: LinkType; rest: string } {
  const value = href.trim();
  const match = SCHEME.exec(value);
  if (!match) return { type: 'https', rest: value };
  const scheme = match[1].toLowerCase();
  const rest = value.slice(match[1].length);
  if (scheme === 'mailto:') return { type: 'mailto', rest };
  if (scheme === 'tel:') return { type: 'tel', rest };
  return { type: 'https', rest };
}

/** Build an href from a type and the user input (a typed prefix is stripped; tel is cleaned). Empty input gives an empty string. */
export function buildHref(type: LinkType, input: string): string {
  let rest = input.trim().replace(SCHEME, '');
  if (type === 'tel') rest = rest.replace(/[\s()-]/g, '');
  if (rest === '') return '';
  if (type === 'https') return /^[/#?]/.test(rest) ? rest : `https://${rest}`;
  return `${type}:${rest}`;
}
