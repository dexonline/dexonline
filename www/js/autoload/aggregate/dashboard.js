$(function() {
  function init() {
    $('#lexemeId').select2({
      ajax: { url: wwwRoot + 'ajax/getLexemes.php', delay: 500, },
      minimumInputLength: 1,
      placeholder: 'caută un lexem',
      width: '100%',
    });

    $('#definitionId').select2({
      ajax: { url: wwwRoot + 'ajax/getDefinitions.php', delay: 500, },
      templateResult: formatDefinition,
      templateSelection: formatDefinition,
      minimumInputLength: 1,
      placeholder: 'caută o definiție',
      width: '100%',
    });

    $('#entryId').select2({
      ajax: { url: wwwRoot + 'ajax/getEntries.php', delay: 500, },
      minimumInputLength: 1,
      placeholder: 'caută o intrare',
      width: '100%',
    });

    $('#treeId').select2({
      ajax: { url: wwwRoot + 'ajax/getTrees.php', delay: 500, },
      minimumInputLength: 1,
      placeholder: 'caută un arbore',
      width: '100%',
    });

    $('#labelId').select2({
      ajax: { url: wwwRoot + 'ajax/getTags.php', delay: 500, },
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

    var pfm = $("#panelFlexModels");
    pfm.on('show.bs.collapse', function(e) {
      $.ajax({
        type: "POST",
        url: wwwRoot + "ajax/getDashboardFlexModels.php",
        dataType: "html",
        success: function(response) {
          $('#flexModelsContent').html(response);
          $.getScript(wwwRoot + "js/modelDropdown.js")
            .done(function() { })
            .fail(function() {alert("Nu pot descărca scriptul.");})
        },
        error: function() { alert("Nu pot descărca lista de modele.") },
        timeout: 3000,
      });
    });

    pfm.on('hidden.bs.collapse', function(e) {
      $('#flexModelsContent').html('');
    });

    $('#panelBulkReplace').on('show.bs.collapse', function(e) {
      $.ajax({
        type: "POST",
        url: wwwRoot + "ajax/getBulkReplace.php",
        dataType: "html",
        success: function (response)
        {
          $('#bulkReplaceContent').html(response);
          $('#sourceDropdownBulk').select2({
            templateResult: formatSource,
            templateSelection: formatSource,
          });
        },
        error: function() { alert("Nu pot încărca modulul.") },
        timeout: 3000,
      });
    });

    $('#panelBulkReplace').on('hidden.bs.collapse', function(e) {
      $('#bulkReplaceContent').html('');
    });

  }

  init();
});
