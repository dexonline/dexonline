$(function() {
  function init() {
    $('.deleteRuleLink').click(function() {
      return confirm('Confirmați ștergerea acestei reguli?');
    });

    $('.tagLookup').select2({
      ajax: { url: wwwRoot + 'ajax/getTags.php', delay: 500, },
      allowClear: true,
      minimumInputLength: 1,
      placeholder: 'caută o etichetă',
      width: '300px',
    });
  }

  init();
});
