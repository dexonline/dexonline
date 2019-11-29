$(document).ready(function () {

  // toggle checked/unchecked for all checkboxes in list
  // according to secondClass ( structured : unstructured)
  $('.toggleAll').on('click', function () {
    var secondClass = this.className.split(' ')[1];
    var status = $(this).is(':checked');
    toggleAll(secondClass, status);
    unchecked[secondClass] = status ? 0 : checkboxes[secondClass];
    changeFields();
  });

  // counting unchecked objects, changing some fields accordingly
  $('#bulkReplaceContent').on('click', '.objCheckbox', function () {
    var secondClass = this.className.split(' ')[1];
    var count = countUnchecked('.' + secondClass);
    unchecked[secondClass] = count;
    $('.toggleAll' + '.' + (secondClass)).prop('checked', !count);
    changeFields();
  });

  // toggle between DeletionsOnly, InsertionsOnly and All modifications
  $('input[name="radiodiff"]').click(function () {
    var selValue = $(this).val();
    $('#panel-body ins').toggle(selValue != 'del');
    $('#panel-body del').toggle(selValue != 'ins');
  });

  // getting the array for unchecked objects to be excluded from replace
  $('[name="saveButton"]').click(function () {
    var uncheckedIds = $('.objCheckbox').not(':checked').map(function () {
      return this.value;
    }).get().join(',');
    $('input[name="excludedIds"]').val(uncheckedIds);
  });

  // setting variables
  var objCount = parseInt($('#chng').text());
  var checkboxes = { structured : $('.objCheckbox.structured').length,
                     unstructured : $('.objCheckbox.unstructured').length};
  var unchecked = { structured : 0,
                    unstructured : 0 };

  function countUnchecked(cls) {
    return $('.objCheckbox' + cls).not(':checked').length;
  }

  function toggleAll(secondClass, status) {
    $('.objCheckbox' + '.' + secondClass).prop('checked', status);
  }

  function changeFields() {
    var checkedCount = objCount - unchecked['structured'] - unchecked['unstructured'];
    $('#chng').text(checkedCount);
    $('#de').prop('hidden', hideAmountPreposition(checkedCount));
  }

  function hideAmountPreposition(amount) {
    return (amount % 100) < 20;
  }

  function disableCheck(secondClass){
    $('.toggleAll' + '.' + secondClass).prop('disabled', true).removeProp('checked');
  }

  // counting checkboxes
  if (checkboxes['structured'] === 0) {
    $('#labelStructured').addClass('disabled');
    disableCheck('structured');
  }
  if (checkboxes['unstructured'] === 0) {
    $('#labelUnstructured').addClass('disabled');
    disableCheck('unstructured');
  }

});
