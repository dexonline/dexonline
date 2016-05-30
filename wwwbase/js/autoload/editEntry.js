$(function() {

  function init() {

    initSelect2('#lexemIds', 'ajax/getLexemsById.php', {
      ajax: { url: wwwRoot + 'ajax/getLexems.php' },
      minimumInputLength: 1,
      templateSelection: formatLexemWithEditLink,
    });
            
    $('button[name="delete"]').click(function() {
      return confirm('Confirmați ștergerea acestei intrări?');
    });
  }

  init();
});
