$(function() {
  var sd = $("#sourceDropdown");
  sd.on('change', function(e) {
    $.ajax({
      type: "POST",
      context: $("#load"),
      isLocal: true,
      url: wwwRoot + "ajax/getDefinitionsForMissingRareGlyphsTags.php",
      data: {"sourceId" : sd.val()},
      dataType: "json",
      success: function(response) {
        $('#missingRareGlyphsTagContent').html(response.html);
        $('#count').html(response.count);
        $('#toggleAll').change();
        $('#debugAjax').append(response.debug);
      },
      error: function() { alert("Nu pot descărca lista de definiții.") },
      timeout: 30000,
    });
  });
});

$(document).ready(function() {

  // toggle checked/unchecked for all checkboxes in list
  $('.toggleAll').on('change', function() {
    toggleAll(this.checked);
    changeFields();
  });

  // counting unchecked objects, changing some fields accordingly
  $('#missingRareGlyphsTagContent').on('change', '.objCheckbox', function() {
    changeFields();
    $('.toggleAll').prop('checked', !unchecked);
  });

  // getting the array for unchecked objects to be excluded from replace
  $('#btnSave').click(function() {
    var uncheckedIds = checkboxes.not(':checked').map(function() {
      return this.value;
    }).get().join(',');
    $('input[name="excludedIds"]').val(uncheckedIds);
  });

  // setting variables
  var objCount;
  var unchecked;
  var checkboxes = $('.objCheckbox');

  function countUnchecked() {
    unchecked = $('.objCheckbox').not(':checked').length;
  }

  function toggleAll(status) {
    $('#missingRareGlyphsTagContent .objCheckbox').prop('checked', status);
  }

  function changeFields() {
    objCount = parseInt($('#count').text());
    countUnchecked();
    var toBeChanged = objCount - unchecked;
    $('#chng').text(toBeChanged);
    $('#de').prop('hidden', hideAmountPreposition(toBeChanged));
  }

  function hideAmountPreposition(amount) {
    return (amount % 100) < 20;
  }

  function disableCheck(){
    $('.toggleAll').prop('disabled', true).removeProp('checked');
  }

  // counting checkboxes
  if (checkboxes === 0) {
    $('#labelAll').addClass('disabled');
    disableCheck();
  }

  changeFields();

});
