$(function() {
  function init() {
    meaningTreeRenumber();
  }

  function meaningTreeRenumberHelper(node, prefix) {
    node.children('li').each(function(i) {
      var c = $(this).children('.meaningContainer');
      var s = prefix + (prefix ? '.' : '') + (i + 1);
      c.find('.bc').text(s);
      $(this).children('ul').each(function() {
        meaningTreeRenumberHelper($(this), s);
      });
    });
  }

  function meaningTreeRenumber() {
    $('.meaningTree').each(function() {
      meaningTreeRenumberHelper($(this), '');
    });
  }

  init();
});
