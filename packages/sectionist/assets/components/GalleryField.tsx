import { useRef, useState } from 'preact/hooks';
import { Collapsible } from './Collapsible';
import { Field, TextInput } from './Field';
import { IconButton } from './Icon';
import { useDragReorder } from './useDragReorder';

interface GalleryImage {
  id: number;
  caption: string;
}

interface Attachment {
  id: number;
  attributes: { sizes?: Record<string, { url: string }>; url: string };
  fetch: () => Promise<unknown>;
  toJSON: () => { sizes?: Record<string, { url: string }>; url: string };
}

interface MultiFrame {
  on: (event: string, cb: () => void) => void;
  open: () => void;
  state: () => { get: (key: string) => { map: <T>(cb: (a: Attachment) => T) => T[] } };
}

interface WpMedia {
  (options: { library: { type: string }; multiple: boolean }): MultiFrame;
  attachment: (id: number) => Attachment;
}

const media = () => (window as unknown as { wp: { media: WpMedia } }).wp.media;

const thumbOf = (id: number): string => {
  const a = media().attachment(id);
  if (!a.attributes.url) {
    a.fetch();
    return '';
  }
  const j = a.toJSON();
  return j.sizes?.thumbnail?.url ?? j.url;
};

/** Gallery of media library images. Each image is a sortable card with a caption. */
export function GalleryField(props: { id: string; value: unknown; labels: Record<string, string>; onInput: (v: GalleryImage[]) => void }) {
  const t = props.labels;
  const images: GalleryImage[] = Array.isArray(props.value) ? (props.value as GalleryImage[]) : [];
  const keys = useRef<string[]>([]);
  const counter = useRef(0);
  while (keys.current.length < images.length) keys.current.push(`g${counter.current++}`);
  keys.current.length = images.length;
  const [open, setOpen] = useState<string[]>([]);
  const [, refresh] = useState(0);

  const commit = (next: GalleryImage[], nextKeys: string[]) => {
    keys.current = nextKeys;
    props.onInput(next);
  };
  const reorder = (id: string, before: string) => {
    const from = keys.current.indexOf(id);
    const target = keys.current.indexOf(before);
    if (from < 0 || target < 0 || from === target) return;
    const next = [...images];
    const nextKeys = [...keys.current];
    const [image] = next.splice(from, 1);
    const [key] = nextKeys.splice(from, 1);
    next.splice(target, 0, image);
    nextKeys.splice(target, 0, key);
    commit(next, nextKeys);
  };
  const drag = useDragReorder(reorder);
  const toggle = (key: string) => setOpen(open.includes(key) ? open.filter((k) => k !== key) : [...open, key]);

  const choose = () => {
    const frame = media()({ library: { type: 'image' }, multiple: true });
    frame.on('select', () => {
      const added = frame
        .state()
        .get('selection')
        .map((a) => a.id)
        .filter((id) => !images.some((i) => i.id === id));
      commit(
        [...images, ...added.map((id) => ({ id, caption: '' }))],
        [...keys.current, ...added.map(() => `g${counter.current++}`)],
      );
      setTimeout(() => refresh((n) => n + 1), 300);
    });
    frame.open();
  };

  return (
    <div class="sectionist-list sectionist-gallery">
      {images.map((image, index) => {
        const key = keys.current[index];
        const src = thumbOf(image.id);
        return (
          <Collapsible
            key={key}
            id={key}
            open={open.includes(key)}
            onToggle={() => toggle(key)}
            title={image.caption.trim() !== '' ? image.caption : `${t.galleryImage} ${index + 1}`}
            toggleLabel={t.toggle}
            dragLabel={t.drag}
            dragging={drag.isDragging(key)}
            dropTarget={drag.isDropTarget(key)}
            {...drag.handlers(key)}
            actions={
              <IconButton
                icon="trash"
                label={t.galleryRemove}
                danger
                onClick={() =>
                  commit(
                    images.filter((_, i) => i !== index),
                    keys.current.filter((_, i) => i !== index),
                  )
                }
              />
            }
          >
            {src !== '' && <img class="sectionist-image-field__preview" src={src} alt="" />}
            <Field id={`${props.id}-${key}-caption`} label={t.galleryCaption}>
              <TextInput
                id={`${props.id}-${key}-caption`}
                value={image.caption}
                onInput={(caption) => props.onInput(images.map((x, i) => (i === index ? { ...x, caption } : x)))}
              />
            </Field>
          </Collapsible>
        );
      })}
      <button type="button" class="button" onClick={choose}>
        {t.galleryAdd}
      </button>
    </div>
  );
}
