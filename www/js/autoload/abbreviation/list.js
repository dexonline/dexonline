$(function () {
  const targetNode = $('#abbrevs')[0];
  const config = { childList: true };

  const doSort = function(mutationsList, observer) {
    // Use traditional 'for loops' for IE 11
    for(let mutation of mutationsList) {
        if (mutation.type === 'childList') {
            observer.disconnect();
            sortTable($('#table-abbrevs'));
        }
    }
  };

  const observer = new MutationObserver(doSort);

  function sortTable(table) {
    table.tablesorter({
      headerTemplate: '{content} {icon}',
      sortInitialOrder: 'asc',
      theme: 'bootstrap',
      widgets : [ "uitheme" ],
    });
    table.tablesorterPager({
      container: $("#abbrevsPager"),
      output: '{page}/{totalPages}',
      size: 50,
    });
  }

  var sd = $("#sourceDropdown");
  sd.on('change', function() {
    $('#frm_edit #sourceId').val(sd.val());
    $('#loadWarning').collapse('show');
    $.ajax({
      type: "POST",
      context: $("#load"),
      isLocal: true,
      url: wwwRoot + "ajax/getAbbreviations.php",
      data: {"sourceId" : sd.val()},
      dataType: "json",
      success: function (response)
      {
        observer.observe(targetNode, config);
        $('#abbrevs').html(response.html);
        $('#count').html(response.count);
        $('#debugAjax').append(response.debug);
        $('#loadWarning').collapse('hide');
      }
    });
  });

  $('#abbrevs').on('click', '#command-add', function() {
    prepareModalForm(new Abbrev(null, 'add', 'Adăugare'));
  });

  $('#abbrevs').on('click', '[name^="btn-edit"]', function() {
    prepareModalForm(new Abbrev($(this), 'edit', 'Editare'));
  });

  $('#abbrevs').on('click', '[name^="btn-trash"]', function() {
    prepareModalForm(new Abbrev($(this), 'delete', 'Ștergere'));
  });

  $(".commands").click(function (event) {
    abbrevEdit();
    event.preventDefault();
  });

  class Abbrev {
    constructor(elem, action, title) {
      if (elem !== null) {
        var tr = elem.closest("tr");
      }
      this.id = elem === null ? 0 : tr.data('row-id');
      this.enforced = elem === null ? 0 : tr.find('i').eq(0).data('checked');
      this.ambiguous = elem === null ? 0 : tr.find('i').eq(1).data('checked');
      this.caseSensitive = elem === null ? 0 : tr.find('i').eq(2).data('checked');
      this.short = elem === null ? '' : tr.find('td:nth-of-type(5)').html();
      this.internalRep = elem === null ? '' : tr.find('td:nth-of-type(6)').html();
      this.action = action;
      this.title = title;
    }
  }

  function prepareModalForm(obj){

    frm = $('#frm_edit');
    frm.find('#abbrevId').val(obj.id);
    frm.find('#action').val(obj.action);

    frm.find('[name="enforced"]').prop('checked', obj.enforced);
    frm.find('[name="ambiguous"]').prop('checked', obj.ambiguous);
    frm.find('[name="caseSensitive"]').prop('checked', obj.caseSensitive);

    frm.find('#short').val(obj.short);
    frm.find('#internalRep').val(obj.internalRep);
    frm.find('#message').hide().empty();

    tog = obj.action === 'delete';

    frm.find('#btn-delete').toggle(tog);
    frm.find('#btn-save').toggle(!tog);
    frm.find('#deleted').toggleClass('notClickable', tog);

    $('#edit_modal #title').text(obj.title + ' abreviere');
    $('#edit_modal').modal('show');
  }

  function abbrevEdit() {
    data = $("#frm_edit").serializeArray();
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/editAbbreviation.php",
      data: data,
      context: $(this),
      isLocal: true,
      dataType: "json",
      success: function (response)
      {
        if (response.action === 'add'){
          $('#table-abbrevs tbody').append(response.html);
          updateCounter($('#count'), +1);
        }
        else if (response.action === 'edit') {
          $('#'+response.id).replaceWith(response.html);
        }
        else if (response.action === 'delete') {
          $('#'+response.id).remove();
          $('#frm_edit #message').html(response.html).show();
          updateCounter($('#count'), -1);
        }
        else if (response.action === 'duplicate') {
          $('#frm_edit #message').html(response.html).show();
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
