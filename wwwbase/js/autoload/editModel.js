$(function() {

  function init() {
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

    var d = td.children('div').first().clone(true).appendTo(td);
    d.children('input').attr('name', 'forms_' + inflId + '_' + count).val('');

    td = td.next();
    d = td.children('div').first().clone(true).appendTo(td);
    d.children('input').attr('name', 'isLoc_' + inflId + '_' + count);

    td = td.next();
    d = td.children('div').first().clone(true).appendTo(td);
    d.children('input').attr('name', 'recommended_' + inflId + '_' + count);

    return false;
  }

  init();

});
