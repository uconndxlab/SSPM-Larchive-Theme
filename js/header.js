

document.addEventListener('DOMContentLoaded', () => {
  const hamburgIcon = document.querySelector('.hamburg-wrap');
  const hamburgPopup = document.querySelector('.hamburg-popup');
  if (!hamburgIcon || !hamburgPopup) return;

  const links = Array.from(hamburgPopup.querySelectorAll('a, button'));
  const setOpen = (open) => {
    hamburgPopup.classList.toggle('open', open);
    hamburgIcon.setAttribute('aria-expanded', String(open));
    links.forEach(link => { link.tabIndex = open ? 0 : -1 });
  };

  hamburgIcon.addEventListener('click', () => {
    setOpen(!hamburgPopup.classList.contains('open'));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && hamburgPopup.classList.contains('open')) {
      setOpen(false);
      hamburgIcon.focus();
    }
  });

  document.addEventListener('pointerdown', (event) => {
    if (!hamburgPopup.contains(event.target) && !hamburgIcon.contains(event.target)) {
      setOpen(false);
    }
  });
})
