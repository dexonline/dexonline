$(function() {

  var stem = null;

  function init() {
    stem = $('#stem').detach().removeAttr('id');
    $('#modelForm').submit(beforeSubmit);
    $('.addFormLink').click(appendForm);
  }

  function beforeSubmit() {
    // reactivate disabled inputs or they won't submit
    $(this).find('input[disabled]')
      .removeAttr('disabled');
  }

  function appendForm() {
    var inflId = $(this).data('inflId');
    var td = $(this).closest('td').next();
    var count = td.children().length;

    var p = stem.clone(true);
    p.children().eq(0).attr('name', 'forms_' + inflId + '_' + count);
    p.children().eq(1).attr('name', 'isLoc_' + inflId + '_' + count);
    p.children().eq(2).attr('name', 'recommended_' + inflId + '_' + count);
    p.appendTo(td);
    return false;
  }

  init();

});
