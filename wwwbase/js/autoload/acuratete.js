$(function() {
  initSelect2('#userId', 'ajax/getUsersById.php', {
    ajax: createUserAjaxStruct(),
    minimumInputLength: 3,
    placeholder: '(opțional)',
    width: '180px',
  });
            
  $('#deleteButton').click(function() {
    return confirm('Confirmați ștergerea proiectului?');
  });
});
