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
      url: wwwRoot + "ajax/getPageIndex.php",
      data: {"sourceId" : sourceId},
      dataType: "html",
      success: function (response)
      {
        $('#pageIndexes').html(response);
      }
    });
  });

  $('#pageIndexes').on('click', '#command-add', function(){
    prepareModalForm(new PageIndex(null, 'add', 'Adăugare'));
  });

  $('#pageIndexes').on('click', '[name="btn-edit"]', function(){
    prepareModalForm(new PageIndex($(this), 'edit', 'Editare'));
  });

  $('#pageIndexes').on('click', '[name="btn-trash"]', function(){
    prepareModalForm(new PageIndex($(this), 'delete', 'Ștergere'));
  });

  $(".commands").click(function (event) {
    pageIndexEdit();
    event.preventDefault();
  });

  function PageIndex(elem, action, title) {
    if (elem !== null){
      tr = elem.closest("tr");
    }
    this.id = elem === null ? 0 : tr.data('row-id');

    this.volume = elem === null ? '' : tr.find('td:nth-of-type(2)').html();
    this.page = elem === null ? '' : tr.find('td:nth-of-type(3)').html();
    this.word = elem === null ? '' : tr.find('td:nth-of-type(4)').html();
    this.number = elem === null ? '' : tr.find('td:nth-of-type(5)').html();

    this.action = action;
    this.title = title;
  }

  function prepareModalForm(obj){

    frm = $('#frm_edit');
    frm.find('#pageIndexId').val(obj.id);
    frm.find('#action').val(obj.action);

    frm.find('#volume').val(obj.volume);
    frm.find('#page').val(obj.page);
    frm.find('#word').val(obj.word);
    frm.find('#number').val(obj.number);

    frm.find('#message').hide().empty();

    tog = obj.action === 'delete';

    frm.find('#btn-delete').toggle(tog);
    frm.find('#btn-save').toggle(!tog);
    frm.find('#deleted').toggleClass('notClickable', tog);

    $('#edit_modal #title').text(obj.title + ' index');
    $('#edit_modal').modal('show');
  }

  function pageIndexEdit() {
    data = $("#frm_edit").serializeArray();
    var elem = $("#edit_modal .commands");
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/editPageIndex.php",
      data: data,
      context: elem,
      dataType: "json",
      success: function (response)
      {
        if (response.action === 'add'){
          $('#table-page-index tbody').append(response.html);
          updateCounter($('#pageIndexCount'), +1);
        }
        else if (response.action === 'edit') {
          $('#'+response.id).replaceWith(response.html);
        }
        else if (response.action === 'delete') {
          $('#'+response.id).remove();
          $('#frm_edit #message').html(response.html).show();
          updateCounter($('#pageIndexCount'), -1);
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
