$(function() {
  initSelect2('#userId', 'ajax/getUsersById.php', {
    ajax: createUserAjaxStruct(),
    minimumInputLength: 3,
    placeholder: 'alegeți un utilizator',
  });

  $('#deleteButton').click(function() {
    return confirm('Confirmați ștergerea proiectului?');
  });
});
