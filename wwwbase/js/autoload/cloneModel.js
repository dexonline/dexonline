$(function() {
  $('.bulk').click(function() {
    var checked = $(this).data('checked');
    $('input[name="lexemeId[]"]').prop('checked', checked);
  });
});
