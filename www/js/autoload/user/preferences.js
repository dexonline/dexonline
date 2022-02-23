$(function() {

  function tabCmp(a, b) {
    var tabA = $(a).data('defaultOrder');
    var tabB = $(b).data('defaultOrder');
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
