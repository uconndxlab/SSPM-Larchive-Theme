
// Dropdown elements
let dropdowns = Array.from(document.querySelectorAll('.draft-dropdown-wrap'));

// Track each dropdown selection
let dropdownSelections = new Map();
dropdownSelections.set('collection', 'unset');
dropdownSelections.set('item-type', 'unset');
dropdownSelections.set('language', 'unset');
dropdownSelections.set('status', 'unset');
dropdownSelections.set('visibility', 'unset');

// All author objects
const allAuthors = [
    {
      name: 'Author 1',
      id: 'author-1',
      position: null,
      selected: false
    },
    {
      name: 'Author 2',
      id: 'author-2',
      position: null,
      selected: false
    },
    {
      name: 'Author 3',
      id: 'author-3',
      position: null,
      selected: true
    },
    {
      name: 'Author 4',
      id: 'author-4',
      position: null,
      selected: true
    },
    {
      name: 'Author 5',
      id: 'author-5',
      position: null,
      selected: false
    },
    {
      name: 'Author 6',
      id: 'author-6',
      position: null,
      selected: true
    },
    {
      name: 'Author 7',
      id: 'author-7',
      position: null,
      selected: false
    },
  ]

// Track author attributes
const authorsMap = new Map();
allAuthors.forEach(author => {
  authorsMap.set(author.id, author)
})

// Authors drag table
let table = document.querySelector('.authors-table');
let tbody = table.tBodies[0] || table;

// Drag variable declarations
let sourceRow = null;
let rafPending = false;
let lastTargetRow = null;
let lastClientY = 0;


