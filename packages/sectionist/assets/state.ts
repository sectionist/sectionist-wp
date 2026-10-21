import type { Section, TypeDef } from './types';

export type Action =
  | { kind: 'add'; type: TypeDef }
  | { kind: 'remove'; id: string }
  | { kind: 'move'; id: string; offset: -1 | 1 }
  | { kind: 'update'; id: string; field: string; value: string };

const newId = (): string => Math.random().toString(36).slice(2, 10);

export function reducer(sections: Section[], action: Action): Section[] {
  switch (action.kind) {
    case 'add':
      return [...sections, { id: newId(), type: action.type.name, data: { ...action.type.defaults } }];
    case 'remove':
      return sections.filter((s) => s.id !== action.id);
    case 'move': {
      const index = sections.findIndex((s) => s.id === action.id);
      const target = index + action.offset;
      if (index < 0 || target < 0 || target >= sections.length) return sections;
      const next = [...sections];
      [next[index], next[target]] = [next[target], next[index]];
      return next;
    }
    case 'update':
      return sections.map((s) =>
        s.id === action.id ? { ...s, data: { ...s.data, [action.field]: action.value } } : s,
      );
  }
}
