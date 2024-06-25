// TODO add checkbox selector
// TODO add pre-filling from existing characters
// TODO add preview from normative fonts
// TODO add togglable grid between pixels
// TODO add fill?
// TODO add larger brushes?

/**
 * Glyph data model.
 */
class GlyphModel {
  /** Array of lines. Each line is array of bits. Each bit is string, '.' or '@'.
   * Passed into GlyphEditorCanvas by reference, so should not be replaced.
   * @type {string[][]} */
  _pixels = [];

  /** Width of the glyph. Normally will be either 6 or 12.
   * @type {number} */
  _width = 0;

  /** Height of the glyph. Normally will be 12.
   * @type {number} */
  _height = 0;

  /** List of change listeners.
   * @type {Function[]} */
  _changeListeners = [];

  /**
   * Creates a glyph model from input text.
   * @param {string} [inputText] Input text string (or falsy value)
   */
  constructor(inputText) {
    this.parseSerialization(inputText, false);
  }

  /**
   * Resets the model data.
   */
  clearData() {
    this._width = 0;
    this._height = 0;
    this._pixels = [];
  }

  /**
   * Accepts input text as lines of . and @ characters.
   * If falsy argument is passed, clears the data.
   * @param {string} [inputText] Input text string (or falsy value)
   * @param {string | false} [changeSourceId] Change source passed to the
   * listeners. Pass `false` to avoid triggering the change listeners.
   */
  parseSerialization(inputText, changeSourceId) {
    if (!inputText) {
      this.clearData();
    } else {   
      // TODO handle lines of different width
      this._pixels = inputText.trim().split('\n').map(
        line => Array.from(line.trim())
      );
      this._height = this._pixels.length;
      this._width = Math.max(...this._pixels.map(line => line.length));
    }

    if (changeSourceId !== false) {
      this._triggerChangeListeners(changeSourceId);
    }
  };

  /**
   * Formats the format with . and @ characters.
   * @returns {string}
   */
  serialize() {
    return this._pixels.map(line => line.join('')).join('\n');
  }

  /** Width of the model, in pixels (should normally be 6 or 12).
   * @type {number} */
  get width() {
    return this._width;
  };

  /** Height of the model, in pixels (should normally be 12).
   * @type {number} */
  get height() {
    return this._height;
  };

  /**
   * Checks if a given pixel is filled.
   * @param {number} x
   * @param {number} y
   */
  getPixel(x, y) {
    return this._pixels[y][x] !== '.';
  };

  /**
   * Sets whether the given pixel is filled.
   * @param {number} x
   * @param {number} y
   * @param {boolean} isFilled
   * @param {string | false} changeSourceId Change source passed to the
   * listeners. Pass `false` to avoid triggering the change listeners.
   */
  setPixel(x, y, isFilled, changeSourceId) {
    this._pixels[y][x] = (isFilled ? '@' : '.');
    if (changeSourceId !== false) {
      this._triggerChangeListeners(changeSourceId);
    }
  };

  /**
   * Registers a change listener.
   * @param {Function} listener A function that is triggered after glyph data
   * is changed. Function receives change source ID.
   */
  addChangeListener(listener) {
    this._changeListeners.push(listener);
  };

  /**
   * Removes a change listener.
   * @param {Function} listener Same function that was passed into `addChangeListener`.
   */
  removeChangeListener(listener) {
    const listenerIndex = this._changeListeners.indexOf(listener);
    this._changeListeners.splice(listenerIndex, 1);
  };

  /**
   * Calls all the registered change listeners.
   * @param {string} changeSourceId String that will be passed to the listeners.
   */
  _triggerChangeListeners(changeSourceId) {
    this._changeListeners.forEach(
      listener => listener(changeSourceId)
    );
  };
};

/**
 * Vizualizes edited glyph.
 */
class GlyphEditorView {
  /** Canvas for the GUI editing.
   * @type {HTMLCanvasElement} */
  _canvas = null;

  /** Model being edited.
   * @type {GlyphModel} */
  _model = null;

  /** Zoom of the pixels (how much screen pixels will be used for font pixel).
   * Must be integer.
   * @type {number} */
  _pxZoom = 10;

  /** The filledness value that is used for drawing.
   * @type {boolean} */
  _fillColor = false;

  /** True if mouse is down right now.
   * @type {boolean} */
  _mouseIsDown = false;

  /** Constructs a new GlyphEditorView.
   * @param {GlyphModel} model
   * @param {HTMLDivElement} Wrapper to which the canvas will be appeneded
   * immediately during the creation of `GlyphModel`.
   */
  constructor(model, parentElement) {
    this._model = model;
    this._canvas = document.createElement('CANVAS');
    this._canvas.className = 'glyph-editor__canvas';
    parentElement.appendChild(this._canvas);
    this.resizeCanvas();
    this.repaintCanvas();
    this._model.addChangeListener(() => {
      this.resizeCanvas();
      this.repaintCanvas()
    });
    // TODO remove this if the editor is view re-created
    this._canvas.addEventListener('mousedown', this.onMouseDown.bind(this));
    this._canvas.addEventListener('mousemove', this.onMouseMove.bind(this));
    this._canvas.addEventListener('mouseup', this.onMouseUp.bind(this));
    document.addEventListener('mouseup', this.onDocumentMouseUp.bind(this));
  };

  /**
   * Ensure sure the canvas size is correct.
   */
  resizeCanvas() {
    this._canvas.width = this._model.width * this._pxZoom;
    this._canvas.height = this._model.height * this._pxZoom;
  }

  /**
   * Updates the contents of the editor.
   */
  repaintCanvas() {
    const width = this._model.width;
    const height = this._model.height;
    const ctx = this._canvas.getContext('2d');
    ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);

