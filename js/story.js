document.addEventListener('DOMContentLoaded', () => {
  const audio = document.querySelector('#story-audio');
  const audioWrap = document.querySelector('.story-audio');
  const audioSlider = document.querySelector('#audio-dur-slider');
  const timeline = document.querySelector('#audio-timeline');
  const backButton = document.querySelector('.audio-reverse');
  const playButton = document.querySelector('.audio-toggle');
  const forwardButton = document.querySelector('.audio-forward');
  const currentTime = document.querySelector('.audio-time.curr');
  const fullTime = document.querySelector('.audio-time.full');

  const formatTime = (seconds) => {
    if (!Number.isFinite(seconds)) return '0:00';
    const minutes = Math.floor(seconds / 60);
    const remainder = Math.floor(seconds % 60);
    return `${minutes}:${remainder < 10 ? '0' : ''}${remainder}`;
  };

  const setPlayButton = (playing) => {
    if (!playButton) return;
    playButton.setAttribute('aria-pressed', String(playing));
    playButton.setAttribute('aria-label', playing ? 'Pause' : 'Play');
    playButton.querySelector('.audio-toggle-icon').innerHTML = playing
      ? '<i class="bi bi-pause-circle" style="font-size:52px" aria-hidden="true"></i>'
      : '<i class="bi bi-play-circle" style="font-size:52px" aria-hidden="true"></i>';
  };

  const seekToPercent = (percent) => {
    if (!audio || !Number.isFinite(audio.duration)) return;
    audio.currentTime = Math.max(0, Math.min(1, percent)) * audio.duration;
  };

  const updateWaveformOverlay = (previewPercent = null) => {
    if (!audioWrap) return;

    const percent = previewPercent ?? (
      Number.isFinite(audio?.duration) && audio.duration > 0
        ? (audio.currentTime / audio.duration) * 100
        : 0
    );

    audioWrap.style.setProperty('--overlay-prog', `${percent}%`);
  };

  if (audio && audioSlider) {
    setPlayButton(false);

    const updateDuration = () => {
      if (fullTime) fullTime.textContent = formatTime(audio.duration);
    };

    audio.addEventListener('loadedmetadata', updateDuration);
    audio.addEventListener('durationchange', updateDuration);
    audio.addEventListener('canplay', updateDuration);

    // A media row can exist while its stored file is unavailable. In that
    // case, use the theme's known-good sample instead of leaving a dead player.
    audio.addEventListener('error', () => {
      const fallbackSrc = audio.dataset.fallbackSrc;
      if (!fallbackSrc || audio.dataset.usingFallback === 'true') return;
      audio.dataset.usingFallback = 'true';
      audio.src = fallbackSrc;
      audio.load();
    });

    if (audio.readyState >= HTMLMediaElement.HAVE_METADATA) updateDuration();

    audio.addEventListener('timeupdate', () => {
      const percent = Number.isFinite(audio.duration) && audio.duration > 0
        ? (audio.currentTime / audio.duration) * 100
        : 0;
      audioSlider.value = String(percent);
      updateWaveformOverlay(percent);
      if (currentTime) currentTime.textContent = formatTime(audio.currentTime);
    });

    audio.addEventListener('play', () => setPlayButton(true));
    audio.addEventListener('pause', () => setPlayButton(false));
    audio.addEventListener('ended', () => setPlayButton(false));

    audioSlider.addEventListener('input', () => seekToPercent(Number(audioSlider.value) / 100));
    backButton?.addEventListener('click', () => { audio.currentTime = Math.max(0, audio.currentTime - 30); });
    forwardButton?.addEventListener('click', () => { audio.currentTime = Math.min(audio.duration || 0, audio.currentTime + 30); });
    playButton?.addEventListener('click', () => { audio.paused ? audio.play() : audio.pause(); });

    timeline?.addEventListener('pointerdown', (event) => {
      const bounds = timeline.getBoundingClientRect();
      seekToPercent((event.clientX - bounds.left) / bounds.width);
    });

    // Match the reference player: hover previews the point that would be
    // selected, then returns to the actual playback position on exit.
    timeline?.addEventListener('mousemove', (event) => {
      const bounds = timeline.getBoundingClientRect();
      const percent = ((event.clientX - bounds.left) / bounds.width) * 100;
      updateWaveformOverlay(Math.max(0, Math.min(100, percent)));
    });

    timeline?.addEventListener('mouseleave', () => {
      window.setTimeout(updateWaveformOverlay, 100);
    });

    document.querySelectorAll('.overview-item[data-start]').forEach((segment) => {
      segment.addEventListener('click', () => {
        audio.currentTime = Number(segment.dataset.start || 0);
        audio.play();
      });
    });
  }

  const selectors = Array.from(document.querySelectorAll('.selections [role="tab"]'));
  const panels = Array.from(document.querySelectorAll('.story-content [role="tabpanel"]'));

  selectors.forEach((selector, index) => {
    selector.addEventListener('click', () => {
      selectors.forEach((tab) => {
        const selected = tab === selector;
        tab.classList.toggle('selected', selected);
        tab.setAttribute('aria-selected', String(selected));
      });
      panels.forEach((panel, panelIndex) => panel.classList.toggle('selected', panelIndex === index));
    });
  });
});
