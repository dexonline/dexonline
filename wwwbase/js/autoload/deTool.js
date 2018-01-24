$(function() {
  var stem = null;
  var stemOption = null;

  var lexemStruct = {
    ajax: {
      url: wwwRoot + 'ajax/getLexemes.php',
    },
    allowClear: true,
    createTag: allowNewOptions,
    minimumInputLength: 1,
    placeholder: 'caută un lexem',
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
    initSelect2('.lexem', 'ajax/getLexemesById.php', lexemStruct);
    initSelect2('.model', 'ajax/getModelsByCodes.php', modelStruct);
    $('.lexem').change(lexemChange);
    $('.model').change(modelChange);
    $('.shortcutI3').click(shortcutI3);
    $('#addRow').click(addRow);
    $('#refreshButton, #saveButton').click(endEdit);

    stem = $('#stem').detach().removeAttr('id');
    stemOption = stem.find('.model option').detach();
  }

  function addRow() {
    var r = stem.clone(true).appendTo('#lexemesTable');
    r.find('.lexem').select2(lexemStruct);
    r.find('.model').select2(modelStruct);
    return false;
  }

  // Refresh the model list
  function lexemChange() {
    var lexemeId = $(this).val();
    var m = $(this).closest('tr').find('.model');
    m.html('');
    
    if (lexemeId == null) {
      // lexeme field cleared
      m.trigger('change');
    } else if (lexemeId.startsWith('@')) {
      // new lexeme form
      m.append(stemOption).trigger('change');
    } else {
      $.ajax({
        url: wwwRoot + 'ajax/getModelByLexemeId.php?id=' + lexemeId,
        success: function(model) {
          var id = model.modelType + model.modelNumber;
          var text = model.modelType + model.modelNumber + ' (' + model.exponent + ')';
          m.append(new Option(text, id, true, true));
          m.trigger('change');
        },
      });
    }

    $('#saveButton').prop('disabled', true);
  }

  function modelChange() {
    $('#saveButton').prop('disabled', true);
  }

  function shortcutI3() {
    var m = $(this).closest('tr').find('.model');
    m.html('').append(stemOption).trigger('change');

    $('#saveButton').prop('disabled', true);

    return false;
  }

  function endEdit() {
    // make sure even empty lexemeIds and models are being submitted
    $('.lexeme, .model').each(function() {
      if ($(this).val() == null) {
        $(this).append(new Option(0, 0, true, true));
      }
    });
  }

  init();
});
