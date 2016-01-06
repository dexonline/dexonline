struct_inflectedFormAjax = {
  data: function(query, page) { return { query: query }; },
  dataType: 'json',
  results: function(data, page) { return data; }, 
  url: wwwRoot + 'ajax/getInflectedForms.php',
};

function forbiddenFormsInit() {
  $('#inflectedForm').select2({
    ajax: struct_inflectedFormAjax,
    formatResult: formatForbiddenForm,
    formatSelection: formatForbiddenForm,
    minimumInputLength: 1,
    placeholder: 'caută o formă flexionară',
    width: '600px',
  });
  $('.forbiddenFormsAction').click(saveForbiddenForm);
}

function formatForbiddenForm(x) {
  return '<b>' + x.form + '</b> (' + x.baseForm + ', ' + x.model + ', ' + x.inflection + ')';
}

function saveForbiddenForm() {
  $.get(wwwRoot + 'ajax/forbiddenFormHandler.php',
        { id: $('#inflectedForm').val(),
          action: $(this).data('action') })
    .done(function(data) {
      flashMessage('Succes!', 'green');
    })
    .fail(function(data) {
      flashMessage('Eroare!', 'red');
    });
  return false;
}

function flashMessage(text, color) {
  $('#ff-flash')
    .text(text)
    .css('color', color)
    .slideDown('fast', function() {
      setTimeout(function() {
        $('#ff-flash').slideUp('fast');
      }, 1000);
    });
}
