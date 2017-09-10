$(function() {

  initSelect2('#tagIds', 'ajax/getTagsById.php', {
    ajax: { url: wwwRoot + 'ajax/getTags.php' },
    minimumInputLength: 1,
  });

});
