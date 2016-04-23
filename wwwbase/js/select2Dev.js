/* Custom code built on top of select2.min.js */

struct_lexemAjax = {
  data: function(term, page) { return { term: term }; },
  dataType: 'json',
  results: function(data, page) { return data; }, 
  url: wwwRoot + 'ajax/getLexems.php',
};

struct_modelAjax = {
  data: function(term, page) { return { term: term }; },
  dataType: 'json',
  results: function(data, page) { return data; }, 
  url: wwwRoot + 'ajax/getModels.php',
};

struct_definitionAjax = {
  data: function(term, page) { return { term: term }; },
  dataType: 'json',
  results: function(data, page) { return data; }, 
  url: wwwRoot + 'ajax/wotdGetDefinitions.php',
};

// TODO this is hard-coded for PRIV_STRUCT. Should be configurable
struct_userAjax = {
  data: function(term) {
    return { term: term,
             priv: PRIV_STRUCT,
           };
  },
  dataType: 'json',
  results: function(data, page) { return data; }, 
  url: wwwRoot + 'ajax/getUsers.php',
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
  }).select2('focus');
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

  $('#structuristId').select2({
    ajax: struct_userAjax,
    allowClear: true,
    initSelection: select2InitSelectionAjaxUserSingle,
    minimumInputLength: 1,
    placeholder: '(opțional)',
    width: '173px',
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
    return {
      id: '@' + term,
      text: term + ' (cuvânt nou)',
      consistentAccent: 1,
      hasParadigm: 1,
    };
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
        callback(data);
      });
  }
}

function select2InitSelectionAjax(element, callback) {
  var data = [];

  $(element.val().split(',')).each(function (index, lexemId) {
    $.ajax({
      url: wwwRoot + 'ajax/getLexemById.php?id=' + this,
      dataType: 'json',
      success: function(result) {
        data.push(result);
      },
      async: false,
    });
  });
  callback(data);
}

function select2InitSelectionAjaxModel(element, callback) {
  var data = [];

  $(element.val().split(',')).each(function() {
    $.ajax({
      url: wwwRoot + 'ajax/getModels.php?exact=1&term=' + this,
      dataType: 'json',
      success: function(displayValue) {
        var tuple = displayValue.results[0];
        data.push(tuple);
      },
      async: false,
    });
  });
  callback(data);
}

function select2InitSelectionAjaxUserSingle(element, callback) {
  var id = $(element).val();
  if (id) {
    $.ajax(wwwRoot + 'ajax/getUsers.php?id=' + id, {dataType: 'json'})
      .done(function(data) {
        var rec = data.results[0];
        callback(rec);
      });
  }
}

