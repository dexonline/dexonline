$(function() {
  $('#donateOnlineEmail').focus();

  $('#donateOnline').submit(function() {
    var email = $('#donateOnlineEmail').val();
    if (email) {
      return true;
    } else {
      alert('Vă rugăm să completați adresa de email.');
      return false;
    }
  });

  $('.donateDetailLink').click(function() {
    $(this).parent().next().slideToggle();
    return false;
  });
});
