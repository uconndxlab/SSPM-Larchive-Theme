/**
 * @typedef {Object} CollectionItem
 * @property {string} image
 * @property {string} id
 * @property {string} alt
 * @property {string} title
 * @property {string} short_title
 * @property {string} description
 * @property {string} language
 * @property {number} duration
*/


const lightGrey = '#D5D8E5'
const extraLightGrey = '#F6F7FC'
const blue = '#360078';

// Initialise collection arrays
/** @type {CollectionItem[]} */
let allCollectionItems = [];

/** @type {CollectionItem[]} */
let currCollectionItems = [];

const allLangs = [
  'english',
  'german',
  'polish',
  'italian'
]

let selectedLangs = [...allLangs];


// Initialize DOM variables
let durSliderMin = document.getElementById('dur-slider-min');
let durSliderMax = document.getElementById('dur-slider-max');
let sliderTrack = document.querySelector('.slider-track');
let sliderContainer = document.querySelector('.slider-container');
let durLabelMin = document.querySelector('.dur-slider-label.min');
let durLabelMax = document.querySelector('.dur-slider-label.max');
let collectionUl = document.querySelector('.collection-ul');

// Set slider vars
const minGap= 8.33;
const step  = 8.33;
let durMin = 0;
let durMax = 60;


window.addEventListener('DOMContentLoaded', async () => {

  // Ensure DOM elements loaded
  ensureDOMEls();

  // Initial slider calls
  handleDurMin();
  handleDurMax();

  // Get all portraits
  const portraits = Array.from(document.querySelectorAll('.portrait'));

  // Add portrait listeners
  portraits.forEach(p => p.addEventListener('pointerenter', () => changePortrait(p, portraits)))
  portraits.forEach(p => p.addEventListener('touchend', () => changePortrait(p, portraits)))

  // Add slider listeners
  sliderContainer.addEventListener('mousemove', (e) => {
    const rect = sliderContainer.getBoundingClientRect();
    const clickPct = (e.clientX - rect.left) / rect.width;

    const minPct = valueToPct(durSliderMin);
    const maxPct = valueToPct(durSliderMax);

    const closer = Math.abs(clickPct - minPct) <= Math.abs(clickPct - maxPct) ? 'min' : 'max';
    setSlider(closer);
  })


  // Get language checkmarks
  const langChecks = Array.from(document.querySelectorAll('.languages .category-check'));

  // Get all languages
  const getLangs = () => {
    if(langChecks.length > 0) {
      if(langChecks.filter(c => c.checked).length === 0) selectedLangs = [...allLangs];
      else {
        selectedLangs.length = 0;
        langChecks.forEach(check => {

          const label = document.querySelector(`label[for="${check.id}"]`)

          const langName = !label.textContent.toLowerCase().includes('english') ? label.textContent.toLowerCase() : 'English';

          if(check.checked) selectedLangs.push(langName);
      })
      }
      checkItems()

    }
  }

  if(langChecks) {
    langChecks.forEach(check => {
      check.addEventListener('change', getLangs)
    })
  }

  // Load collection items
  try {
    allCollectionItems = await loadCollectionItems()
  } catch(error) {
    console.error(error)
  }

  // Set currently loaded items to all items
  currCollectionItems = [...allCollectionItems];
  checkItems()


  // View/grid select
  const viewSelect = document.querySelector('.view-select');
  const gridSelect = document.querySelector('.selection-wrap#grid');
  const listSelect = document.querySelector('.selection-wrap#list');

  let currentEl = gridSelect;

  // Add view/grid listener
  viewSelect.addEventListener('pointerdown', (e) => {
    console.log(e.target)
    if(e.target !== currentEl) {
      if(Array.from(collectionUl.children).length !== 0) {
        collectionUl.classList.add('filtering');
        collectionUl.querySelector('li').addEventListener('transitionend', () => {
            viewSelect.classList.toggle('select-grid');
            gridSelect.classList.toggle('active');
            listSelect.classList.toggle('active');
            collectionUl.classList.toggle('list');
            collectionUl.classList.toggle('grid');
            currentEl = e.target;

          collectionUl.classList.remove('filtering');
        }, { once:true })
      }


    }

  })

  // Refresh content (currently just visual)
  const refresh = document.querySelector('.refresh');
  refresh.addEventListener('pointerdown', () => {
    refresh.classList.add('refresh-ani');
    refresh.addEventListener('animationend', () => { refresh.classList.remove('refresh-ani')}, { once:true })
  })

})

// Ensure DOM elements function
function ensureDOMEls() {
  if(!durSliderMin) durSliderMin = document.getElementById('dur-slider-min');
  if(!durSliderMax) durSliderMax = document.getElementById('dur-slider-max');
  if(!sliderTrack) sliderTrack = document.querySelector('.slider-track');
  if(!sliderContainer) sliderContainer = document.querySelector('.slider-container');
  if(!durLabelMin) durLabelMin = document.querySelector('.dur-slider-label.min');
  if(!durLabelMax) durLabelMax = document.querySelector('.dur-slider-label.max');
  if(!collectionUl) collectionUl = document.querySelector('.collection-ul');

}

// Call slider functions on resize
window.addEventListener('resize', () => {
  handleDurMax()
  handleDurMin()
})

// Change active portrait
function changePortrait(active, portraits) {
  if(active && portraits.length > 0) {
    portraits.forEach(p => {
      if(p === active) { p.classList.add('active') }
      else { p.classList.remove('active') }
    })
  }
}

function searchCollection() {}

