$(function() {

  $('.modelRadio').click(function() {
    // show the corresponding paradigm
    $(this).closest('.card-body').find('.paradigm').hide();
    $('#' + $(this).data('paradigmId')).removeAttr('hidden').show();
  });

});
