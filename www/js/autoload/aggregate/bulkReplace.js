$(document).ready(function () {

  var objCount = parseInt($('#chng').text());
  var checkboxes = {
    structured : $('.objCheckbox[data-type="structured"]').length,
    unstructured : $('.objCheckbox[data-type="unstructured"]').length,
  };
  var unchecked = {
    structured : 0,
    unstructured : 0,
  };

  // toggle all checkboxes in list according to type (structured /
  // unstructured)
  $('.toggleAll').change(function () {
    var type = $(this).data('type');
    var status = $(this).is(':checked');
    $('.objCheckbox[data-type="' + type + '"]').prop('checked', status);
    unchecked[type] = status ? 0 : checkboxes[type];
    changeFields();
  });

  // counting unchecked objects, changing some fields accordingly
  $('.objCheckbox').change(function () {
    var type = $(this).data('type');
    unchecked[type] += $(this).is(':checked') ? -1 : +1;
    var global = $('.toggleAll[data-type="' + type + '"]');
    global.prop('checked', !unchecked[type]);
    changeFields();
  });

  // toggle between DeletionsOnly, InsertionsOnly and All modifications
  $('input[name="radiodiff"]').click(function () {
    var selValue = $(this).val();
    $('#card-body ins').toggle(selValue != 'del');
    $('#card-body del').toggle(selValue != 'ins');
  });

  // getting the array for unchecked objects to be excluded from replace
  $('[name="saveButton"]').click(function () {
    var uncheckedIds = $('.objCheckbox').not(':checked').map(function () {
      return this.value;
    }).get().join(',');
    $('input[name="excludedIds"]').val(uncheckedIds);
  });

  function changeFields() {
    var checkedCount = objCount - unchecked['structured'] - unchecked['unstructured'];
    $('#chng').text(checkedCount);
    $('#de').prop('hidden', hideAmountPreposition(checkedCount));
  }

  function hideAmountPreposition(amount) {
    return (amount % 100) < 20;
  }

});
