$(document).ready(function() {

  // toggle checked/unchecked for all checkboxes in list
  $('#toggleAll').change(function() {
    var status = $(this).is(':checked');
    $('.objCheckbox').prop('checked', status);
    $('#chng').text(status ? objCount : '0');
    $('#de').prop('hidden', !status);
  });

  // counting unchecked objects, changing some fields accordingly
  $('.objCheckbox').change(function() {
    var unchecked = $('.objCheckbox').not(':checked');
    $('#toggleAll').prop('checked', !unchecked.length);
    checkedCount = objCount - unchecked.length;
    $('#chng').text(checkedCount);
    $('#de').prop('hidden', hideAmountPreposition(checkedCount));
  });

  // toggle between DeletionsOnly, InsertionsOnly and All modifications
  $('input[name="radiodiff"]').click(function() {
    var selValue = $(this).val();
    $('#panel-body ins').toggle(selValue != 'del');
    $('#panel-body del').toggle(selValue != 'ins');
  });

  // getting the array for unchecked objects to be excluded from replace
  $('[name="saveButton"]').click(function() {
    var uncheckedIds = $('.objCheckbox').not(':checked').map(function() {
      return this.value;
    }).get().join(',');
    $('input[name="excludedIds"]').val(uncheckedIds);
  });

  // setting variables
  var objCount = parseInt($('#chng').text());
  var checkedCount = 0;

  function hideAmountPreposition(amount) {
    return (amount % 100) < 20;
  }

});
