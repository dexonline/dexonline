$(function() {
  var stem = null;
  var stemOption = null;

  var entryStruct = {
    ajax: {
      url: wwwRoot + 'ajax/getEntries.php',
    },
    allowClear: true,
    createTag: allowNewOptions,
    minimumInputLength: 1,
    placeholder: 'caută o intrare',
    tags: true,
  };

  var modelStruct = {
    ajax: {
      url: wwwRoot + 'ajax/getModelsByCodes.php',
      data: function(params) {
        // convert the request into the format expected server-side
        var arr = [params.term];
        return {
          q: JSON.stringify(arr),
          fuzzy: true,
        };
      },
      processResults: function (data, params) {
        // parse the results into the format expected by select2
        return {
          results: data,
        }
      },
    },
    minimumInputLength: 1,
    placeholder: 'caută un model',
  };

  function init() {
    initSelect2('.entry', 'ajax/getEntriesById.php', entryStruct);
    initSelect2('.model', 'ajax/getModelsByCodes.php', modelStruct);
    $('.entry').change(entryChange);
    $('.model').change(modelChange);
    $('.shortcutI3').click(shortcutI3);
    $('#addRow').click(addRow);
    $('#butTest, #butSave').click(endEdit);

    stem = $('#stem').detach().removeAttr('id');
    stemOption = stem.find('.model option').detach();
  }

  function addRow() {
    var r = stem.clone(true).appendTo('#entriesTable');
    r.find('.entry').select2(entryStruct);
    r.find('.model').select2(modelStruct);
    return false;
  }

  // Refresh the model list
  function entryChange() {
    var entryId = $(this).val();
    var m = $(this).closest('tr').find('.model');
    m.html('');
    
    if (entryId == null) {
      // entry field cleared
      m.trigger('change');
    } else if (entryId.startsWith('@')) {
      // new entry form
      m.append(stemOption).trigger('change');
    } else {
      $.ajax({
        url: wwwRoot + 'ajax/getModelByLexemId.php?id=' + lexemId,
        success: function(model) {
          var id = model.modelType + model.modelNumber;
          var text = model.modelType + model.modelNumber + ' (' + model.exponent + ')';
          m.append(new Option(text, id, true, true));
          m.trigger('change');
        },
      });
    }

    $('#butSave').prop('disabled', true);
  }

  function modelChange() {
    $('#butSave').prop('disabled', true);
  }

  function shortcutI3() {
    console.log('here');
    var m = $(this).closest('tr').find('.model');
    m.html('').append(stemOption).trigger('change');

    $('#butSave').prop('disabled', true);

    return false;
  }

  function endEdit() {
    // make sure even empty entryIds and models are being submitted
    $('.entry, .model').each(function() {
      if ($(this).val() == null) {
        $(this).append(new Option(0, 0, true, true));
      }
    });
  }

  init();
});
