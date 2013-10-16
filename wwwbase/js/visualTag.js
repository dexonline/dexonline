jQuery(document).ready(function() {
  
  var jcrop_api;
  var coords = new Object();
  var gridOptions = {
    add: false,
    search: false,
    edittitle: 'editează',
    deltitle: 'șterge',
    refreshtitle: 'reîncarcă'
  };
  var editOptions = {
    reloadAfterSubmit: true,
    closeAfterEdit: true,
    closeOnEscape: true,
    afterSubmit: checkServerResponse
  };
  var addOptions = {};
  var delOptions = {
    afterSubmit: checkServerResponse,
    closeOnEscape: true,
    reloadAfterSubmit: true
  };
  var imagesTable = function(revised, elementId, pagerId, message) {
    $('#' + elementId).jqGrid({
      url: wwwRoot + 'ajax/visualGetImages.php',
      postData: {revised: revised},
      datatype: 'json',
      cmTemplate: {sortable: false},
      colNames: ['Link Imagine', 'Id imagine', 'Lexeme asociat imaginii','Id User', 'User', 'Lățime', 'Înălțime', 'Ultima Modificare'],
      colModel: [
        {name: 'link', index: 'link', width: 100},
        {name: 'imageId', index: 'imageId', hidden: true},
        {name: 'lexeme', index: 'lexeme', width: 160, align: 'center'},
        {name: 'userId', index: 'userId', hidden: true},
        {name: 'user', index: 'user', width: 100, align: 'center'},
        {name: 'width', index: 'width', width: 70, align: 'center'},
        {name: 'height', index: 'height', width: 70, align: 'center'},
        {name: 'latestMod', index: 'latestMod', width: 100, align: 'center'}
      ],
      rowNum: 20,
      recreateForm: true,
      width: '700px',
      height: '100%',
      rowList: [20, 50, 100, 200],
      pager: $('#' + pagerId),
      viewrecords: true,
      caption: 'Imagini a căror etichetare este ' + message,
      ondblClickRow: function(rowid) {
        $('#imgToTag').val($(this).jqGrid('getCell', rowid, 'imageId'));
        $('form').submit();
      }
    });
  }
  
  initJcrop();
  resetAllFields();

  function initJcrop() {
    $('#jcrop').Jcrop({
      boxHeight: 500,
      boxWidth: 500,
      onSelect: setCoords,
      onChange: setCoords
    }, function() {
      jcrop_api = this;
    });
  }

  function setCoords(c) {
    // Calculates the centre of the selection
    coords.cx = Math.round((2 * c.x + c.w) / 2);
    coords.cy = Math.round((2 * c.y + c.h) / 2);

    $('#x').val(coords.cx);
    $('#y').val(coords.cy);
  }

  /** Clears the actual selection */
  $('#clrSel').click(function(e) {
    jcrop_api.release();

    coords.cx = 0;
    coords.cy = 0;

    $('#x').val('');
    $('#y').val('');
  });

  $('#setCoordTag').click(function() {
    $('#xTag').val(coords.cx);
    $('#yTag').val(coords.cy);
  });

  $('#setCoordImg').click(function() {
    $('#xImg').val(coords.cx);
    $('#yImg').val(coords.cy);
  });

  $('#toggleHelp').click(function() {
    $('#helpText').toggle();
  });

  $('#previewTags').click(function() {
    img = $('#jcrop');
    $.colorbox({
      href: img.attr('src'),
      title: img.attr('title'),
      maxWidth: '84%', maxHeight: '84%',
      onComplete: function() {
        addCanvas();
        drawOnCanvas();
      },
      onCleanup: function() {
        removeCanvas();
      }
    });
  });

  $('#saveSel').click(function() {
    var data = validateTag();

    if(data) {
      data.oper = "add";
      $.ajax({
        type: 'POST',
        url: wwwRoot + 'ajax/visualTagsEdit.php',
        data: data
      }).done(function() {
        $('#tagsGrid').trigger('reloadGrid');
        resetAllFields();
      });
    }
  });

  $('#tagsGrid').jqGrid({
    url: wwwRoot + 'ajax/visualGetImageTags.php',
    postData: {imageId: $('#imageId').val(), usage: 'table'},
    datatype: 'json',
    cmTemplate: {sortable: false},
    colNames: ['Id', 'Lexem', 'Text afișat', 'X Etichetă', 'Y Etichetă', 'X Imagine', 'Y Imagine'],
    colModel: [
      {name: 'id', index: 'id', hidden: true},
      {name: 'lexeme', index: 'lexeme', width: 80, align: 'center'},
      {name: 'label', index: 'label', width: 120, align: 'center', editable: true},
      {name: 'textX', index: 'textX', width: 55, align: 'center', editable: true},
      {name: 'textY', index: 'textY', width: 55, align: 'center', editable: true},
      {name: 'imgX', index: 'imgX', width: 55, align: 'center', editable: true},
      {name: 'imgY', index: 'imgY', width: 55, align: 'center', editable: true}
    ],
    rowNum: 20,
    recreateForm: true,
    width: '450px',
    height: '100%',
    rowList: [20, 50, 100, 200],
    pager: $('#tagsPaging'),
    viewrecords: true,
    caption: 'Etichete salvate',
    editurl: wwwRoot + 'ajax/visualTagsEdit.php',
    ondblClickRow: function(rowid) { $(this).jqGrid('editGridRow', rowid, editOptions); }
  })
  .navGrid('#tagsPaging', gridOptions, editOptions, addOptions, delOptions);

  imagesTable(1, 'revisedTable', 'revisedPaging', 'completă');
  imagesTable(0, 'unrevisedTable', 'unrevisedPaging', 'incompletă');
});

/** Replaces the submit event that triggers on change, set in select2Dev.js */
function replaceSubmitEvent() {
  $('#lexemId').off();
  $('#lexemId').on('change', function(e){
    var id = $(this).select2('data').id;
    var text = $(this).select2('data').text;

    // Matches only the lexeme, without the description in brackets 
    text = text.match(/^[^ \(]+/);

    $('#label').val(text);
  });
}

function validateTag() {
  var data = {
    id: '',
    lexemeId: $('#lexemId').attr('value'),
    imageId: $('#imageId').val(),
    label: $('#label').val(),
    xTag: $('#xTag').val(),
    yTag: $('#yTag').val(),
    xImg: $('#xImg').val(),
    yImg: $('#yImg').val()
  };

  if(!data.label) {
    alert('Ai uitat să completezi câmpul Cuvânt');
    return false;

  } else if(!data.xTag || !data.yTag) {
    alert('Ai uitat să completezi câmpurile Coordonatele centrului etichetei');
    return false;

  } else if(!data.xImg || !data.yImg) {
    alert('Ai uitat să completezi câmpurile Coordonatele zonei etichetate');
    return false;
  }

  return data;
}

function validateLexeme() {
  if(!($('#imgLexemeId').val())) {
    alert('Ai uitat să completezi ce lexem descrie cel mai bine imaginea');
    return false;
  }
}

function checkServerResponse(response, postData) {
  if (response.responseText) {
    return [false, response.responseText];
  } else {
    return [true];
  }
}

function resetAllFields() {
  $('#label').val('');
  $('#xTag').val('');
  $('#yTag').val('');
  $('#xImg').val('');
  $('#yImg').val('');
  $('#lexemId').select2('data', {id: '', text: ''});
}
