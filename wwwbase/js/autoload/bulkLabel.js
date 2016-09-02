$(function() {

  function init() {
    $('.modelRadio').click(radioClick);
    $('.defLink').click(defClick);
  }

  function radioClick() {
    // show or hide the comment field
    var comment = $(this).parent().siblings('.bulkLabelComment');
    if ($(this).val() == '0') {
      comment.stop().slideDown();
    } else {
      comment.stop().slideUp();
    }

    // show the corresponding paradigm
    var paradigms = $(this).parent().siblings('.paradigms').children();
    var order = parseInt($(this).data('order'));
    paradigms.stop().slideUp();
    paradigms.eq(order).stop().slideDown();
  }

  function defClick(anchor) {
    var tmp = $(this).data('otherText');
    $(this).data('otherText', $(this).text());
    $(this).text(tmp);

    $(this).siblings('.blDefinitions').stop().slideToggle();
    return false;
  }

  init();

});
