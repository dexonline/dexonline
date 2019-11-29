$(function() {
  var sd = $("#sourceDropdown");
  sd.on('change', function(e) {
    $.ajax({
      type: "POST",
      context: $("#load"),
      isLocal: true,
      url: wwwRoot + "ajax/getTypos.php",
      data: {"sourceId" : sd.val()},
      dataType: "json",
      success: function(response) {
        $('#typosPanelContent').html(response.html);
        $('#count').html(response.count);
        $('#debugAjax').append(response.debug);
      },
      error: function() { alert("Nu pot descărca lista de definiții.") },
      timeout: 3000,
    });
  });
});
