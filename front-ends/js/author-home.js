

document.addEventListener('DOMContentLoaded', () => {
  const adminDownload = document.querySelector('button#download');
  const downloadPopup = document.querySelector('.download-popup')
  const popupClose = document.querySelector('.download-popup .bi-x');



  // Open download popup
  adminDownload.addEventListener('pointerdown', () => {
    downloadPopup.classList.toggle('open');
    document.body.classList.toggle('popup-overlay')
    document.documentElement.classList.toggle('overflow-hidden')
  })

  // Close download popup
  popupClose.addEventListener('pointerdown', () => {
    downloadPopup.classList.toggle('open');
    document.body.classList.toggle('popup-overlay')
    document.body.classList.toggle('position-relative');
    document.documentElement.classList.toggle('overflow-hidden')
  })
})

