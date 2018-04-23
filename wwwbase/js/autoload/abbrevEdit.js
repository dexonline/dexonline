$(function () {
  $('#lds-dual-ring').hide();
  $("#load").click(function () {
    var sourceId = $("#sourceDropDown option").filter(":selected").val();
    $('#frm_edit [name="sourceId"]').val(sourceId);
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/getAbbreviations.php",
      data: {"sourceId" : sourceId},
      dataType: "html",
      beforeSend: function(){
        $("#lds-dual-ring").show();
      },
      complete: function(){
        $('#lds-dual-ring').hide();
      },
      success: function (data)
      {
        $('#abbrevs').html(data);
      }
    });
  });
  
  $('#abbrevs').on('click', '#command-add', function(){
    prepareModalForm(0,0,0,0,'','','add');
    $('#edit_modal').modal('show');
  });

  $("#btn-save").click(function () {
    abbrevEdit();
  });
  
  $('#abbrevs').on('click', '[name="btn-edit"]', function(){
    var id = $(this).data('row-id');
    elem = $(this).closest("tr");
    
    var enf = elem.find('i').eq(0).data('checked');
    var amb = elem.find('i').eq(1).data('checked');
    var cs = elem.find('i').eq(2).data('checked');
    
    var sho = elem.find('td:nth-of-type(5)').html();
    var ir = elem.find('td:nth-of-type(6)').html();
    prepareModalForm(id, enf, amb, cs, sho, ir, 'edit');
    $('#edit_modal').modal('show');
  });
  
  function prepareModalForm(id, enf, amb, cs, sho, ir, act){
    elem = $('#frm_edit');
    elem.find('[name="abbrevId"]').val(id);
    elem.find('#action').val(act);
    
    elem.find('[name="enforced"]').prop('checked', enf);
    elem.find('[name="ambiguous"]').prop('checked', amb);
    elem.find('[name="caseSensitive"]').prop('checked', cs);
    
    elem.find('#short').val(sho);
    elem.find('#internalRep').val(ir);
  }

  function abbrevEdit() {
    data = $("#frm_edit").serializeArray();
    $.ajax({
      type: "POST",
      url: wwwRoot + "ajax/editAbbreviation.php",
      data: data,
      dataType: "json",
      success: function (response)
      {
        if (response.action === 'add'){
          $('#table-abbrevs tbody').append(response.html);
        } else if (response.action === 'edit') {
          $('#table-abbrevs tbody').find('#'+response.id).replaceWith(response.html);
        } else if (response.action === 'delete') {
          $('#table-abbrevs tbody').remove('#'+response.id);
        } else {
          
        }
        
        $('#edit_modal').modal('hide');
      }
    });
  }
});
