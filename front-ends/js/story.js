// Initialize audio variables
let audioWrap = document.querySelector('.story-audio');
let audioSlider = document.querySelector('input[type="range"]');
let audioLabel = document.querySelector('label[for="audio-dur-slider"]');
let audioReverse = document.querySelector('.audio-reverse');
let audioForward = document.querySelector('.audio-forward');
let audioPause = document.querySelector('.bi-pause-circle');
let audioPlay = document.querySelector('.bi-play-circle');
let audioCurrTime = document.querySelector('.audio-time.curr');
let audioFullTime = document.querySelector('.audio-time.full');
let audioTimeline = document.querySelector('#audio-timeline')

// Initialize overview variables
let overviewWrap = document.querySelector('.content-overview');
let overviewText = document.querySelector('.content-overview .text-content');
let overviewItems = document.querySelector('.overview-items');

// Initialize selectors
let overviewSelector = document.querySelector('.selections .overview');
let transcriptSelector = document.querySelector('.selections .transcript');
let resourceSelector = document.querySelector('.selections .resources');

// Initialize content selectors
let overviewContent = document.querySelector('.content.overview');
let transcriptContent = document.querySelector('.content.transcript');
let resourceContent = document.querySelector('.content.resources');

/** @type {HTMLAudioElement} */
let storyAudio = document.querySelector('#story-audio')
console.log(storyAudio)

  // On audio load set total duration text
  storyAudio.addEventListener('loadeddata', () => {
    console.log(storyAudio.duration)
    audioFullTime.textContent = `${secToMin(storyAudio.duration)}`;
  })

window.addEventListener('DOMContentLoaded', () => {
  // Ensure DOM elements
  ensureDOMEls()

  // Set content sections to map
  const contentMap = new Map;
  contentMap.set(overviewSelector, overviewContent);
  contentMap.set(transcriptSelector, transcriptContent);
  contentMap.set(resourceSelector, resourceContent);

  // Add listeners for selectors
  contentMap.keys().forEach(key => {
    key.addEventListener('pointerdown', () => {
      const curr = contentMap.keys().find(k => k.classList.contains('selected'))
      contentMap.get(curr).classList.remove('selected')
      curr.classList.remove('selected');

      key.classList.add('selected');
      contentMap.get(key).classList.add('selected');

      if(key === overviewSelector) setOverviewHeight()
    })
  })

  // Set overview items max height
  const setOverviewHeight = () => {
  const textHeight = overviewText.getBoundingClientRect().height;
  overviewItems.style.maxHeight = `${textHeight}px`;
  }

  // If overview is pre-selected set max height
  if(overviewSelector.classList.contains('selected')) setOverviewHeight()





  // On audio time update set overlay slider, and current time text
  storyAudio.addEventListener('timeupdate', () => {
    updateOverlay()
    setSlider()
    audioCurrTime.textContent = secToMin(storyAudio.currentTime)
  })

  // Add pause/play button listener
  audioPause.addEventListener('pointerdown', pauseAudio);
  audioPlay.addEventListener('pointerdown', playAudio);

  // Add reverse/forward listeners
  audioReverse.addEventListener('pointerdown', backThirty);
  audioForward.addEventListener('pointerdown', forwardThirty);

  // Add overlay hover listener
  audioTimeline.addEventListener('mousemove', (e) => {
    const rect = audioTimeline.getBoundingClientRect();
    const percent = (((e.clientX - rect.left) / rect.width) * 100).toFixed(2)
    updateOverlay(percent)
  })

  // Reset overlay on mouse leave after short timeout
  audioTimeline.addEventListener('mouseleave', () => {
    setTimeout(updateOverlay, 100)
  })

  // Set story audio time on overlay click
  audioTimeline.addEventListener('pointerdown', (e) => {
    const rect = audioTimeline.getBoundingClientRect();
    const percent = Math.round((((e.clientX - rect.left) / rect.width) * 100))

    storyAudio.currentTime = (percent / 100) * storyAudio.duration;
  })

})

// Ensure DOM elements
function ensureDOMEls() {
  if(!audioWrap) audioWrap = document.querySelector('.story-audio');
  if(!audioSlider) audioSlider = document.querySelector('.audio-dur-slider');
  if(!audioLabel) audioLabel = document.querySelector('label[for="audio-dur-slider"]');
  if(!audioReverse) audioReverse = document.querySelector('.audio-reverse');
  if(!audioForward) audioForward = document.querySelector('.audio-forward');
  if(!audioPause) audioPause = document.querySelector('.bi-pause-circle');
  if(!audioPlay) audioPlay = document.querySelector('.bi-play-circle');
  if(!audioCurrTime) audioCurrTime = document.querySelector('.audio-time.curr');
  if(!audioFullTime) audioFullTime = document.querySelector('.audio-time.full');
  if(!audioTimeline) audioTimeline = document.querySelector('#audio-timeline');

  if(!overviewWrap) overviewWrap = document.querySelector('.content.overview');
  if(!overviewText) overviewText = document.querySelector('.content.overview .text-content');
  if(!overviewItems) overviewItems = document.querySelector('.overview-items');




}

// Get minutes from seconds
const secToMin = (s) => {
  const secs = Math.floor(s % 60);
  const mins = Math.floor(s / 60);
  return `${mins}:${secs < 10 ? '0' : ''}${secs}`
}

// Play audio
const playAudio = () => {
  audioPlay.classList.toggle('toggled');
  audioPause.classList.toggle('toggled');
  storyAudio.play()
}

// Pause audio
const pauseAudio = () => {
  audioPlay.classList.toggle('toggled');
  audioPause.classList.toggle('toggled');
  storyAudio.pause()
}

// Reverse 30 seconds
const backThirty = () => {
  const curr = storyAudio.currentTime;
  storyAudio.currentTime = curr - 30 >= 0 ? curr - 30 : 0
}

// Forwards 30 seconds
const forwardThirty = () => {
  const curr = storyAudio.currentTime;
  storyAudio.currentTime = curr + 30 <= storyAudio.duration ? curr + 30 : storyAudio.duration;
}

// Update overlay background
const updateOverlay = (hover = null) => {
  if(!hover) {
  const percent = ((storyAudio.currentTime / storyAudio.duration) * 100).toFixed(2)
  audioWrap.style.setProperty('--overlay-prog', `${percent}%`)
  } else {
    audioWrap.style.setProperty('--overlay-prog', `${hover}%`)
  }
}

// Set audio slider
const setSlider = () => {
  const percent = ((storyAudio.currentTime / storyAudio.duration) * 100).toFixed(2)
  audioSlider.value = Math.floor(percent / .25) * .25;
}

// On slider move set time
const sliderMove = () => {
  storyAudio.currentTime = Math.floor((audioSlider.value / 100) * storyAudio.duration)
}


window.addEventListener('resize', () => {
  const w = window.innerWidth;
  let newWidth;
  if(w > 1600) { newWidth = 800 }
  else if(w > 1500) { newWidth = 750 }
  else if(w > 1400) { newWidth = 750 }
  else if(w > 900) { newWidth = 750 }
  else if(w > 800) { newWidth = 650 }
  else if(w > 650) { newWidth = 600 }
  else if(w > 550) { newWidth = 450}
  else { newWidth = 400 }
  audioTimeline.setAttribute('width', `${newWidth}`)
})