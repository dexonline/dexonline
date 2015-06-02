$(document).ready(function() {
  var struct_getUsers = {
    data: function(term, page) { return { q: term, privilege: 1, nick: 1 }; },
    dataType: 'json',
    results: function(data, page) { return { results: data }; },
    url: wwwRoot + 'ajax/getUsers.php'
  };
  $('#curators').select2({
    ajax: struct_getUsers,
    multiple: true,
    minimumInputLength: 2,
    width: '500px',
    initSelection: function(element, callback) {
      callback(curators);
    }
  }).select2('val', curators_ids);
});
