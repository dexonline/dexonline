$(function() {

  function tabCmp(a, b) {
    var tabA = $(a).find('input').val();
    var tabB = $(b).find('input').val();
    return parseInt(tabA) - parseInt(tabB);
  }

  /* Sorts the tab list group in increasing tab order. */
  $('#restore-tab-order-link').click(function() {
    $('#tab-order')
      .children()
      .sort(tabCmp)
      .appendTo($('#tab-order'));
    return false;
  });

});
