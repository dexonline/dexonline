$(function () {
  $(document).ajaxSend(function( event, request, settings ) {
      settings.context.addClass('running');
  });
  $(document).ajaxComplete(function( event, request, settings ) {
      settings.context.removeClass('running');
  });

  $("#load").click(function () {
    var sourceId = $("#sourceDropDown option").filter(":selected").val();
    var elem = $(this);
    $('#frm_edit #sourceId').val(sourceId);
    $.ajax({
      type: "POST",
      context: elem,
      url: wwwRoot + "ajax/getAbbreviations.php",
      data: {"sourceId" : sourceId},
      dataType: "html",
      success: function (response)
      {
        $('#abbrevs').html(response);
      }
    });
  });

  $('#abbrevs').on('click', '#command-add', function(){
    prepareModalForm(new Abbrev(null, 'add', 'Adăugare'));
  });

  $('#abbrevs').on('click', '[name="btn-edit"]', function(){
    prepareModalForm(new Abbrev($(this), 'edit', 'Editare'));
  });

  $('#abbrevs').on('click', '[name="btn-trash"]', function(){
    prepareModalForm(new Abbrev($(this), 'delete', 'Ștergere'));
  });

  $(".commands").click(function (event) {
    abbrevEdit();
    event.preventDefault();
  });

  function Abbrev(elem, action, title) {
    if (elem !== null){
      tr = elem.closest("tr");
    }
    this.id = elem === null ? 0 : tr.data('row-id');
    this.enforced = elem === null ? 0 : tr.data('enforced');
    this.ambiguous = elem === null ? 0 : tr.data('ambiguous');
    this.caseSensitive = elem === null ? 0 : tr.data('caseSensitive');
    this.html = elem === null ? 0 : tr.data('html');

    this.short = elem === null ? '' : tr.find('td:nth-of-type(6)').html();
    this.internalRep = elem === null ? '' : tr.find('td:nth-of-type(7)').html();
    this.action = action;
    this.title = title;
  }

  function prepareModalForm(obj) {
    frm = $('#frm_edit');
    frm.find('#abbrevId').val(obj.id);
    frm.find('#action').val(obj.action);

    frm.find('[name="enforced"]').prop('checked', obj.enforced);
    frm.find('[name="ambiguous"]').prop('checked', obj.ambiguous);
    frm.find('[name="caseSensitive"]').prop('checked', obj.caseSensitive);
    frm.find('[name="html"]').prop('checked', obj.html);

    frm.find('#short').val(obj.short);
    frm.find('#internalRep').val(obj.internalRep);
    frm.find('#message').hide();

    tog = obj.action === 'delete';

    frm.find('#btn-delete').toggle(tog);
    frm.find('#btn-save').toggle(!tog);
    frm.find('input').attr('disabled', tog);

    $('#edit_modal #title').text(obj.title + ' abreviere');
    $('#edit_modal').modal('show');
  }

  function abbrevEdit() {
    data = $("#frm_edit").serializeArray();
    var elem = $("#edit_modal .commands");
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/editAbbreviation.php",
      data: data,
      context: elem,
      dataType: "json",
      success: function (response)
      {
        if (response.action === 'add'){
          $('#table-abbrevs tbody').append(response.html);
          updateCounter($('#abbrevCount'), +1);
        }
        else if (response.action === 'edit') {
          $('#'+response.id).replaceWith(response.html);
        }
        else if (response.action === 'delete') {
          $('#'+response.id).remove();
          $('#message .notice-body').html(response.html);
          $('#message').show();
          updateCounter($('#abbrevCount'), -1);
        }
        else if (response.action === 'duplicate') {
          $('#message .notice-body').html(response.html);
          $('#message').show();
        }

        if (response.status === 'finished'){
          $('#edit_modal').modal('hide');
        }
      }
    });
  }

  function updateCounter(elem, amount){
      count = parseInt(elem.html());
      elem.text(count+amount);
  }
});