document.addEventListener('DOMContentLoaded', () => {
  if(!dropdowns || dropdowns.length === 0) dropdowns = Array.from(document.querySelectorAll('.draft-dropdown-wrap'));

  // Add dropdown listener
  dropdowns.forEach(drop => {
    drop.addEventListener('pointerdown', (e) => {
      openDropdown(e.currentTarget);
    }, { once:true })
  })

  // Ensure elements loaded
  if(!table) table = document.querySelector('.authors-table');
  if(!tbody) tbody = table.tBodies[0] || table;
  const authorPopup = document.querySelector('.add-author-popup');
  const authorPopupList = authorPopup.querySelector('.add-author-list');
  const addAuthorEl = document.querySelector('.add-author');
  const listEmpty = authorPopup.querySelector('.list-empty');

  addAuthorEl.addEventListener('pointerdown', () => authorPopup.classList.toggle('open'));

  // Make author element
  const makeAuthorEl = (author) => {
    const tr = document.createElement('tr');
    tr.className = 'author-drag';
    tr.setAttribute('data-author-id', author.id);
    tr.draggable = true;

    const td = document.createElement('td');

    const icon = document.createElement('i');
    icon.classList.add('bi', 'bi-list', 'drag-icon');

    const span = document.createElement('span');
    span.className = 'author-remove';
    span.textContent = 'Remove';
    span.addEventListener('pointerdown', () => removeAuthor(author))

    td.innerText = author.name;

    td.appendChild(icon);
    td.appendChild(span);

    tr.appendChild(td);

    tbody.appendChild(tr)
  }

  // Make add author element
  const makeAddEl = (author, prepend = false) => {
    const li = document.createElement('li');
    const plus = document.createElement('div');
    plus.className = 'add-plus';

    li.textContent = author.name;
    li.setAttribute('data-author-id', author.id);
    li.appendChild(plus);

    li.addEventListener('pointerdown', () => {
      addAuthor(author, li);
    })

    if(prepend) { authorPopupList.prepend(li) } else { authorPopupList.append(li) }
  }


  // Remove author from selected + add to unselected
  const removeAuthor = (author) => {
    if(!author) return;

    const authorEl = tbody.querySelector(`tr[data-author-id="${author.id}"]`)
    if(!authorEl) return

    tbody.removeChild(authorEl)

    Object.assign(authorsMap.get(author.id), {
      position: null,
      selected: false
    })
    if(!listEmpty.classList.contains('d-none')) listEmpty.classList.add('d-none')
    makeAddEl(author, true)
  }

  // Add author to selected
  const addAuthor = (author, el) => {

    authorPopupList.removeChild(el);
    makeAuthorEl(author);

    Object.assign(authorsMap.get(author.id), {
        selected: true,
        position: Array.from(tbody.children).length - 1
      }
    )

    // Show "none remaining" if all selected
    if(Array.from(authorPopupList.children).length === 0) {
      listEmpty.classList.remove('d-none');
    }
  }

  // Add selected authors to list
  authorsMap.values().filter(a => a.selected).forEach((author, idx) => {
    makeAuthorEl(author);
    authorsMap.get(author.id).position = idx
  })

  // Add unselected authors to popup
  authorsMap.values().filter(a => !a.selected).forEach(author => {
    makeAddEl(author)
  })

  // Popup listener
  authorPopup.querySelector('i').addEventListener('pointerdown', () => {
    authorPopup.classList.toggle('open');
  })

  // File inputs
  const interviewInput = document.querySelector('#file-input-interview');
  const resourceInput = document.querySelector('#file-input-resource');


  // File inputs map
  const fileMap = new Map();
  fileMap.set('interview', {
    input: interviewInput,
    drop: document.querySelector('.file-drop.interview'),
    btn: document.querySelector('.file-input-btn.interview'),
    types: interviewInput.accept
  })

  fileMap.set('resource', {
    input: resourceInput,
    drop: document.querySelector('.file-drop.resource'),
    btn: document.querySelector('.file-input-btn.resource'),
    types: resourceInput.accept
  })

  // Log dropped file
  const handleDrop = (file) => {
    console.log(file)
  }

  // For each input add drop/click/change listeners
  fileMap.values().forEach(options => {
    options.btn.addEventListener('pointerdown', () => {
      options.input.click();
    })

    options.drop.addEventListener('drop', (e) => {
      const item = e.dataTransfer.items[0];
      if(item.kind === 'file') {
        e.preventDefault();
        handleDrop(item.getAsFile());
      }
    })

    options.input.addEventListener('change', () => {
      const fileItems = options.input.files;
      if(fileItems.length > 0 && fileItems.length < 2) {
        const item = fileItems[0];

        // Check for valid file type
        if(item.type !== '' && options.types.includes(item.type)) {
          console.log(item);
        }
        else {
          console.log('invalid file')
        }
      } else if(fileItems.length > 1) {
        console.log('too many files')
      }
    })

    // Drag listener
    options.drop.addEventListener('dragover', (e) => {
      const fileItems = [...e.dataTransfer.items].filter(
        (item) => item.kind === 'file'
      )
      e.preventDefault()

      // Disable drop if unsupported filetype or too many files
      if(fileItems.length > 0 && fileItems.length < 2) {
        const item = fileItems[0];
        if(item.type !== '' && options.types.includes(item.type)) {
          e.dataTransfer.dropEffect = 'copy';
        } else {
          e.dataTransfer.dropEffect = 'none';
        }
      } else {
        e.dataTransfer.dropEffect = 'none';
      }

    })

  })




  // Prevent default browser drop
  window.addEventListener('dragover', (e) => {
    const dt = e.dataTransfer;
    if(!dt || !dt.items) return;


    const fileItems = [...dt.items].filter(
      (item) => item.kind === 'file',
    )
    if(fileItems.length === 0) return;

    e.preventDefault();
    const targets = [...fileMap.values()].map(({ drop }) => drop)
    if(!targets.some(t => t.contains(e.target))) {
      dt.dropEffect = 'none';
    }
  })

})

