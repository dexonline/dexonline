$(function() {

  function init() {

    initSelect2('#entryIds', 'ajax/getEntriesById.php', {
      ajax: { url: wwwRoot + 'ajax/getEntries.php' },
      minimumInputLength: 1,
      templateSelection: formatEntryWithEditLink,
    });
            
    initSelect2('#tagIds', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php' },
      minimumInputLength: 1,
    });

    $('#deleteMeaningButton').popover({
      container: 'body',
      content: function() {
        return $('#deletePopoverContent').html();
      },
      html : true,
      trigger : 'click',
    });

    $('button[name="delete"]').click(function() {
      return confirm('Confirmați ștergerea acestui arbore?');
    });
  }

  init();
});
