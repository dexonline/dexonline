$(function() {
  var lexemSourceOptions = {
    ajax: { url: wwwRoot + 'ajax/getSources.php' },
    minimumInputLength: 1,
    placeholder: 'surse care atestă flexiunea',
    width: '100%',
  };
  var similarLexemOptions = {
    ajax: { url: wwwRoot + 'ajax/getLexems.php' },
    minimumInputLength: 1,
    placeholder: 'sau indicați un lexem similar',
    width: '250px',
  };

  function init() {
    initSelect2('#entryId', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      allowClear: true,
      minimumInputLength: 1,
      templateSelection: formatEntryWithEditLink,
      width: '100%',
    });

    initSelect2('#tagIds', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php' },
      minimumInputLength: 1,
    });
            
    $('.lexemEditSaveButton').click(saveEverything);

    initSelect2('#sourceIds', 'ajax/getSourcesById.php', lexemSourceOptions);

    $('.similarLexem')
      .select2(similarLexemOptions)
      .on('change', similarLexemChange);
  }

  function saveEverything() {
    // allow disabled selects to submit (they should have been readonly,
    // not disabled, but Select2 4.0 doesn't use readonly).
    $('input[name="stopWord"]').prop('disabled', false);
    $('input[name="isLoc"]').prop('disabled', false);
    $('select[name="modelType"]').prop('disabled', false);
    $('select[name="modelNumber"]').prop('disabled', false);
  }

  function similarLexemChange() {
    var lexemId = $(this).find('option:selected').val();
    var url = wwwRoot + 'ajax/getModelByLexemId.php?id=' + lexemId;
    $.get(url)
      .done(function(data) {
        $('select[name="modelType"]').data('selected', data.modelType);
        $('select[name="modelNumber"]').data('selected', data.modelNumber);
        updateModelTypeList($('*[data-model-dropdown]'));
        $('input[name="restriction"]').val(data.restriction);
      });
  }

  init();
});
