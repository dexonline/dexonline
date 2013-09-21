$(document).ready(function() {
  $('.colorbox').colorbox({
    maxWidth: '84%', maxHeight: '84%',
    onComplete: function() {addCanvas(); drawOnCanvas();},
    onCleanup: function() {removeCanvas();}
  });

  // Adds tags visibility toggle
  var tagsToggle = document.createElement('button');
  tagsToggle.innerHTML = 'Ascunde/Afișează etichetele';
  document.getElementById('cboxContent').appendChild(tagsToggle);
  $(tagsToggle).attr('id', 'tagsToggle')
               .on('click', function() {$('canvas').toggle();});
});

/* Once the plugin is loaded, adds the canvas element */
function addCanvas() {
  var img = $('.cboxPhoto');
  var canvasElement = document.createElement('canvas');

  document.getElementById('cboxLoadedContent').appendChild(canvasElement);

  $('canvas').attr('width', img.css('width')).attr('height', img.css('height'));
}

/* Clears the canvas and the deletes the canvas element itself */
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

    // Removes only the dummy text layer, used only for getting dimensions
    canvas.removeLayerGroup('Pre');
  }
}

function drawTag(canvas, tagNo, tagData) {
  var tagNamePadding = 10;
  var tagNameMaxWidth = 100;

  // Draws a dummy text to get its dimensions
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

  // Draws the line between the two points
  .drawLine({
    layer: true,
    name: 'tag' + tagNo,
    groups: ['Tags'],
    strokeStyle: '#000',
    strokeWidth: 1,
    x1: tagData[0], y1: tagData[1],
    x2: tagData[2], y2: tagData[3]
  })

  // Draws a rectangle that has the dimensions of the dummy text + tagNamePadding
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

  // Rewrites the text over the recatngle
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
