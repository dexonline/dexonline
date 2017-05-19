$(function() {

  function init() {
    initSelect2('#tagIds', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php' },
      minimumInputLength: 1,
      width: '100%',
    });
  }

  init();

});
