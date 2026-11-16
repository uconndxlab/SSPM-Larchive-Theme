document.addEventListener('DOMContentLoaded', () => {
  const player = document.getElementById('story-player');
  const status = document.getElementById('playback-status');
  const slider = document.getElementById('audio-dur-slider');
  const waveform = document.querySelector('.story-audio');
  const timeline = document.getElementById('audio-timeline');
  const resizeTimeline = () => {
    if (!timeline) return;
    const width = window.innerWidth;
    timeline.setAttribute('width', width > 1600 ? '800' : width > 900 ? '750' : width > 800 ? '650' : width > 650 ? '600' : width > 550 ? '450' : '400');
  };
  resizeTimeline();
  window.addEventListener('resize', resizeTimeline);
  const play = document.querySelector('.audio-toggle');
  const back = document.querySelector('.audio-reverse');
  const forward = document.querySelector('.audio-forward');
  const format = seconds => `${Math.floor(seconds / 60)}:${String(Math.floor(seconds % 60)).padStart(2, '0')}`;
  const seekable = () => player && Number.isFinite(player.duration) && player.duration > 0 && player.dataset.available === '1';
  const setState = () => {
    if (!play) return;
    play.setAttribute('aria-pressed', String(!player.paused));
    play.setAttribute('aria-label', player.paused ? 'Play' : 'Pause');
    play.querySelector('.audio-toggle-icon').innerHTML = `<i class="bi bi-${player.paused ? 'play' : 'pause'}-circle fs-head"></i>`;
  };
  const update = () => {
    const ready = seekable();
    for (const el of [slider, back, forward]) if (el) el.disabled = !ready;
    if (slider) slider.value = ready ? player.currentTime / player.duration * 100 : 0;
    if (waveform) waveform.style.setProperty('--overlay-prog', `${slider?.value || 0}%`);
    const current = document.querySelector('.audio-time.curr'), full = document.querySelector('.audio-time.full');
    if (current) current.textContent = format(player.currentTime || 0);
    if (full) full.textContent = ready ? format(player.duration) : '—';
  };
  let recordingVersion = 0;
  async function start() {
    if (!player || player.dataset.available !== '1') return;
    const version = recordingVersion;
    try { await player.play(); if (version === recordingVersion) status.textContent = ''; }
    catch {
      if (version !== recordingVersion) return;
      status.textContent = 'Playback could not start. Try again or download the original recording.'; setState();
    }
  }
  const seek = seconds => { if (seekable()) player.currentTime = Math.max(0, Math.min(player.duration, seconds)); };
  if (player) {
    for (const event of ['loadedmetadata', 'durationchange', 'timeupdate', 'emptied']) player.addEventListener(event, update);
    for (const event of ['play', 'pause', 'ended']) player.addEventListener(event, setState);
    player.addEventListener('error', () => {
      recordingVersion++;
      player.dataset.available = '0';
      status.textContent = 'The recording could not be loaded. Its original file may be missing or unsupported.';
      if (play) play.disabled = true;
      update(); setState();
    });
    play?.addEventListener('click', () => { if (player.paused) start(); else player.pause(); });
    slider?.addEventListener('input', () => seek(Number(slider.value) / 100 * player.duration));
    if (timeline && waveform) {
      const timelinePercent = event => {
        const bounds = timeline.getBoundingClientRect();
        return Math.max(0, Math.min(100, (event.clientX - bounds.left) / bounds.width * 100));
      };
      timeline.addEventListener('pointermove', event => waveform.style.setProperty('--overlay-prog', `${timelinePercent(event)}%`));
      timeline.addEventListener('pointerleave', update);
      timeline.addEventListener('pointerdown', event => seek(timelinePercent(event) / 100 * player.duration));
    }
    back?.addEventListener('click', () => seek(player.currentTime - 30));
    forward?.addEventListener('click', () => seek(player.currentTime + 30));
    document.getElementById('recording-select')?.addEventListener('change', e => {
      recordingVersion++;
      player.pause();
      const option = e.target.selectedOptions[0];
      player.dataset.available = option.dataset.available;
      if (option.dataset.available === '1') player.src = option.value;
      else player.removeAttribute('src');
      if (play) play.disabled = option.dataset.available !== '1';
      status.textContent = option.dataset.available === '1' ? '' : 'The original recording is missing.';
      player.load(); update(); setState();
    });
    document.querySelectorAll('[data-start]').forEach(segment => segment.addEventListener('click', () => {
      if (segment.dataset.start !== '' && seekable()) { seek(Number(segment.dataset.start)); start(); }
    }));
    update();
  }
  const tabs = [...document.querySelectorAll('.selections [role="tab"]')];
  const panels = [...document.querySelectorAll('.story-content [role="tabpanel"]')];
  tabs.forEach((tab, i) => {
    tab.id = `story-tab-${i}`; tab.setAttribute('aria-controls', `story-panel-${i}`);
    panels[i].id = `story-panel-${i}`; panels[i].setAttribute('aria-labelledby', tab.id);
  });
  function select(index) {
    tabs.forEach((tab, i) => { tab.classList.toggle('selected', i === index); tab.setAttribute('aria-selected', String(i === index)); tab.tabIndex = i === index ? 0 : -1; });
    panels.forEach((panel, i) => { panel.classList.toggle('selected', i === index); panel.hidden = i !== index; });
  }
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => select(index));
    tab.addEventListener('keydown', e => {
      if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) {
        e.preventDefault();
        const next = e.key === 'Home' ? 0 : e.key === 'End' ? tabs.length - 1 : (index + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
        select(next); tabs[next].focus();
      }
    });
  });
  select(0);
});
