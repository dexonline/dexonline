$(function() {
  var menuBar = null;
  var stemLi = null;
  var sel = null; // selected <li>

  function init() {
    $('.expand').click(toggleSubtree);
    $('#link-expand-all').click(toggleAll);

    // collapse all subtrees
    $('#tag-tree ul ul').hide();
  }

  function toggleSubtree() {
    $(this).siblings('ul').stop().slideToggle();
    $(this).toggleClass('expanded');
  }

  function toggleAll() {
    var isExpanded = $(this).data('isExpanded');

    if (isExpanded) {
      $('#tag-tree .expand').removeClass('expanded');
      $('#tag-tree ul ul').stop().slideUp();
      $(this).data('isExpanded', false);
    } else {
      $('#tag-tree .expand').addClass('expanded');
      $('#tag-tree ul ul').stop().slideDown();
      $(this).data('isExpanded', true);
    }

    return false;
  }

  init();
});
