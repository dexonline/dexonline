$(function() {
  function init() {
    $('#lexemeId').select2({
      ajax: { url: wwwRoot + 'ajax/getLexemes.php', },
      minimumInputLength: 1,
      placeholder: 'caută un lexem',
      width: '100%',
    });

    $('#definitionId').select2({
      ajax: { url: wwwRoot + 'ajax/getDefinitions.php', },
      templateResult: formatDefinition,
      templateSelection: formatDefinition,
      minimumInputLength: 1,
      placeholder: 'caută o definiție',
      width: '100%',
    });

    $('#entryId').select2({
      ajax: { url: wwwRoot + 'ajax/getEntries.php', },
      minimumInputLength: 1,
      placeholder: 'caută o intrare',
      width: '100%',
    });

    $('#treeId').select2({
      ajax: { url: wwwRoot + 'ajax/getTrees.php', },
      minimumInputLength: 1,
      placeholder: 'caută un arbore',
      width: '100%',
    });

    $('#labelId').select2({
      ajax: { url: wwwRoot + 'ajax/getTags.php', },
      minimumInputLength: 1,
      placeholder: 'caută o etichetă',
      width: '100%',
    });

    $('.quickNav select').change(function(e) {
      $(this).closest('form').submit();
    });

    $('#advSearchModelTypes').select2();

    $('.calendar').datepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      keyboardNavigation: false,
      language: 'ro',
      todayBtn: 'linked',
      todayHighlight: true,
      weekStart: 1,
    });
  }

  init();
});
