

document.addEventListener('DOMContentLoaded', () => {
  const hamburgIcon = document.querySelector('.hamburg-wrap');
const hamburgPopup = document.querySelector('.hamburg-popup');
  hamburgIcon.addEventListener('pointerdown', () => {
    hamburgPopup.classList.toggle('open')
  })
})
