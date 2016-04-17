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
      span.css('border-bottom', '2px solid green');
    } else {
      span.css('border-bottom', '2px solid red');
    }
    if (!span.data('clicked')) {
      span.data('clicked', '1');
      numLeft--;
      if (!numLeft) {
        $('#submitButton').removeAttr('disabled');
      }
    }
  }

  init();

});
