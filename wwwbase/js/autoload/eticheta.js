$(function() {

  function init() {
    initSelect2('#parentId', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php', },
      allowClear: true,
      minimumInputLength: 1,
      placeholder: 'caută o etichetă',
      width: '100%',
    });
  }

  init();
});
