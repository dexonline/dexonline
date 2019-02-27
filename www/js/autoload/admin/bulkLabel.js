$(function() {

  function init() {
    $('.modelRadio').click(radioClick);
  }

  function radioClick() {
    // show the corresponding paradigm
    $(this).closest('.panel-body').find('.paradigm').stop().slideUp();
    $('#' + $(this).data('paradigmId')).stop().slideDown();
  }

  init();

});
