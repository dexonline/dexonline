$(function() {
  var TINYMCE_COOKIE = 'tinymce';

  similarRecord = [];
  var internalRep = $('#internalRep');
  var comment = $('#comment');

  function init() {
    // Show/hide elements related to the similar source and definition
    var c = $('#similarRecord').html().replace(/^<!--(.*)-->$/, '$1');
    similarRecord = JSON.parse(c);
    updateFields(similarRecord);

    initSelect2('#entryIds', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      createTag: allowNewOptions,
      minimumInputLength: 1,
      tags: true,
      templateSelection: formatEntryWithEditLink,
    });
            
    initSelect2('#tagIds', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php' },
      minimumInputLength: 1,
    });
            
    $('#entryIds, #sourceDropDown').change(updateFieldsJson);
    $('#refreshButton').click(updateFieldsJson);
    $('#similarDiff ins, #similarDiff del').click(definitionCopyFromSimilar);
    $('#tinymceToggleButton').click(tinymceToggle);

    var c = $.cookie(TINYMCE_COOKIE);
    if (c == 'on') {
      $('#tinymceToggleButton').click();
    }
  }

  function updateFieldsJson() {
    var data = {
      definitionId: $('input[name="definitionId"]').val(),
      definitionInternalRep: internalRep.val(),
      commentInternalRep: comment.val(),
      sourceId: $('#sourceDropDown').val(),
      entryIds: $('#entryIds').val(),
    };
    $.post(wwwRoot + 'ajax/getSimilarRecord.php', data, updateFields, 'json');
  }

  function updateFields(data) {
    if (data.source) {
      $('.similarSourceName').text(data.source.shortName);
    }
    if (data.definition) {
      $('#similarDefinitionEdit').show();
      $('#similarDefinitionEdit').attr('href', '?definitionId=' + data.definition.id);
      $('#similarRep').html(data.definition.htmlRep);
    } else {
      $('#similarDefinitionEdit').hide();
      $('#similarRep').html('');
    }
    $('#similarDiff').html(data.htmlDiff);
    $('#similarIdentical').toggle(data.identical);
    var existsAndIsDifferent = (data.definition != null) && !data.identical;
    $('#similarNotIdentical').toggle(existsAndIsDifferent);
    $('#similarDiff').toggle(existsAndIsDifferent);

    $('#similarSourceMessageYes, #similarSourceMessageNoSource, #similarSourceMessageNoDefinition').hide();
    if (data.source && data.definition) {
      $('#similarSourceMessageYes').show();
    } else if (data.source) {
      $('#similarSourceMessageNoDefinition').show();
    } else {
      $('#similarSourceMessageNoSource').show();
    }

    if (typeof data.htmlRep != 'undefined') {
      $('#defPreview').html(data.htmlRep);
    }
    if (typeof data.commentHtmlRep != 'undefined') {
      $('#commentPreview').html(data.commentHtmlRep);
    }
  }

  function definitionCopyFromSimilar() {
    var ins = ($(this).prop('tagName') == 'INS') ? 1 : 0;
    var defId = $('input[name=definitionId]').val();
    var similarId = similarRecord.definition.id;
    var url = wwwRoot + 'admin/editSimilarDefinition.php' +
        '?defId=' + defId +
        '&similarId=' + similarId +
        '&sstart=' + $(this).parent().data('start1') +
        '&slen=' + $(this).parent().data('len1') +
        '&dstart=' + $(this).parent().data('start2') +
        '&dlen=' + $(this).parent().data('len2') +
        '&ins=' + ins;
    window.location = url;
  }

  function tinymceToggle() {
    if (!tinymce.activeEditor) {
      tinymce.init({
        content_css: '../css/tinymce.css',
        entity_encoding: 'raw',
        menubar: false,
        resize: 'both',
        selector: 'textarea',
        setup: tinymceSetup,
        toolbar: 'undo redo | bold italic spaced superscript subscript',
        width: 650,
      });
      $.cookie(TINYMCE_COOKIE, 'on');
    } else {
      for (id in tinymce.editors) {
        tinymce.EditorManager.execCommand(
          'mceRemoveEditor',true, id);
      }
      $.cookie(TINYMCE_COOKIE, 'off');
    }
    var tmp = $(this).data('otherText');
    $(this).data('otherText', $(this).text());
    $(this).text(tmp);
    return false;
  }

  function tinymceSetup(editor) {
    // Compensate for (possibly) a TinyMCE bug <https://github.com/tinymce/tinymce/issues/3047>
    var obj = $('#' + editor.id);
    obj.val(obj.val().replace(/</g, '&lt;'));

    editor.on('init', function() {

      // Register a "spaced" format
      editor.formatter.register('spaced', {
        inline : 'span',
        classes: 'spaced',
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
  }

    // Convert some of our internal notation to HTML. This is not exhaustive,
    // just enough to allow TinyMCE to work properly.
  function internalToHtml(ed) {
    var s = $('#' + ed.target.id).val();
    s = s.replace(/\\@/g, '~~~SAVE~~~'); // move \@ out of the way
    s = s.replace(/@([^@]*)@/g, '<strong>$1</strong>');
    s = s.replace(/~~~SAVE~~~/g, '\\@'); // restore \@

    s = s.replace(/\\\$/g, '~~~SAVE~~~'); // move \$ out of the way
    s = s.replace(/\$([^$]*)\$/g, '<em>$1</em>');
    s = s.replace(/~~~SAVE~~~/g, '\\$'); // restore \$

    s = s.replace(/\\%/g, '~~~SAVE~~~'); // move \% out of the way
    s = s.replace(/%([^%]*)%/g, '<span class="spaced">$1</span>');
    s = s.replace(/~~~SAVE~~~/g, '\\%'); // restore \%
      
    s = s.replace(/\^(\d)/g, '<sup>$1</sup>');
    s = s.replace(/_(\d)/g, '<sub>$1</sub>');
    s = s.replace(/\^\{([^}]*)\}/g, '<sup>$1</sup>');
    s = s.replace(/_\{([^}]*)\}/g, '<sub>$1</sub>');
    ed.target.setContent(s);
  }

  // Convert HTML to our internal notation
  function htmlToInternal(ed) {
    var s = ed.content;
    s = s.replace(/<\/?p>/gi, '');
    s = s.replace(/<\/?strong>/gi, '@');
    s = s.replace(/<\/?em>/gi, '$');
    s = s.replace(/<span class="spaced">(.*?)<\/span>/gi, '%$1%');
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
