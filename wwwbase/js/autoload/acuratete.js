$(function() {
  $('#userId').select2({
    ajax: createUserAjaxStruct(),
    allowClear: true,
    initSelection: select2InitSelectionAjaxUserSingle,
    minimumInputLength: 3,
    width: '173px',
  });


  $('#deleteButton').click(function() {
    return confirm('Confirmați ștergerea proiectului?');
  });
});