// Open dropdown
function openDropdown(wrapEl) {
  const dropdown = wrapEl.querySelector('.draft-dropdown');
  const placeholderWrap = wrapEl.querySelector('.dropdown-placeholder');
  const arrow = wrapEl.querySelector('.dropdown-placeholder svg');
  if(!dropdown || !placeholderWrap || !arrow) return;

  wrapEl.classList.add('open');

  // Set styles for opening
  const inputWraps = Array.from(wrapEl.querySelectorAll('.dropdown-item-wrap'));
  dropdown.style.height = 'auto';
  const rect = dropdown.getBoundingClientRect();
  const fullHeight = rect.height;
  dropdown.style.height = '0';
  dropdown.style.opacity = '1';

  dropdown.style.transition = 'height .5s ease';
  dropdown.offsetWidth;
  dropdown.style.height = `${fullHeight}px`;


  // Select const
  const selectDropdown = (e) => {
    closeDropdown()
    const input = e.currentTarget.querySelector('input');
    input.checked = true

    // Set selection in map
    dropdownSelections.set(`${wrapEl.id}`, `${input.value}`)
    const label = e.currentTarget.querySelector('label');
    placeholderWrap.querySelector('span').textContent = label.textContent;
    wrapEl.classList.add('selected');
  }

  // Add select listener
  inputWraps.forEach(i => {
    i.addEventListener('pointerdown', selectDropdown, { once:true })
  })

  // Click outside dropdown
  const clickOutside = (e) => {
    if(e.target.classList.contains('dropdown-item-wrap')) return;
    window.removeEventListener('pointerdown', clickOutside)

    closeDropdown()
  }

  // Close const
  const closeDropdown = () => {
    wrapEl.removeEventListener('pointerdown', closeDropdown)
    inputWraps.forEach(i => i.removeEventListener('pointerdown', selectDropdown))
    window.removeEventListener('pointerdown', clickOutside)
    dropdown.style.height = '0';
    wrapEl.classList.remove('open');
    setTimeout(() => {
      wrapEl.addEventListener('pointerdown', () => {
        openDropdown(wrapEl)
      }, { once:true })
    }, 250)
  }

  wrapEl.addEventListener('pointerdown', closeDropdown)

  setTimeout(() => {
    window.addEventListener('pointerdown', clickOutside)
  }, 250)
}

// Start author drag
function dragStart(e) {
  const row = e.target.closest('tr');
  if(!row) return;

  sourceRow = row;
  sourceRow.classList.add('dragging');

  // Hide default browser element overlay
  if(e.type !== 'touchstart') {
    const img = new Image();
    img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

    e.dataTransfer.setDragImage(img, 0, 0)
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', '');
  }

}

// While dragging over author table
function dragOver(e) {
  if(!sourceRow) return;

  e.preventDefault();

  // Last mouse y
  lastClientY = e.clientY;

  // Row mouse currently over
  const targetRow = e.target.closest('tr');

  // If mouse not over row or mouse over started row return
  if(!targetRow || targetRow === sourceRow) return;

  // Save latest hover row
  lastTargetRow = targetRow;

  // Throttle to prevent layout errors
  if(rafPending) return;
  rafPending = true;

  requestAnimationFrame(() => {
    rafPending = false;

    // Get latest target
    const target = lastTargetRow;
    if(!target || target === sourceRow) return;

    // Measure target row
    const rect = target.getBoundingClientRect();

    // Check if mouse is above or below latest target
    const before = (lastClientY - rect.top) < rect.height / 2;

    // If above insert before target else after
    const insertBefore = before ? target : target.nextElementSibling;

    // Avoid inserting upon itself
    if(insertBefore === sourceRow) return;

    // Reorder table
    tbody.insertBefore(sourceRow, insertBefore)

  })
}

// Author drag ended
function dragEnd() {

  // Remove drag class
  sourceRow.classList.remove('dragging');

  // Set author positions to new positions
  Array.from(tbody.children).forEach((child, idx) => {
    const authorId = child.getAttribute('data-author-id');
    if(!authorId) return;

    Object.assign(authorsMap.get(authorId), {
      position: idx
    })
  })

  sourceRow = null;
  lastTargetRow = null;
  rafPending = false;
}

  tbody.addEventListener('dragstart', dragStart);
  tbody.addEventListener('dragover', dragOver);
  tbody.addEventListener('dragend', dragEnd);

