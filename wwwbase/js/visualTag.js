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
  resetAllFields();
  imageLoadError();

  function initJcrop() {
    $('#jcrop').load(function() {
      $(this).Jcrop({
        bgColor: '',
        boxHeight: 500,
        boxWidth: 500,
        onSelect: setCoords,
        onChange: setCoords
      }, function() {
        jcrop_api = this;
      });
    });
  }

  function setCoords(c) {
    // Calculates the centre of the selection
    coords.cx = Math.round((2 * c.x + c.w) / 2);
    coords.cy = Math.round((2 * c.y + c.h) / 2);

    $('#x').val(coords.cx);
    $('#y').val(coords.cy);
  }

  $('#setTextCoords').click(function() {
    $('#textXCoord').val(coords.cx);
    $('#textYCoord').val(coords.cy);
  });

  $('#setImgCoords').click(function() {
    $('#imgXCoord').val(coords.cx);
    $('#imgYCoord').val(coords.cy);
  });

  $('#previewTags').click(function() {
    img = $('#jcrop');
    $.colorbox({
      href: img.attr('src'),
      title: img.attr('title'),
      maxWidth: '84%', maxHeight: '84%',
      onComplete: function() {
        var visualId = $('#visualId').val();
        addCanvas();
        drawOnCanvas(visualId);
      },
      onCleanup: function() {
        removeCanvas();
      }
    });
  });

  /* Validate new tag data before submitting the form. */
  $('#addTagButton').click(function() {
    if (!$('#tagLexemId').val()) {
      alert('Lexemul etichetei nu poate lipsi.');
      return false;
    } else if (!$('#tagLabel').val()) {
      alert('Textul de afișat nu poate fi vid.');
      return false;
    } else if (!$('#textXCoord').val() || !$('#textYCoord').val()) {
      alert('Coordonatele centrului etichetei nu pot fi vide.');
      return false;
    } else if (!$('#imgXCoord').val() || !$('#imgYCoord').val()) {
      alert('Coordonatele vârfului săgeții nu pot fi vide.');
      return false;
    }
    return true;
  });

  $('#tagsGrid').jqGrid({
    url: wwwRoot + 'ajax/visualGetImageTags.php',
    postData: { visualId: $('#visualId').val(), usage: 'table' },
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
    width: 900,
    height: '100%',
    rowList: [20, 50, 100, 200],
    pager: $('#tagsPaging'),
    viewrecords: true,
    caption: 'Etichete existente',
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

    $('#label').val(text);
  });
}

/* Checks if the lexeme has been entered */
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

/* Resets all tag info fields values */
function resetAllFields() {
  $('#label').val('');
  $('#xTag').val('');
  $('#yTag').val('');
  $('#xImg').val('');
  $('#yImg').val('');
  $('#lexemId').select2('data', {id: '', text: ''});
}

/* Prints an error message instead of the image, in case it is 
   missing from the database */
function imageLoadError() {
  $('.visualTagImg').error(function() {
    $('.imageHolder').html($('.missingImageError').css('display', 'block'));
  });
}
