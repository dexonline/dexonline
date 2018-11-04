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
    var target = $(this).next();
    var inflId = $(this).data('inflId');
    var count = target.children().length;
    var suffix = inflId + '_' + count;

    var r = $('.fieldWrapper').first().clone(true).appendTo(target);
    r.find('input').eq(0).attr('name', 'forms_' + suffix).val('');
    r.find('input').eq(1).attr('name', 'recommended_' + suffix);

    return false;
  }

  init();

});
