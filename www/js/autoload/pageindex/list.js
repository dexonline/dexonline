$(function () {
  const targetNode = $('#pageindex')[0];
  const config = { childList: true };

  const doSort = function(mutationsList, observer) {
    // Use traditional 'for loops' for IE 11
    for(let mutation of mutationsList) {
        if (mutation.type === 'childList') {
            observer.disconnect();
            sortTable($('#table-pageindex'));
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
      container: $("#pageindexPager"),
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
      url: wwwRoot + "ajax/getPageIndex.php",
      data: {"sourceId" : sd.val()},
      dataType: "json",
      success: function (response)
      {
        observer.observe(targetNode, config);
        $('#pageindex').html(response.html);
        $('#count').html(response.count);
        $('#debugAjax').append(response.debug);
        $('#loadWarning').collapse('hide');
      }
    });
  });

  $('#pageindex').on('click', '#command-add', function() {
    prepareModalForm(new PageIndex(null, 'add', 'Adăugare'));
  });

  $('#pageindex').on('click', '[name^="btn-edit"]', function() {
    prepareModalForm(new PageIndex($(this), 'edit', 'Editare'));
  });

  $('#pageindex').on('click', '[name^="btn-trash"]', function() {
    prepareModalForm(new PageIndex($(this), 'delete', 'Ștergere'));
  });

  $(".commands").click(function (event) {
    pageindexEdit();
    event.preventDefault();
  });

  class PageIndex {
    constructor(elem, action, title) {
      if (elem !== null) {
        var tr = elem.closest("tr");
      }
      this.id = elem === null ? 0 : tr.data('row-id');
      this.volume = elem === null ? 0 : tr.find('td:nth-of-type(2)').html();
      this.page = elem === null ? 0 : tr.find('td:nth-of-type(3)').html();
      this.word = elem === null ? 0 : tr.find('td:nth-of-type(4)').html();
      this.number = elem === null ? '' : tr.find('td:nth-of-type(5)').html();
      this.action = action;
      this.title = title;
    }
  }

  function prepareModalForm(obj){

    frm = $('#frm_edit');
    frm.find('#pageindexId').val(obj.id);
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

    $('#edit_modal #title').text(obj.title + ' index pagini');
    $('#edit_modal').modal('show');
  }

  function pageindexEdit() {
    data = $("#frm_edit").serializeArray();
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/editPageIndex.php",
      data: data,
      context: $(this),
      isLocal: true,
      dataType: "json",
      success: function (response)
      {
        if (response.action === 'add'){
          $('#table-pageindex tbody').append(response.html);
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
