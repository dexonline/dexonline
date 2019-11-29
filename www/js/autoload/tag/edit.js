$(function() {

  function init() {
    initSelect2('#parentId', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php', delay: 500, },
      allowClear: true,
      minimumInputLength: 1,
      placeholder: 'caută o etichetă',
      width: '100%',
    });

    $('#color').closest('.colorpicker-component').colorpicker({
      align: 'left',
      colorSelectors: collectFrequentColors('#frequent-color'),
      format: 'hex',
    });
    $('#background').closest('.colorpicker-component').colorpicker({
      align: 'left',
      colorSelectors: collectFrequentColors('#frequent-background'),
      format: 'hex',
    });
  }

  function collectFrequentColors(sel) {
    var result = [];
    $(sel).find('div').each(function() {
      result.push($(this).text());
    });
    return result;
  }

  init();
});
