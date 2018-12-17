$(function() {
  var TINYMCE_COOKIE = 'tinymce';

  function init() {
    $('#tinymceToggleButton').click(tinymceToggle);

    var c = $.cookie(TINYMCE_COOKIE);
    if (c == 'on') {
      $('#tinymceToggleButton').click();
    }
  }

  function tinymceToggle() {
    if (!tinymce.activeEditor) {
      // necessary since CSS and JS files are merged in a different directory
      tinymce.baseURL = wwwRoot + 'js/third-party/tinymce-4.9.1';
      tinymce.suffix = '.min';

      tinymce.init({
        branding: false,
        content_css: '../css/tinymce.css',
        entity_encoding: 'raw',
        menubar: false,
        resize: 'both',
        selector: '.tinymceTextarea',
        setup: tinymceSetup,
        toolbar: 'undo redo | bold italic spaced superscript subscript abbrev',
        width: '100%',
      });
      $.cookie(TINYMCE_COOKIE, 'on', { expires: 3650, path: '/' });
    } else {
      for (id in tinymce.editors) {
        tinymce.EditorManager.execCommand(
          'mceRemoveEditor',true, id);
      }
      $.cookie(TINYMCE_COOKIE, 'off', { expires: 3650, path: '/' });
    }
    return false;
  }

  function tinymceSetup(editor) {
    // Compensate for (possibly) a TinyMCE bug <https://github.com/tinymce/tinymce/issues/3047>
    var obj = $('#' + editor.id);
    obj.val(obj.val().replace(/</g, '&lt;'));

    editor.on('init', function() {

      var _doc = $(document);
      // Trigger keyboard events on parent document.
      // This is done so that keybindings work even when TinyMCE has focus.
      // In this case evt.target will be .mce-content-body.
      editor.on('keydown', function(evt) { _doc.trigger(evt); });

      // Set fontSize slightly bigger than default 11px
      editor.getBody().style.fontSize = '14px';

      // Register a "spaced" format
      editor.formatter.register('spaced', {
        inline : 'span',
        classes: 'spaced',
      });

      // Register an "abbrev" format
      editor.formatter.register('abbrev', {
        inline : 'abbr',
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
    editor.addButton('spaced', {
      tooltip: 'Spațiat',
      text: '␣',
      onClick: function() {
        editor.formatter.toggle('spaced');
      },
      onPostRender: function() {
        var self = this, setup = function() {
          editor.formatter.formatChanged('spaced', function(state) {
            self.active(state);
          });
        };
        editor.formatter ? setup() : editor.on('init', setup);
      }
    });

    // Add a toolbar button for abbreviated text
    editor.addButton('abbrev', {
      tooltip: 'Abreviere',
      text: '#',
      onClick: function() {
        editor.formatter.toggle('abbrev');
      },
      onPostRender: function() {
        var self = this, setup = function() {
          editor.formatter.formatChanged('abbrev', function(state) {
            self.active(state);
          });
        };
        editor.formatter ? setup() : editor.on('init', setup);
      }
    });
  }

    // Convert some of our internal notation to HTML. This is not exhaustive,
    // just enough to allow TinyMCE to work properly.
  function internalToHtml(ed) {
    var s = $('#' + ed.target.id).val();
    s = '<p>' + s.replace(/\n{1,}/gi, '</p><p>') + '</p>'; // wrap paragraphs

    s = s.replace(/\\@/g, '~~~SAVE~~~'); // move \@ out of the way
    s = s.replace(/@([^@]*)@/g, '<strong>$1</strong>');
    s = s.replace(/~~~SAVE~~~/g, '\\@'); // restore \@

    s = s.replace(/\\\$/g, '~~~SAVE~~~'); // move \$ out of the way
    s = s.replace(/\$([^$]*)\$/g, '<em>$1</em>');
    s = s.replace(/~~~SAVE~~~/g, '\\$'); // restore \$

    s = s.replace(/\\%/g, '~~~SAVE~~~'); // move \% out of the way
    s = s.replace(/%([^%]*)%/g, '<span class="spaced">$1</span>');
    s = s.replace(/~~~SAVE~~~/g, '\\%'); // restore \%

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
    s = s.replace(/<abbr>(.*?)<\/abbr>/gi, '#$1#');
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
