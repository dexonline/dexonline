/* Duplicates some code from editEntry.js */
$(function() {

  function init() {

    $('#associateEntryIds').select2({
      ajax: { url: wwwRoot + 'ajax/getEntries.php', },
      minimumInputLength: 1,
      placeholder: 'alegeți una sau mai multe intrări',
      width: '100%',
    });

    $('#associateModal').on('shown.bs.modal', associateModalShown);
  }

  function associateModalShown() {
    // copy definition ids from checked checkboxes
    var checkboxes = $('input[name="selectedDefIds[]"]:checked');
    var ids = checkboxes.map(function() {return $(this).val(); });
    var idString = ids.get().join();

    $('input[name="associateDefinitionIds"]').val(idString);
    $('#associateEntryIds').select2('open');
  }

  init();
});
