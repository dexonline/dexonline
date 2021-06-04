$(function() {
  $('#checkAll').click(function() {
    var anyUnchecked = $('input[name="lexemeId[]"]:not(:checked)').length;

    if (anyUnchecked) {
      $('input[name="lexemeId[]"]').prop('checked', true);
    } else {
      $('input[name="lexemeId[]"]').removeProp('checked');
    }
  });
});
