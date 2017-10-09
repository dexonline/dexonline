$(function() {

  function init() {
    $('.frequentObjects').each(function() {
    });

    $('.frequentObjectDelete').click(frequentObjectDelete);
  }

  function frequentObjectDelete() {
    $(this).closest('.btn-group').remove();
    
  }

  init();

});

function formatObjectWithFrequentLink(obj) {
  var link = ' <a class="glyphicon glyphicon-star frequentObjectAddLink" ' +
      'href="#" title="adaugă la lista frecvent folosită"></a>';

  return $('<span>' + obj.text + link + '</span>');
}

