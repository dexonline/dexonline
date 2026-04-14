$(function() {
  // When present and set to 'on', activate TinyMCE. When absent or set to any
  // other value, do nothing.
  var STORAGE_KEY = 'tinymce';

  function init() {
    $('#tinymceToggleButton').click(tinymceToggle);

    var value = localStorage.getItem(STORAGE_KEY);

    if (!value) {
      // fallback to cookie; if found, migrate it to local storage
      var value = $.cookie(STORAGE_KEY);
      if (value) {
        if (value == 'on') {
          localStorage.setItem(STORAGE_KEY, 'on');
        }
        $.removeCookie(STORAGE_KEY, {path: '/'});
      }
    }

    if (value == 'on') {
      $('#tinymceToggleButton').click();
    }
  }

  function tinymceToggle() {
    var darkMode = getColorScheme() == 'dark';

    if (!tinymce.activeEditor) {
      // necessary since CSS and JS files are merged in a different directory
      tinymce.baseURL = wwwRoot + 'js/third-party/tinymce-5.9.2';
      tinymce.suffix = '.min';

      tinymce.init({
        /* keep the statusbar so that the resize icon is visible */
        branding: false,
        content_css: [ darkMode ? 'dark' : '', '../css/tinymce.css' ],
        elementpath: false,
        entity_encoding: 'raw',
        height: 350,
        menubar: false,
        resize: 'both',
        selector: '.tinymceTextarea',
        setup: tinymceSetup,
        skin: darkMode ? 'oxide-dark' : 'oxide',
        toolbar: 'undo redo | ' +
          'bold italic spaced superscript subscript abbrev smallcapsabbr | ' +
          'romb rombnegru linie | ' +
          'elene chirilice speciale',
        width: '100%',
        plugins: 'paste',
      });
      localStorage.setItem(STORAGE_KEY, 'on');
    } else {
      for (id in tinymce.editors) {
        tinymce.EditorManager.execCommand(
          'mceRemoveEditor',true, id);
      }
      localStorage.removeItem(STORAGE_KEY);
    }
    return false;
  }

  function tinymceSetup(editor) {
    editor.on('init', function() {

      var _doc = $(document);
      // Trigger keyboard events on parent document.
      // This is done so that keybindings work even when TinyMCE has focus.
      // In this case evt.target will be .mce-content-body.
      editor.on('keydown', function(evt) { _doc.trigger(evt); });

      // Register a "spaced" format
      editor.formatter.register('spaced', {
        inline : 'span',
        classes: 'spaced',
      });

      // Register an "abbrev" format
      editor.formatter.register('abbrev', {
        inline : 'abbr',
      });

      // Register an "smallcapsabbr" format
      editor.formatter.register('smallcapsabbr', {
        inline : 'span',
        classes: 'small-caps',
      });

      // Add a shortcut for toggling the spaced format
      editor.addShortcut('ctrl+s', 'spaced', function() {
        editor.formatter.toggle('spaced');
      }, this);

      internalToHtml({ target: this });
    });

    editor.on('show', internalToHtml);
    editor.on('PostProcess', htmlToInternal);
    editor.on('change', function () {
      editor.save();
    });

    // Add a toolbar button for spaced text
    editor.ui.registry.addToggleButton('spaced', {
      tooltip: 'Spațiat',
      text: '␣',
      onAction: function (_) {
        editor.execCommand('mceToggleFormat', false, 'spaced');
      },
      onSetup: function (api) {
        editor.formatter.formatChanged('spaced', function (state) {
          api.setActive(state);
        });
      }
    });

    // Add a toolbar button for abbreviated text
    editor.ui.registry.addToggleButton('abbrev', {
      tooltip: 'Abreviere',
      text: '#',
      onAction: function (_) {
        editor.execCommand('mceToggleFormat', false, 'abbrev');
      },
      onSetup: function (api) {
        editor.formatter.formatChanged('abbrev', function (state) {
          api.setActive(state);
        });
      }
    });

    // Add a toolbar button for small caps text for abbreviation
    editor.ui.registry.addToggleButton('smallcapsabbr', {
      tooltip: 'Capităluțe abrevieri',
      text: 'SC',
      onAction: function (_) {
        editor.execCommand('mceToggleFormat', false, 'smallcapsabbr');
      },
      onSetup: function (api) {
        editor.formatter.formatChanged('smallcapsabbr', function (state) {
          api.setActive(state);
        });
      }
    });

    editor.ui.registry.addButton('romb', {
      text: '◊',
      tooltip: 'Inserează romb (între spații)',
      onAction: function () {
        editor.insertContent(' ◊ ')
      }
    });

    editor.ui.registry.addButton('rombnegru', {
      text: '♦',
      tooltip: 'Inserează romb negru (între spații)',
      onAction: function () {
        editor.insertContent(' ♦ ')
      }
    });

    editor.ui.registry.addButton('linie', {
      text: '–',
      tooltip: 'Inserează linie (între spații)',
      onAction: function () {
        editor.insertContent(' – ')
      }
    });

    /***********/

    const greek = [
      'α','β','γ','δ','ε','ζ','η','θ','ι','κ','λ','μ',
      'ν','ξ','ο','π','ρ','σ','τ','υ','φ','χ','ψ','ω'
    ];

    /*
    editor.ui.registry.addMenuButton('elene', {
      text: 'αβγ',
      fetch: function (callback) {
        callback(greek.map(char => ({
          type: 'menuitem',
          text: char,
          onAction: () => editor.insertContent(char)
        })));
      }
    });
     */

    editor.ui.registry.addButton('elene', {
      text: 'αβγ',
      onAction: function () {
        editor.windowManager.open({
          title: 'Litere grecești',
          body: {
            type: 'panel',
            items: [{
              type: 'htmlpanel',
              html: `
            <div style="font-family: sans-serif; padding: 4px;">

              <!-- Zona de previzualizare -->
              <div style="
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 8px;
              ">
                <div id="greek-preview" style="
                  flex: 1;
                  min-height: 32px;
                  padding: 4px 8px;
                  border: 1px solid #aaa;
                  border-radius: 3px;
                  background: #f9f9f9;
                  font-size: 1.3em;
                  letter-spacing: 2px;
                "></div>
                <button id="greek-backspace" title="Șterge ultimul caracter" style="
                  padding: 4px 8px;
                  cursor: pointer;
                  border: 1px solid #ccc;
                  border-radius: 3px;
                  background: #fff;
                  font-size: 1em;
                ">⌫</button>
                <button id="greek-clear" title="Șterge tot" style="
                  padding: 4px 8px;
                  cursor: pointer;
                  border: 1px solid #ccc;
                  border-radius: 3px;
                  background: #fff;
                  font-size: 1em;
                ">✕</button>
                <button id="greek-insert" title="Inserează în editor" style="
                  padding: 4px 10px;
                  cursor: pointer;
                  border: 1px solid #4a90d9;
                  border-radius: 3px;
                  background: #4a90d9;
                  color: #fff;
                  font-size: 1em;
                ">✓</button>
              </div>

              <!-- Grila de caractere -->
              <div id="greek-grid" style="
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                gap: 4px;
              ">
                ${greek.map(ch =>
                `<button data-ch="${ch}" style="
                    font-size: 1.2em;
                    padding: 6px;
                    cursor: pointer;
                    background: #fff;
                    min-width: 32px;
                  ">${ch}</button>`
              ).join('')}
              </div>

            </div>
          `
            }]
          },
          buttons: [],
        });

        setTimeout(() => {
          const preview  = document.getElementById('greek-preview');
          const grid     = document.getElementById('greek-grid');
          const btnBS    = document.getElementById('greek-backspace');
          const btnClear = document.getElementById('greek-clear');
          const btnIns   = document.getElementById('greek-insert');

          // Adaugă caracter în previzualizare
          grid.addEventListener('click', function (e) {
            const ch = e.target.dataset.ch;
            if (ch) preview.textContent += ch;
          });

          // Șterge ultimul caracter
          btnBS.addEventListener('click', function () {
            preview.textContent = preview.textContent.slice(0, -1);
          });

          // Golește previzualizarea
          btnClear.addEventListener('click', function () {
            preview.textContent = '';
          });

          // Inserează în editor și închide
          btnIns.addEventListener('click', function () {
            const text = preview.textContent;
            if (text) {
              editor.insertContent(text);
              preview.textContent = '';
              editor.windowManager.close();
            }
          });

        }, 100);
      }
    });

    /****/

    const cyrillic = [
      'а','б','в','г','д','е','ж','з','и','й','к','л','м','н','о','п','р','с',
      'т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я'
    ];

    /*
    editor.ui.registry.addMenuButton('chirilice', {
      text: 'яжч',
      fetch: function (callback) {
        callback(cyrillic.map(char => ({
          type: 'menuitem',
          text: char,
          onAction: () => editor.insertContent(char)
        })));
      }
    });
     */

    editor.ui.registry.addButton('chirilice', {
      text: 'яжч',
      onAction: function () {
        editor.windowManager.open({
          title: 'Litere chirilice',
          body: {
            type: 'panel',
            items: [{
              type: 'htmlpanel',
              html: `
            <div style="font-family: sans-serif; padding: 4px;">

              <!-- Zona de previzualizare -->
              <div style="
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 8px;
              ">
                <div id="cyrillic-preview" style="
                  flex: 1;
                  min-height: 32px;
                  padding: 4px 8px;
                  border: 1px solid #aaa;
                  border-radius: 3px;
                  background: #f9f9f9;
                  font-size: 1.3em;
                  letter-spacing: 2px;
                "></div>
                <button id="cyrillic-backspace" title="Șterge ultimul caracter" style="
                  padding: 4px 8px;
                  cursor: pointer;
                  border: 1px solid #ccc;
                  border-radius: 3px;
                  background: #fff;
                  font-size: 1em;
                ">⌫</button>
                <button id="cyrillic-clear" title="Șterge tot" style="
                  padding: 4px 8px;
                  cursor: pointer;
                  border: 1px solid #ccc;
                  border-radius: 3px;
                  background: #fff;
                  font-size: 1em;
                ">✕</button>
                <button id="cyrillic-insert" title="Inserează în editor" style="
                  padding: 4px 10px;
                  cursor: pointer;
                  border: 1px solid #4a90d9;
                  border-radius: 3px;
                  background: #4a90d9;
                  color: #fff;
                  font-size: 1em;
                ">✓</button>
              </div>

              <!-- Grila de caractere -->
              <div id="cyrillic-grid" style="
                display: grid;
                grid-template-columns: repeat(8, 1fr);
                gap: 4px;
              ">
                ${cyrillic.map(ch =>
                `<button data-ch="${ch}" style="
                    font-size: 1.2em;
                    padding: 6px;
                    cursor: pointer;
                    background: #fff;
                    min-width: 32px;
                  ">${ch}</button>`
              ).join('')}
              </div>

            </div>
          `
            }]
          },
          buttons: [],
        });

        setTimeout(() => {
          const preview  = document.getElementById('cyrillic-preview');
          const grid     = document.getElementById('cyrillic-grid');
          const btnBS    = document.getElementById('cyrillic-backspace');
          const btnClear = document.getElementById('cyrillic-clear');
          const btnIns   = document.getElementById('cyrillic-insert');

          // Adaugă caracter în previzualizare
          grid.addEventListener('click', function (e) {
            const ch = e.target.dataset.ch;
            if (ch) preview.textContent += ch;
          });

          // Șterge ultimul caracter
          btnBS.addEventListener('click', function () {
            preview.textContent = preview.textContent.slice(0, -1);
          });

          // Golește previzualizarea
          btnClear.addEventListener('click', function () {
            preview.textContent = '';
          });

          // Inserează în editor și închide
          btnIns.addEventListener('click', function () {
            const text = preview.textContent;
            if (text) {
              editor.insertContent(text);
              preview.textContent = '';
              editor.windowManager.close();
            }
          });

        }, 100);
      }
    });

  }

  // Convert some of our internal notation to HTML. This is not exhaustive,
  // just enough to allow TinyMCE to work properly.
  function internalToHtml(ed) {
    var s = $('#' + ed.target.id).val();
    s = '<p>' + s.replace(/\n/gi, '</p><p>') + '</p>'; // wrap paragraphs

    s = s.replace(/\\@/g, '~~~SAVE~~~'); // move \@ out of the way
    s = s.replace(/@([^@]*)@/g, '<strong>$1</strong>');
    s = s.replace(/~~~SAVE~~~/g, '\\@'); // restore \@

    s = s.replace(/\\\$/g, '~~~SAVE~~~'); // move \$ out of the way
    s = s.replace(/\$([^$]*)\$/g, '<em>$1</em>');
    s = s.replace(/~~~SAVE~~~/g, '\\$'); // restore \$

    s = s.replace(/\\%/g, '~~~SAVE~~~'); // move \% out of the way
    s = s.replace(/%([^%]*)%/g, '<span class="spaced">$1</span>');
    s = s.replace(/~~~SAVE~~~/g, '\\%'); // restore \%

    s = s.replace(/\{~(.*?)~\}/g, '<span class="small-caps">$1</span>');
    s = s.replace(/~~(.*?)~~/g, '<span class="small-caps-l">$1</span>');

    s = s.replace(/\\#/g, '~~~SAVE~~~'); // move \# out of the way
    s = s.replace(/#([^#]*)#/g, '<abbr>$1</abbr>');
    s = s.replace(/~~~SAVE~~~/g, '\\#'); // restore \#

    s = s.replace(/ \* /g, ' ◊ ');
    s = s.replace(/ \*\* /g, ' ♦ ');
    s = s.replace(/ - /g, ' – ');

    s = s.replace(/\^(\d)/g, '<sup>$1</sup>');
    s = s.replace(/_(\d)/g, '<sub>$1</sub>');

    s = s.replace(/\^\{([^}]*)\}/g, '<sup>$1</sup>');
    s = s.replace(/_\{([^}]*)\}/g, '<sub>$1</sub>');
    ed.target.setContent(s);
  }

  // Convert HTML to our internal notation
  function htmlToInternal(ed) {
    var s = ed.content;
    s = s.replace(/<\/p><p>/gi, '\n').replace(/<\/?p>/gi, '');
    s = s.replace(/<\/?strong>/gi, '@');
    s = s.replace(/<\/?em>/gi, '$');
    s = s.replace(/<span class="spaced">(.*?)<\/span>/gi, '%$1%');
    s = s.replace(/<span class="small-caps">(.*?)<\/span>/gi, '{~$1~}');
    s = s.replace(/<span class="small-caps-l">(.*?)<\/span>/gi, '~~$1~~');
    s = s.replace(/<abbr[^>]*>(.*?)<\/abbr>/gi, '#$1#');

    s = s.replace(/ ◊ /g, ' * ');
    s = s.replace(/ ♦ /g, ' ** ');
    s = s.replace(/ – /g, ' - ');

    s = s.replace(/<sup>(\d)<\/sup>/gi, '^$1');
    s = s.replace(/<sub>(\d)<\/sub>/gi, '_$1');
    s = s.replace(/<sup>(.*?)<\/sup>/gi, '^{$1}'); // *? = non-greedy
    s = s.replace(/<sub>(.*?)<\/sub>/gi, '_{$1}');
    s = s.replace(/&lt;/gi, '<');
    s = s.replace(/&gt;/gi, '>');
    ed.content = s;
  }

  init();
});
