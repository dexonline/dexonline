$(function() {
  initSelect2('#userId', 'ajax/getUsersById.php', {
    ajax: createUserAjaxStruct(),
    minimumInputLength: 3,
    placeholder: '(opțional)',
  });

  $('#deleteButton').click(function() {
    return confirm('Confirmați ștergerea proiectului?');
  });
});
