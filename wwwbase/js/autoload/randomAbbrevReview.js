$(function() {
  var numLeft = null;
  var clickTracker = null;
  
  function init() {
    numLeft = parseInt($('#numAmbiguities').text());
    $('label').click(pushAbbrevButton);
  }

  function pushAbbrevButton() {
    var span = $(this).siblings('span');
    var state = parseInt($(this).data('answer'));
    if (state) {
      span.removeClass('text-danger').addClass('text-success');
    } else {
      span.removeClass('text-success').addClass('text-danger');
    }
    if (!span.data('clicked')) {
      span.data('clicked', '1');
      numLeft--;
      if (!numLeft) {
        $('button[name="saveButton"]').removeAttr('disabled');
      }
    }
  }

  init();

});