    for (let y = 0; y < height; y++) {
      for (let x = 0; x < width; x++) {
        this.drawPixel(ctx, x, y);
      }
    }
  }

  /**
   * Draws a single font pixel (usually zoomed-in, so it takes more than 1px).
   * @param {CanvasRenderingContext2D} ctx Rendering context
   */
  drawPixel(ctx, x, y) {
    ctx.fillStyle = this._model.getPixel(x, y) ? 'black' : 'white';
    const zoom = this._pxZoom;
    ctx.fillRect(x * zoom, y * zoom, zoom, zoom);
  }

  /**
   * Get mouse coordinates in pixels based on mouse event.
   * @params {MouseEvent} event
   * @returns {{x: number | null; y: number: | null}}
   */
  _toCanvasCoords(event) {
    const canvasRect = this._canvas.getBoundingClientRect();
    const zoomedX = event.clientX - canvasRect.left;
    const zoomedY = event.clientY - canvasRect.top;
    const x = Math.floor(zoomedX / this._pxZoom);
    const y = Math.floor(zoomedY / this._pxZoom);

    if (x < 0 || y < 0 || x >= this._model.width || y >= this._model.height) {
      return {x: null, y: null};
    }
    return {x, y};
  }

  /**
   * Handles mouse down event on canvas.
   * @param {MouseEvent} event
   */
  onMouseDown(event) {
    event.preventDefault();

    const {x, y} = this._toCanvasCoords(event);
    this._fillColor = !this._model.getPixel(x, y);
    this._model.setPixel(x, y, this._fillColor, 'mouse down on canvas');
    this._mouseIsDown = true;
    return false;
  }

  /**
   * Handles mouse down event on canvas.
   * @param {MouseEvent} event
   */
  onMouseMove(event) {
    if (!this._mouseIsDown) {
      return true;
    }
    event.preventDefault();

    const {x, y} = this._toCanvasCoords(event);
    if (x !== null && y !== null) {
      this._model.setPixel(x, y, this._fillColor, 'mouse move on canvas');
    }
    return false;
  }

  /**
   * Handles mouse down event on canvas.
   * @param {MouseEvent} event
   */
  onMouseUp(event) {
    event.preventDefault();

    const {x, y} = this._toCanvasCoords(event);
    this._model.setPixel(x, y, this._fillColor, 'mouse up on canvas');
    if (x !== null && y !== null) {
      this._mouseIsDown = false;
    }
    return false;
  }

  /**
   * Handler for onMouseUp outside of the drawing area.
   */
  onDocumentMouseUp() {
    this._mouseIsDown = false;
  }
};

/**
 * Converts textareas with ASCII-represented images (using `.` and `@`
 * as pixels) into visual glyph editors. Works as textarea under-the-hood,
 * and should degrade gracefully to a textarea.
 */
class GlyphEditor {
  /** Textarea that is converted into visual font editor.
   * @type {HTMLTextAreaElement} */
  _element = null;

  /** View for vizualizing the editing surface.
   * @type {GlyphEditorView} */
  _view = null;

  /** Wrapper for the editor UI.
   * @type {HTMLDivElement} */
  _editorUiWrapperDiv = null;

  /** Model being edited.
   * @type {GlyphModel} */
  _model = null;

  /** Time, in milliseconds, between updating the canvas based on changed text.
   * @type {number} */
  static TIME_BETWEEN_SYNC = 500;

  /** ID of the interval to regularly update model based on changed textarea
   * values, while the textarea is focused.
   * @type {number | null} */
  _textareaUpdateIntervalId = null;

  /** Creates the glyph editor based on a single textarea.
   * @param {HTMLTextAreaElement} element Text area that will be transformed into the glyph editor. */
  constructor(element) {
    this._element = element;
    element.glyphEditor = this; // TODO: remove this debug reference, get a better way to access existing font editors

    this._model = new GlyphModel(this._element.value);
    this.createUI();
    this.view = new GlyphEditorView(this._model, this._editorUiWrapperDiv);
    this._model.addChangeListener(() => this._element.value = this._model.serialize());
    this._element.addEventListener('focus', this.onTextareaFocus.bind(this));
    this._element.addEventListener('blur', this.onTextareaBlur.bind(this));
  };

  /**
   * Event handler for textarea's onFocus event
   */
  onTextareaFocus() {
    if (this._textareaUpdateIntervalId !== null) return;
    this._textareaUpdateIntervalId = window.setInterval(
      () => this._model.parseSerialization(this._element.value, 'time-based update from textarea'),
      GlyphEditor.TIME_BETWEEN_SYNC
    );
  }

  /**
   * Event handler for textarea's onBlur event
   */
  onTextareaBlur() {
    window.clearInterval(this._textareaUpdateIntervalId);
    this._textareaUpdateIntervalId = null;
    this._model.parseSerialization(this._element.value, 'on textarea blur');
  }

  /**
   * Converts all the textareas with data-glypheditor attribute into font editors.
   * Should be called when the textarea has already been created (e.g. after DOMContentLoaded).
   */
  static createAll() {
    return Array.from(document.querySelectorAll('textarea[data-glypheditor]')).map(
      element => new GlyphEditor(element)
    );
  };

  /**
   * Create elements for the user interface of the editor.
   */
  createUI() {
    this._editorUiWrapperDiv = document.createElement('DIV');
    this._editorUiWrapperDiv.className = 'glyph-editor__wrapper';
    this._element.parentNode.insertBefore(this._editorUiWrapperDiv, this._element);
  }
};


document.addEventListener('DOMContentLoaded', () => GlyphEditor.createAll());
