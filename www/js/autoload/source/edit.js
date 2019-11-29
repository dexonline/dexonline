$(function() {

  var stem;

  function init() {
    stem = $('#stem').detach().removeAttr('hidden');

    $('#addButton').click(addRow);
    $('#authorContainer').on('click', '.deleteButton', deleteRow);

    Sortable.create(authorContainer, {
      handle: '.glyphicon-move',
	    animation: 150,
    });
  }

  function addRow() {
    // get the value of the last role select, possibly undefined
    var value = $('#authorContainer').find('select').last().val();

    var row = stem.clone(true);
    if (value) {
      row.find('select').val(value);
    }
    row.appendTo('#authorContainer');

    $('#authorHeader').removeAttr('hidden');
  }

  function deleteRow() {
    $(this).closest('tr').remove();
  }

  init();

});