// Set slider zIndex
function setSlider(active) {
  if(active === 'min') {
    durSliderMin.style.zIndex = '3';
    durSliderMax.style.zIndex = '2';
  } else {
    durSliderMin.style.zIndex = '2';
    durSliderMax.style.zIndex = '3';
  }
}

// Get percent from value
function valueToPct(slider) {
  const min = Number(slider.min);
  const max = Number(slider.max);
  const v = Number(slider.value);
  return (v-min) / (max-min);
}

// Set slider label position and text
function setLabel(label, slider) {
  const sliderRect = slider.getBoundingClientRect();

  const min = Number(slider.min);
  const max = Number(slider.max);
  const value = Number(slider.value);
  const percent = (value - min) / (max - min)

  const thumbSize = 24;
  const usable = sliderRect.width - thumbSize;
  const center = (thumbSize / 2) + usable * percent;

  const minutes = Math.round(value / step) * 5;

  label.textContent = `${minutes} MIN`;
  label.style.left = `${center}px`;
  label.style.transform = `translateX(-50%)`;
}

// Handle min slider
function handleDurMin() {
  if(Number(durSliderMax.value) - Number(durSliderMin.value) <= minGap * 2) {
    durSliderMin.value = Number(durSliderMax.value) - (minGap * 2);
  }
  
  const newDur = Math.round(durSliderMin.value / step) * 5;
  if(newDur !== durMin) {
    durMin = newDur;
    checkItems()
  }
  
  setLabel(durLabelMin, durSliderMin)
  fillSlider()
}

// Handle max slider
function handleDurMax() {
  if(Number(durSliderMax.value) - Number(durSliderMin.value) <= minGap * 2) {
    durSliderMax.value = Number(durSliderMin.value) + (minGap * 2);
  }
  const newDur = Math.round(durSliderMax.value / step) * 5;
  if(newDur !== durMax) {
    durMax = newDur;
      console.log(`DurMax: ${durMax}`)

    checkItems();
  }
  setLabel(durLabelMax, durSliderMax)
  fillSlider()
}

// Fill slider track
function fillSlider() {
  const minPercent = (durSliderMin.value / 100) * 100;
  const maxPercent = (durSliderMax.value / 100) * 100;
  sliderTrack.style.background = `linear-gradient(to right, ${lightGrey} ${minPercent}%, ${blue} ${minPercent}%, ${blue} ${maxPercent}%, ${lightGrey} ${maxPercent}%)`;
}

// Build collection elements
function buildCollectionItems() {
  if(collectionUl && currCollectionItems.length > 0) {
    collectionUl.classList.add('filtering');
    if(Array.from(collectionUl.children).length !== 0) {
      const children = Array.from(collectionUl.children);
      children[children.length - 1].addEventListener('transitionend', () => {
        build()
      }, { once: true })
    } else {
      build()
    }

    // Create and assign all DOM elements
    function build() {
      collectionUl.innerHTML = '';
      currCollectionItems.forEach(/** @param {CollectionItem} item */ (item) => {
        const imgSrc = `assets/collection/${item.image}.png`
        const id = item.id;
        const alt = item.alt;
        const title = item.title;
        const shortTitle = item.short_title;
        const description = item.description;
        const language = item.language;

        const li = document.createElement('li');
        li.classList.add('collections-item', 'rounded-1', 'overflow-hidden', 'pointer');
        li.id = id;
        const imgEl = document.createElement('img');
        imgEl.src = imgSrc;
        imgEl.alt = alt;
        imgEl.className = 'item-img';
        li.appendChild(imgEl);

        const titleEl = document.createElement('h3');
        titleEl.classList.add('item-title', 'grotesk-mono-bold', 'fs-lg');
        titleEl.textContent = title;
        li.appendChild(titleEl);

        const shortTitleEl = document.createElement('h4');
        shortTitleEl.classList.add('item-short-title', 'grotesk-mono-light', 'fs-sm');
        shortTitleEl.textContent = shortTitle;
        li.appendChild(shortTitleEl);

        const descriptionEl = document.createElement('p');
        descriptionEl.classList.add('item-description', 'fs-body', 'my-3');
        descriptionEl.textContent = description;
        li.appendChild(descriptionEl);

        const langEl = document.createElement('div');
        langEl.classList.add('item-language', 'd-flex', 'gap-2');
        const langName = language;
        const langFlag = document.createElement('div');
        langFlag.classList.add('item-flag', `${langName.toLowerCase()}`)
        const langSpan = document.createElement('span');
        langSpan.classList.add('language-name', 'fs-body');


        langSpan.textContent = langName !== 'English' ? langName : `${langName} (United States)`;
        langEl.appendChild(langFlag);
        langEl.appendChild(langSpan);
        li.appendChild(langEl);

        const hr = document.createElement('hr');
        li.appendChild(hr);

        li.addEventListener('pointerdown', () => {
          window.open('single-story.html','_self')
        })

        collectionUl.appendChild(li);
      })
      // Remove 0 opacity class after short delay
      setTimeout(() => {
        collectionUl.classList.remove('filtering')
      }, 100)
    }

  }
}

// Load collection items from json (easier than having them all in JS)
async function loadCollectionItems() {
  const res = await fetch('./collections-items.json')
  if(!res.ok) throw new Error('Unable to get items.');
  return res.json();
}

// Check and filter collection items
function checkItems() {
  if(selectedLangs.length === 0) selectedLangs = [...allLangs];
  const filtered = allCollectionItems.filter(item => {
    return (
      selectedLangs.includes(item.language.toLowerCase()) &&
      (item.duration >= durMin && item.duration <= durMax)
    )
  })
  if(filtered.length !== 0 && filtered !== currCollectionItems) {
    currCollectionItems = [...filtered];
      buildCollectionItems();
  }
}