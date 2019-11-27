$(function() {
  var stem;

  function init() {
    stem = $('#stem').detach().removeAttr('hidden');

    $('#addButton').click(addRow);
    $('#roleContainer').on('click', '.deleteButton', deleteRow);
  }

  function addRow() {
    console.log('yueah');
    stem.clone(true).appendTo('#roleContainer');
  }

  function deleteRow() {
    $(this).closest('tr').remove();
  }

  init();

});
