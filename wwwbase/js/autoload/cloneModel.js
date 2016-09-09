$(function() {
  $('.bulk').click(function() {
    var checked = $(this).data('checked');
    $('input[name="lexemId[]"]').prop('checked', checked);
  });
});
