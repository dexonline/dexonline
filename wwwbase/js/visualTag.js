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
  
  initJcrop();
  resetCoords();

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
    calculateCentre(c);

    $('#x').val(coords.cx);
    $('#y').val(coords.cy);
  }

  function calculateCentre(c) {
    coords.cx = Math.round((2 * c.x + c.w) / 2);
    coords.cy = Math.round((2 * c.y + c.h) / 2);
  }

  /** Clears the actual selection */
  $('#clrSel').click(function(e) {
    jcrop_api.release();

    resetCoords();
  });

  function resetCoords() {
    coords.cx = 0;
    coords.cy = 0;

    $('#x').val('');
    $('#y').val('');
  }

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

  $('#tagsGrid').jqGrid({
    url: wwwRoot + 'ajax/getSavedTags.php',
    postData: {imageId: $('#imageId').val()},
    datatype: 'json',
    cmTemplate: {sortable: false},
    colNames: ['Id', 'Lexem', 'Text afișat', 'X Etichetă', 'Y Etichetă', 'X Imagine', 'Y Imagine'],
    colModel: [
      {name: 'id', index: 'id', hidden: true},
      {name: 'lexeme', index: 'lexeme', width: 80, align: 'center'},
      {name: 'label', index: 'label', width: 120, align: 'center', editable: true},
      {name: 'xTag', index: 'xTag', width: 55, align: 'center', editable: true},
      {name: 'yTag', index: 'yTag', width: 55, align: 'center', editable: true},
      {name: 'xImg', index: 'yImg', width: 55, align: 'center', editable: true},
      {name: 'yImg', index: 'yImg', width: 55, align: 'center', editable: true}
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
});

/** Replaces the submit event that triggers on change, set in select2Dev.js */
function replaceSubmitEvent() {
  $('#lexemId').off();
  $('#lexemId').on('change', function(e){
    var id = $(this).select2('data').id;
    var text = $(this).select2('data').text;

    // Matches only the lexeme, without the description in brackets 
    text = text.match(/^[^ \(]+/);

    $('#lexemeId').val(id);
    $('#label').val(text);
  });
}

function validateTag() {
  var label = $('#label').val();
  var xImg = $('#xImg').val();
  var yImg = $('#yImg').val();
  var xTag = $('#xTag').val();
  var yTag = $('#yTag').val();

  if(!label) {
    alert('Ai uitat să completezi câmpul Cuvânt');
    return false;

  } else if(!xTag || !yTag) {
    alert('Ai uitat să completezi câmpurile Coordonatele centrului etichetei');
    return false;

  } else if(!xImg || !yImg) {
    alert('Ai uitat să completezi câmpurile Coordonatele zonei etichetate');
    return false;
  }
}

function validateLexeme() {
  var lexeme = $('#imgLexemeId').val();

  if(!lexeme) {
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
