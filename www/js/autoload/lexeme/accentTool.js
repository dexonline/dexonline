$(function() {

  function init() {
    $('.apLetter').click(letterClick);
    $('input[type="checkbox"]').click(noAccentClick);
  }

  function letterClick() {
    var input = $(this).parent().prev();
    var order = parseInt($(this).data('order'));
    var old = parseInt(input.val());

    if (old != -1) {
      // remove old apostrophe
      var oldLetter = $(this).parent().children().eq(old);
      oldLetter.text(oldLetter.text().substring(1));
    }

    // set new apostrophe
    $(this).text("'" + $(this).text());
    input.val(order);

    // uncheck the "no accent" checkbox
    $(this).parent().next().find('input[type="checkbox"]').attr('checked', false);
  }

  function noAccentClick() {
    if ($(this).is(':checked')) {
      var input = $(this).closest('span').prev().prev();
      var old = parseInt(input.val());

      if (old != -1) {
        // remove old apostrophe
        var oldLetter = $(this).closest('span').prev().children().eq(old);
        oldLetter.text(oldLetter.text().substring(1));
        input.val(-1);
      }
    }
  }

  init();
});
