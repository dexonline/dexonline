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
