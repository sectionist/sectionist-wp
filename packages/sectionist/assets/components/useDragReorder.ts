import { useState } from 'preact/hooks';

/** Native HTML5 drag & drop state for a sortable list of ids. */
export function useDragReorder(onReorder: (id: string, before: string) => void) {
  const [dragId, setDragId] = useState('');
  const [overId, setOverId] = useState('');
  const reset = () => {
    setDragId('');
    setOverId('');
  };

  return {
    isDragging: (id: string) => dragId === id,
    isDropTarget: (id: string) => !!dragId && overId === id && dragId !== id,
    handlers: (id: string) => ({
      onDragStart: (e: DragEvent) => {
        setDragId(id);
        const card = (e.currentTarget as HTMLElement).closest('.sectionist-card, [data-drag-item]');
        if (card && e.dataTransfer) {
          e.dataTransfer.effectAllowed = 'move';
          e.dataTransfer.setData('text/plain', id);
          e.dataTransfer.setDragImage(card, 16, 16);
        }
      },
      onDragOver: (e: DragEvent) => {
        if (!dragId) return;
        e.preventDefault();
        setOverId(id);
      },
      onDrop: (e: DragEvent) => {
        e.preventDefault();
        if (dragId) onReorder(dragId, id);
        reset();
      },
      onDragEnd: reset,
    }),
  };
}
