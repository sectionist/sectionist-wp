import type { Section, TypeDef } from './types';

export type Action =
  | { kind: 'add'; type: TypeDef }
  | { kind: 'remove'; id: string }
  | { kind: 'reorder'; id: string; before: string }
  | { kind: 'move'; id: string; offset: -1 | 1 }
  | { kind: 'update'; id: string; field: string; value: unknown }
  | { kind: 'active'; id: string; value: boolean }
  | { kind: 'background'; id: string; value: string };

const newId = (): string => Math.random().toString(36).slice(2, 10);

/** The hero always comes first, the call to action always last. */
export const isPinnedStart = (section: Section): boolean => section.type === 'hero';
export const isPinnedEnd = (section: Section): boolean => section.type === 'cta';
export const isPinned = (section: Section): boolean => isPinnedStart(section) || isPinnedEnd(section);

export function pinEdges(sections: Section[]): Section[] {
  return [...sections.filter(isPinnedStart), ...sections.filter((s) => !isPinned(s)), ...sections.filter(isPinnedEnd)];
}

export function reducer(sections: Section[], action: Action): Section[] {
  return pinEdges(apply(sections, action));
}

function apply(sections: Section[], action: Action): Section[] {
  switch (action.kind) {
    case 'add': {
      const added = { id: newId(), type: action.type.name, active: true, background: '', data: { ...action.type.defaults } };
      return [...sections, added];
    }
    case 'remove':
      return sections.filter((s) => s.id !== action.id);
    case 'reorder': {
      const item = sections.find((s) => s.id === action.id);
      if (!item || action.id === action.before) return sections;
      const from = sections.indexOf(item);
      const rest = sections.filter((s) => s.id !== action.id);
      const at = rest.findIndex((s) => s.id === action.before);
      if (at < 0) return sections;
      // Dragging downwards drops the item after the target, upwards before it.
      rest.splice(from < sections.findIndex((s) => s.id === action.before) ? at + 1 : at, 0, item);
      return rest;
    }
    case 'move': {
      const index = sections.findIndex((s) => s.id === action.id);
      const target = index + action.offset;
      if (index < 0 || target < 0 || target >= sections.length) return sections;
      const next = [...sections];
      [next[index], next[target]] = [next[target], next[index]];
      return next;
    }
    case 'active':
      return sections.map((s) => (s.id === action.id ? { ...s, active: action.value } : s));
    case 'background':
      return sections.map((s) => (s.id === action.id ? { ...s, background: action.value } : s));
    case 'update':
      return sections.map((s) =>
        s.id === action.id ? { ...s, data: { ...s.data, [action.field]: action.value } } : s,
      );
  }
}
