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
        toolbar: 'undo redo | bold italic spaced superscript subscript abbrev smallcapsabbr',
        width: '100%',
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
