function revealEmbedButtons() {
  document.querySelectorAll('[data-sectionist-embed-load]').forEach(function (button) {
    button.hidden = false;
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', revealEmbedButtons, { once: true });
} else {
  revealEmbedButtons();
}

document.addEventListener('click', function (event) {
  if (!(event.target instanceof Element)) {
    return;
  }

  const button = event.target.closest('[data-sectionist-embed-load]');
  if (!button) {
    return;
  }

  const gate = button.closest('[data-sectionist-embed]');
  const template = gate ? gate.querySelector('template[data-sectionist-embed-template]') : null;
  if (!gate || !template) {
    return;
  }

  event.preventDefault();
  gate.replaceWith(template.content.cloneNode(true));
});
