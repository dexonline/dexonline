/* Custom code built on top of select2.min.js */

struct_lexemAjax = {
  data: function(term, page) { return { term: term }; },
  dataType: 'json',
  results: function(data, page) { return data; }, 
  url: wwwRoot + 'ajax/getLexems.php',
};

struct_definitionAjax = {
  data: function(term, page) { return { term: term }; },
  dataType: 'json',
  results: function(data, page) { return data; }, 
  url: wwwRoot + 'ajax/wotdGetDefinitions.php',
};

function contribInit() {
  $('#lexemIds').select2({
    ajax: struct_lexemAjax,
    createSearchChoice: allowNewLexems,
    formatInputTooShort: function () { return 'Vă rugăm să introduceți minim un caracter'; },
    formatSearching: function () { return 'Căutare...'; },
    initSelection: select2InitSelectionAjax,
    minimumInputLength: 1,
    multiple: true,
    tokenSeparators: [',', '\\', '@'],
    width: '600px',
  });
}

function adminIndexInit() {
  $('#lexemId').select2({
    ajax: struct_lexemAjax,
    minimumInputLength: 1,
    placeholder: 'caută un lexem',
    width: '300px',
  }).on('change', function(e) {
    $(this).parents('form').submit();
  });

  $('#definitionId').select2({
    ajax: struct_definitionAjax,
    formatResult: function(item) {
      return item.text + ' (' + item.source + ') [' + item.id + ']';
    },
    minimumInputLength: 1,
    placeholder: 'caută o definiție',
    width: '400px',
  }).on('change', function(e) {
    $(this).parents('form').submit();
  });
}

function visualTagInit() {
  $('#lexemId').select2({
    ajax: struct_lexemAjax,
    initSelection: select2InitSelectionAjaxSingle,
    minimumInputLength: 1,
    placeholder: 'caută un lexem',
    width: '300px',
  });
  $('#tagLexemId').select2({
    ajax: struct_lexemAjax,
    initSelection: select2InitSelectionAjaxSingle,
    minimumInputLength: 1,
    placeholder: 'caută un lexem',
    width: '300px',
  }).on('change', function(e) {
    if (!$('#tagLabel').val()) {
      var displayValue = $(this).select2('data').text;
      $('#tagLabel').val(displayValue);
    }
  });
}

function structIndexInit() {
  $('#structLexemFinder').select2({
    ajax: struct_lexemAjax,
    minimumInputLength: 1,
    placeholder: 'caută un lexem...',
    width: '300px',
  });
}

function allowNewLexems(term, data) {
  if (!data.length || data[0].text != term) {
    return { id: '@' + term, text: term + ' (cuvânt nou)'};
  }
};

function select2InitSelection(element, callback) {
  var data = [];
  $(element.val().split(',')).each(function () {
    data.push({ id: this, text: this });
  });
  callback(data);
}

function select2InitSelectionAjaxSingle(element, callback) {
  var id = $(element).val();
  if (id) {
    $.ajax(wwwRoot + 'ajax/getLexemById.php?id=' + id, {dataType: 'json'})
      .done(function(data) {
        callback({ id: id, text: data });
      });
  }
}

function select2InitSelectionAjax(element, callback) {
  var data = [];

  $(element.val().split(',')).each(function (index, lexemId) {
    $.ajax({
      url: wwwRoot + 'ajax/getLexemById.php?id=' + this,
      dataType: 'json',
      success: function(displayValue) {
        if (displayValue) {
          data.push({ id: lexemId, text: displayValue });
        } else {
          data.push({ id: lexemId, text: lexemId.substr(1) + ' (cuvânt nou)' });
        }
      },
      async: false,
    });
  });
  callback(data);
}
