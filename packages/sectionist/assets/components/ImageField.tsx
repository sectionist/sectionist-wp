import { useEffect, useState } from 'preact/hooks';

interface MediaAttachment {
  id: number;
  attributes: { sizes?: Record<string, { url: string }>; url: string };
  fetch: () => Promise<unknown>;
  toJSON: () => { id: number; sizes?: Record<string, { url: string }>; url: string };
}

interface MediaFrame {
  on: (event: string, cb: () => void) => void;
  open: () => void;
  state: () => { get: (key: string) => { first: () => MediaAttachment } };
}

interface WpMedia {
  (options: { library: { type: string }; multiple: boolean }): MediaFrame;
  attachment: (id: number) => MediaAttachment;
}

const media = () => (window as unknown as { wp: { media: WpMedia } }).wp.media;

const thumb = (a: { sizes?: Record<string, { url: string }>; url: string }) => a.sizes?.medium?.url ?? a.sizes?.thumbnail?.url ?? a.url;

export function ImageField(props: { value: number; labels: Record<string, string>; onInput: (id: number) => void }) {
  const { value, labels: t } = props;
  const [url, setUrl] = useState('');

  useEffect(() => {
    if (!value) {
      setUrl('');
      return;
    }
    const attachment = media().attachment(value);
    const show = () => setUrl(thumb(attachment.toJSON()));
    if (attachment.attributes.url) show();
    else attachment.fetch().then(show);
  }, [value]);

  const choose = () => {
    const frame = media()({ library: { type: 'image' }, multiple: false });
    frame.on('select', () => {
      const selected = frame.state().get('selection').first();
      setUrl(thumb(selected.toJSON()));
      props.onInput(selected.id);
    });
    frame.open();
  };

  return (
    <div class="sectionist-image-field">
      {value > 0 && url !== '' && <img class="sectionist-image-field__preview" src={url} alt="" />}
      <div class="sectionist-image-field__actions">
        <button type="button" class="button" onClick={choose}>
          {value > 0 ? t.imageReplace : t.imageChoose}
        </button>
        {value > 0 && (
          <button type="button" class="button-link-delete" onClick={() => props.onInput(0)}>
            {t.imageRemove}
          </button>
        )}
      </div>
    </div>
  );
}
