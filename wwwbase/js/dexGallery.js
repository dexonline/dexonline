$(document).ready(function() {
  $('.colorbox').colorbox({
    maxWidth: '84%', maxHeight: '84%',
    onComplete: function() {addCanvas(); drawOnCanvas();},
    onCleanup: function() {removeCanvas();}
  });
});

/* Adaugă elementul canvas odată ce s-a încărcat pluginul */
function addCanvas() {
  var img = $('.cboxPhoto');
  var canvasElement = document.createElement('canvas');

  document.getElementById('cboxLoadedContent').appendChild(canvasElement);

  $('canvas').attr('width', img.css('width')).attr('height', img.css('height'));
}

/* Șterge conținutul desenat pe canvas și apoi șterge elementul */
function removeCanvas() {
  var canvasElement = document.getElementsByTagName('canvas')[0];

  $(canvasElement).clearCanvas();
  canvasElement.parentNode.removeChild(canvasElement);
}

function drawOnCanvas() {
  var canvas = $('canvas');
  var title = $('#cboxTitle').html();
  var tags = $('#' + title).children();

  if(tags.length){
    var data = new Array();
    var dim = JSON.parse('[' + tags[0].innerHTML + ']');
    var widthScale = parseInt(canvas.attr('width')) / dim[0];
    var heightScale = parseInt(canvas.attr('height')) / dim[1];

    for(var i = 1; i < tags.length; i++){
      var data = JSON.parse('[' + tags[i].innerHTML + ']');

      data[0] *= widthScale; data[2] *= widthScale;
      data[1] *= heightScale; data[3] *= heightScale;
      drawTag(canvas, i, data);
    }

    /* Șterge primul text, care are doar caracter orientativ */
    canvas.removeLayerGroup('Pre');
  }
}

function drawTag(canvas, tagNo, tagData) {
  var tagNamePadding = 10;
  var tagNameMaxWidth = 100;

  /* Scrie textul pentru a stabili dimensiunea lui */ 
  canvas.drawText({
    layer: true,
    name: 'pre' + tagNo,
    groups: ['Pre'],
    fromCenter: true,
    strokeStyle: '#fff',
    strokeWidth: 2,
    fontSize: 12,
    fontFamily: 'Arial',
    text: tagData[4],
    maxWidth: tagNameMaxWidth,
    x: tagData[0], y: tagData[1],
  })

  /* Trage linia între cele două puncte */
  .drawLine({
    layer: true,
    name: 'tag' + tagNo,
    groups: ['Tags'],
    strokeStyle: '#000',
    strokeWidth: 1,
    x1: tagData[0], y1: tagData[1],
    x2: tagData[2], y2: tagData[3]
  })

  /* Desenează un dreptungi de dimensiunea textului + un delta */ 
  .drawRect({
    layer: true,
    name: 'tagBackground' + tagNo,
    groups: ['TagsBackground'],
    fromCenter: true,
    fillStyle: '#fff',
    x: tagData[0], y: tagData[1],
    width: canvas.measureText('pre' + tagNo).width + tagNamePadding,
    height: canvas.measureText('pre' + tagNo).height + tagNamePadding
  })

  /* Rescrie textul în dreptunghi */
  .drawText({
    layer: true,
    name: 'tagName' + tagNo,
    groups: ['TagNames'],
    fromCenter: true,
    fillStyle: '#000',
    strokeWidth: 2,
    fontSize: 12,
    fontFamily: 'Arial',
    text: tagData[4],
    maxWidth: tagNameMaxWidth,
    x: tagData[0], y: tagData[1],
    cursors: {
      mouseover: 'pointer'
    },
    click: function() {
      window.open('http://www.dexonline.ro/definitie/' + tagData[4], '_self');
    }
  });
}