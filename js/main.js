document.addEventListener('DOMContentLoaded', () => {
  const data = document.getElementById('archive-data');
  if (!data) return;
  const items = JSON.parse(data.textContent);
  const list = document.querySelector('.collection-ul');
  const search = document.getElementById('collection-search');
  const min = document.getElementById('dur-slider-min');
  const max = document.getElementById('dur-slider-max');
  const categories = [...document.querySelectorAll('.category:not(.languages) input')];
  const languages = [...document.querySelectorAll('.languages input')];
  let durationActive = false;
  const selected = checks => checks.filter(c => c.checked).map(c => c.value);
  const element = (tag, className, text) => {
    const el = document.createElement(tag);
    el.className = className;
    if (text != null) el.textContent = text;
    return el;
  };
  function render() {
    const cats = selected(categories), langs = selected(languages);
    const query = search.value.trim().toLocaleLowerCase();
    const filtered = items.filter(item =>
      [item.title, item.description, ...item.subjects].join(' ').toLocaleLowerCase().includes(query) &&
      (!cats.length || item.categories.some(c => cats.includes(c))) &&
      (!langs.length || item.languages.some(l => langs.includes(l))) &&
      (!durationActive || (item.duration !== null && item.duration >= Number(min.value) && item.duration <= Number(max.value)))
    );
    list.replaceChildren();
    for (const item of filtered) {
      const li = element('li', 'collections-item rounded-1 overflow-hidden pointer');
      li.classList.toggle('no-image', !item.image);
      if (item.image) {
        const img = element('img', 'item-img');
        img.src = item.image; img.alt = item.title;
        img.addEventListener('error', () => { li.classList.add('no-image'); img.replaceWith(element('div', 'item-img media-placeholder', 'No image available')); }, {once: true});
        li.append(img);
      } else li.append(element('div', 'item-img media-placeholder', 'No image available'));
      const heading = element('h3', 'item-title grotesk-mono-bold fs-lg');
      const link = element('a', 'text-reset', item.title); link.href = item.url; heading.append(link); li.append(heading);
      li.append(element('h4', 'item-short-title grotesk-mono-light fs-sm', item.collection));
      li.append(element('p', 'item-description fs-body my-3', item.description));
      li.append(element('div', 'item-language d-flex gap-2', item.languages.join(', ')));
      const metadata = element('div', 'item-meta d-flex gap-3');
      if (item.date) metadata.append(element('span', 'fs-body', item.date));
      if (item.duration !== null) metadata.append(element('span', 'fs-body', `${Math.round(item.duration * 10) / 10} MIN`));
      li.append(metadata);
      li.append(document.createElement('hr'));
      li.addEventListener('click', e => { if (!e.target.closest('a')) window.location.assign(item.url); });
      list.append(li);
    }
    if (!filtered.length) list.append(element('li', 'collection-empty p-4', 'No stories match these filters.'));
    document.getElementById('collection-count').textContent = `${filtered.length} OUT OF ${items.length} STORIES`;
    updateSlider();
  }
  function updateSlider() {
    for (const [slider, cls] of [[min, 'min'], [max, 'max']]) {
      const label = document.querySelector(`.dur-slider-label.${cls}`);
      label.textContent = `${slider.value} MIN`;
      label.style.left = `${Number(slider.value) / Number(slider.max) * 100}%`;
      label.style.transform = 'translateX(-50%)';
    }
    const a = Number(min.value) / Number(min.max) * 100, b = Number(max.value) / Number(max.max) * 100;
    document.querySelector('.slider-track').style.background = `linear-gradient(to right, #D5D8E5 ${a}%, #360078 ${a}%, #360078 ${b}%, #D5D8E5 ${b}%)`;
  }
  function clearDuration() { min.value = 0; max.value = max.max; durationActive = false; }
  function clearChecks(checks) { checks.forEach(c => { c.checked = false; }); }
  function reset() { search.value = ''; clearChecks([...categories, ...languages]); clearDuration(); render(); }
  min.addEventListener('input', () => { min.value = Math.min(Number(min.value), Number(max.value)); durationActive = true; render(); });
  max.addEventListener('input', () => { max.value = Math.max(Number(max.value), Number(min.value)); durationActive = true; render(); });
  [...categories, ...languages].forEach(c => c.addEventListener('change', render));
  search.addEventListener('input', render);
  document.getElementById('collection-search-form').addEventListener('submit', e => { e.preventDefault(); render(); });
  document.getElementById('duration-clear').addEventListener('click', () => { clearDuration(); render(); });
  document.getElementById('category-clear').addEventListener('click', () => { clearChecks(categories); render(); });
  document.getElementById('language-clear').addEventListener('click', () => { clearChecks(languages); render(); });
  document.querySelector('.clear-all').addEventListener('click', reset);
  document.querySelector('.refresh').addEventListener('click', reset);
  for (const mode of ['grid', 'list']) {
    const control = document.getElementById(mode);
    control.tabIndex = 0; control.setAttribute('role', 'button');
    const switchView = () => {
      list.classList.toggle('grid', mode === 'grid'); list.classList.toggle('list', mode === 'list');
      document.querySelector('.view-select').classList.toggle('select-grid', mode === 'grid');
      for (const other of ['grid', 'list']) document.getElementById(other).classList.toggle('active', other === mode);
    };
    control.addEventListener('click', switchView);
    control.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); switchView(); } });
  }
  document.querySelectorAll('.portrait').forEach(p => p.addEventListener('pointerenter', () => {
    document.querySelectorAll('.portrait').forEach(other => other.classList.toggle('active', other === p));
  }));
  render();
});
