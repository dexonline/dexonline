$(function() {

  function init() {
    $('.modelRadio').click(radioClick);
    $('.defLink').click(defClick);
  }

  function radioClick() {
    // show or hide the comment field
    var comment = $(this).parent().siblings('.bulkLabelComment');
    if ($(this).val() == '0') {
      comment.slideDown();
    } else {
      comment.slideUp();
    }

    // show the corresponding paradigm
    var paradigms = $(this).parent().siblings('.paradigms').children();
    var order = parseInt($(this).data('order'));
    paradigms.slideUp();
    paradigms.eq(order).slideDown();
  }

  function defClick(anchor) {
    var tmp = $(this).data('otherText');
    $(this).data('otherText', $(this).text());
    $(this).text(tmp);

    $(this).siblings('.blDefinitions').slideToggle();
    return false;
  }

  init();

});
