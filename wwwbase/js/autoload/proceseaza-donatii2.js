$(function() {

  $('input[name="processTicketId[]"]').change(toggleMessageCheckbox);

  // The send email checkbox is conditioned on the process ticket checkbox.
  function toggleMessageCheckbox() {
    var parentDiv = $(this).closest('div.checkbox');
    var next = parentDiv.next();
    if (next.is('div.checkbox')) {
      var cb = next.find('input[type="checkbox"]');
      if ($(this).is(':checked')) {
        cb.prop('disabled', false);
      } else {
        cb.prop('disabled', true).prop('checked', false);
      }
    }
  }

});
