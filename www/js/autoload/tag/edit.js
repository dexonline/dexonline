$(function() {

  function init() {
    initSelect2('#parentId', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php', },
      allowClear: true,
      minimumInputLength: 1,
      placeholder: 'caută o etichetă',
      width: '100%',
    });

    $('.frequent-color').click(frequentColorClick);
  }

  function frequentColorClick() {
    var input = $($(this).data('target'));
    input.val($(this).data('value'));
  }

  init();
});
