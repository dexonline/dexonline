$(function() {

  $('.tree .deleteLink').click(deleteTree);

  function deleteTree() {
    var link = $(this);
    var treeId = link.data('id');

    $.get(wwwRoot + 'ajax/deleteTree.php?id=' + treeId)
      .done(function() {
        link.closest('.tree').slideUp();
      })
      .fail(function() {
        alert('A apărut o problemă la comunicarea cu serverul. Arborele nu a fost încă șters.');
      });

    return false;
  }

});
