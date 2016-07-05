$(function() {

  function init() {

    initSelect2('#entryIds', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      minimumInputLength: 1,
      templateSelection: formatEntryWithEditLink,
    });
            
    $('button[name="delete"]').click(function() {
      return confirm('Confirmați ștergerea acestui arbore?');
    });
  }

  init();
});
