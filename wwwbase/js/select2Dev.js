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

function structIndexInit() {
  $('#structLexemFinder').select2({
    ajax: struct_lexemAjax,
    minimumInputLength: 1,
    placeholder: 'caută un lexem...',
    width: '300px',
  });
}

function definitionEditInit() {
  // Show/hide elements related to the similar source and definition
  var comment = $('#similarRecord').html().replace(/^<!--(.*)-->$/, '$1');
  var data = JSON.parse(comment);
  definitionEditUpdateFields(data);

  $('#lexemIds').select2({
    ajax: struct_lexemAjax,
    createSearchChoice: allowNewLexems,
    escapeMarkup: function(m) { return m; },
    initSelection: select2InitSelectionAjax,
    formatSelection: formatLexemWithEditLink,
    minimumInputLength: 1,
    multiple: true,
    width: '600px',
  });

  $('#lexemIds, #sourceDropDown').change(definitionEditUpdateFieldsJson);
  $('#refreshButton').click(definitionEditUpdateFieldsJson);
  $('#similarDefinitionEdit').click(similarDefinitionEditClick);
}

function definitionEditUpdateFieldsJson() {
  var data = {
    definitionId: $('input[name="definitionId"]').val(),
    definitionInternalRep: $('textarea[name="internalRep"]').val(),
    commentInternalRep: $('textarea[name="commentContents"]').val(),
    sourceId: $('#sourceDropDown').val(),
    lexemIds: $('#lexemIds').val(),
  };
  $.post(wwwRoot + 'ajax/getSimilarRecord.php', data, definitionEditUpdateFields, 'json');
}

function definitionEditUpdateFields(data) {
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

function formatLexemWithEditLink(lexem) {
  if (startsWith(lexem.id, '@')) {
    // don't show an edit link for soon-to-be created lexems
    return lexem.text;
  } else {
    return lexem.text + ' <a class="select2Edit" href="lexemEdit.php?lexemId=' + lexem.id + '">&nbsp;</a>';
  }
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
