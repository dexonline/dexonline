function loadRestrictionMenu(modelType, selectedValues) {
  formData = { 'modelType' : modelType, 'selectedValues' : selectedValues }
  $.ajax({
    type: "POST",
    context: $(this),
    isLocal: true,
    url: wwwRoot + "ajax/getRestrictionsForModelType.php",
    data: formData,
    dataType: "json",
    success: function(response) {
      $('#restrictionMenu').html(response.html);
      $('#debugAjax').append(response.debug);
    },
    error: function() { alert("Nu pot descărca restricțiile.") },
    timeout: 30000,
  });
}
