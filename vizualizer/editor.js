// TODO optimize all this
// this code is public domain (CC0 or unlicense, your choice)

const makeCharsById = arr => arr.reduce((res, char) => { res[char[0]] = char; return res }, {});

const fonts = ['BITMAPFONT_WQY', 'BITMAPFONT_RMG2000', 'BITMAPFONT_TTYP0', 'SHINONOME_GOTHIC', 'SHINONOME_MINCHO'];
const charsById = fonts.reduce((res, fontName) => { res[fontName] = makeCharsById(window[fontName]); return res }, {});

async function getCharacterBitmap(letter, itemWidth) {
  const imageDataArray = new Uint8ClampedArray(letter.map(
    line => Array.from(parseInt(line).toString(2).padStart(itemWidth, '0'))
      .reverse()
      .reduce((res, pixel) => {
        const component = pixel === '0' ? 255 : 0;
        res.push(component, component, component, 255);
        return res
      }, [])
  ).flat())
  const imageData = new ImageData(imageDataArray, itemWidth, 12)
  return await createImageBitmap(imageData)
}

async function updateCanvasPreview(canvas) {
  const ctx = canvas.getContext('2d');
  const letter = canvas.dataset.char.split(',')
  const imageBitmap = await getCharacterBitmap(letter, canvas.width)

  ctx.drawImage(imageBitmap, 0, 0)
}

function updateCanvasPreviews() {
  const previews = document.querySelectorAll('canvas.char-preview');
  for (let i = 0; i < previews.length; i++) {
    updateCanvasPreview(previews[i]);
  }
}

function getFilteredItems() {
  const form = document.getElementById('filter-condition');
  const inputsFrom = form.querySelectorAll('input[type="checkbox"][name="from"]:checked');
  const inputsIfMissing = form.querySelectorAll('input[type="checkbox"][name="if-missing"]:checked');
  const inputsOverlap = form.querySelectorAll('input[type="checkbox"][name="overlap"]:checked');
  
  const idsFrom = Array.from(inputsFrom).map(input => input.value);
  const idsIfMissing = Array.from(inputsIfMissing).map(input => input.value);
  const idsOverlap = Array.from(inputsOverlap).map(input => input.value);

  const itemsToShow = Object.assign({}, ...idsFrom.map(fontId => charsById[fontId]));
  idsIfMissing.forEach(
    fontId => window[fontId].forEach(([charId]) => delete itemsToShow[charId])
  )
  if (idsOverlap.length) {
    const originalIds = Object.keys(itemsToShow)
    const idsToDelete = originalIds.filter(charCode => !idsOverlap.some(fontId => charsById[fontId][charCode]))
    idsToDelete.forEach(charId => delete itemsToShow[charId])
  }
  return itemsToShow;
}

function performFilter() {
  const items = getFilteredItems();
  const ids = Object.keys(items);
  const showPreviewsCheckbox = document.querySelector('#filter-condition input[type="checkbox"][name="draw-chars"]');
  const showPreviews = showPreviewsCheckbox.checked;
  ids.sort()

  const countSpan = document.getElementById('filtered-count');
  countSpan.innerText = `${ids.length} character${ids.length === 1 ? '' : 's'}`;

  const result = document.getElementById('filtered-chars');
  result.innerHTML = ids.map(code => {
    const isNotBMP = code < 0 || code > 0xFFFF;
    const char = isNotBMP ? `(NOT BMP)` : String.fromCharCode(code);
    const codeText = `0x${parseInt(code, 10).toString(16)} (${code}) ${char}`;
    let html
    if (showPreviews) {
      const [_, isFullwidth, data] = items[code];
      const sizeAttr = isFullwidth ? 'data-fw width="12"' : 'data-hw width="6"';
      html = `<canvas class="char-preview" ${sizeAttr} height="12" data-char="${data.join(',')}" title="${codeText}"></canvas>`;
    } else {
      html = `<strong title="${codeText}">${char}</strong>`;
    }
    return html
  }).join(', ');
  if (showPreviews) updateCanvasPreviews();
}

function updatePreviewText(text, element) {
  const fontListInput = element.querySelector('.font-list');
  const fonts = fontListInput.value.split(' ').map(x => x.trim()).filter(x => x in charsById)
  fontListInput.value = fonts.join(' ');
  const canvas = element.querySelector('canvas');
  // TODO: can be too wide for halfwidth characters, ideally should be fixed
  const lines = text.split('\n');
  canvas.width = Math.max(...lines.map(x => x.length)) * 12
  canvas.height = lines.length * 12
  
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  lines.forEach((line, lineIndex) => 
    Array.from(line).reduce((x, char) => {
      const charCode = char.charCodeAt(0)
      const y = lineIndex * 12
      let charDesc
      for (const fontName of fonts) {
        charDesc = charsById[fontName][charCode];
        if (charDesc !== undefined) break;
      }
      
      let width = 12
      if (charDesc) {
        const [_, isFullwidth, data] = charDesc;
        width = isFullwidth ? 12 : 6;
        const imageBitmap = getCharacterBitmap(data, width).then(
          bmp => ctx.drawImage(bmp, x, y)
        );
      } else {
        //TODO use the official fallback glyph?
        ctx.fillStyle = "red";
        ctx.fillRect(x, y, 12, 12);
      }
      return x+width
    }, 0)
  );
}

function updatePreviewTexts() {
  const form = document.getElementById('preview-form');
  const text = form.querySelector('[name="preview-text"]').value
  Array.from(form.querySelectorAll('.preview-block')).forEach(
    element => updatePreviewText(text, element)
  )
}

document.addEventListener('DOMContentLoaded', () => {
  const filterConditionForm = document.getElementById('filter-condition');  
  filterConditionForm.addEventListener('submit', (e) => { e.preventDefault(); performFilter(); return false; })
  
  const previewForm = document.getElementById('preview-form');
  previewForm.addEventListener('submit', (e) => { e.preventDefault(); updatePreviewTexts(); return false; })
  
  Array.from(document.querySelectorAll('.font-id-list')).forEach(
    fontIdListElement => { fontIdListElement.innerText = fonts.join(', '); }
  )
})

//WQY has space to the bottom [and to the right]
//Shinonome has space to the top [and to the right]
