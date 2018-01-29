$(function() {

  function init() {

    initSelect2('#entryIds', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      minimumInputLength: 1,
      templateSelection: formatEntryWithEditLink,
    });

    $('#deleteMeaningButton').popover({
      container: 'body',
      content: function() {
        return $('#deletePopoverContent').html();
      },
      html: true,
      trigger: 'click',
    });

    $('button[name="delete"]').click(function() {
      return confirm('Confirmați ștergerea acestui arbore?');
    });
  }

  init();
});
