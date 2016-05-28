$(function() {
  var stem = null;
  var stemOption = null;

  var lexemStruct = {
    ajax: {
      url: wwwRoot + 'ajax/getLexems.php',
    },
    allowClear: true,
    createTag: allowNewLexems,
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
    initSelect2('.lexem', 'ajax/getLexemsById.php', lexemStruct);
    initSelect2('.models', 'ajax/getModelsByCodes.php', modelStruct);
    $('.lexem').change(lexemChange);
    $('.models').change(modelChange);
    $('.shortcutI3').click(shortcutI3);
    $('#addRow').click(addRow);
    $('#butTest, #butSave').click(endEdit);

    stem = $('#stem').detach().removeAttr('id');
    stemOption = stem.find('.models option').detach();
  }

  function addRow() {
    var r = stem.clone(true).appendTo('#lexemsTable');
    r.find('.lexem').select2(lexemStruct);
    r.find('.models').select2(modelStruct);
    return false;
  }

  // Refresh the model list
  function lexemChange() {
    var lexemId = $(this).val();
    var m = $(this).closest('tr').find('.models');
    m.html('');
    
    if (lexemId == null) {
      // lexem field cleared
      m.trigger('change');
    } else if (lexemId.startsWith('@')) {
      // new lexem form
      m.append(stemOption).trigger('change');
    } else {
      $.ajax({
        url: wwwRoot + 'ajax/getModelsByLexemId.php?id=' + lexemId,
        success: function(models) {
          $.each(models, function(index, t) {
            var id = t.modelType + t.modelNumber;
            var text = t.modelType + t.modelNumber + ' (' + t.exponent + ')';
            m.append(new Option(text, id, true, true));
          });
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
    var m = $(this).closest('tr').find('.models');
    m.html('').append(stemOption).trigger('change');

    $('#butSave').prop('disabled', true);

    return false;
  }

  function endEdit() {
    // create an array of arrays of model codes
    var models = [];
    $('.models').each(function() {
      var list = [];
      $(this).find('option:selected').each(function() {
        list.push($(this).val());
      });
      models.push(list);
    });
    $('input[name="jsonModels"]').val(JSON.stringify(models));

    // make sure even empty lexemIds are being submitted
    $('.lexem').each(function() {
      if ($(this).val() == null) {
        $(this).append(new Option(0, 0, true, true));
      }
    });
  }

  init();
});
