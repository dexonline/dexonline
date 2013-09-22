$(document).ready(function() {
  $('.colorbox').colorbox({
    maxWidth: '84%', maxHeight: '84%',
    rel: 'gallery',
    onComplete: function() {
      addCanvas();
      drawOnCanvas();
    },
    onCleanup: function() {
      removeCanvas();
    }
  });
});

/* Once the plugin is loaded, it clones the canvas element and prepares it for display */
function addCanvas() {
  var canvas = $('#prototypeCanvas'), parent = $('#cboxLoadedContent'),
      img = $('.cboxPhoto'), tagsToggle = $('#prototypeTagsToggleButton');

  canvas.clone().css('display', 'block').attr('id', 'activeCanvas')
        .attr('width', img.css('width')).attr('height', img.css('height'))
        .appendTo(parent);

  tagsToggle.clone().css('display', 'block').attr('id', 'tagsToggle')
            .on('click', function() {
              $('#activeCanvas').toggle();
             }).appendTo(parent);
}

/* Clears the canvas before it being deleted by colorbox plugin */
function removeCanvas() {
  $('#activeCanvas').clearCanvas();
}

function drawOnCanvas() {
  var canvas = $('#activeCanvas');
  var title = $('#cboxTitle').html();
  var tags = $('#' + parseInt(title)).children();

  $('#cboxTitle').html(title.match(/[^\d]+$/));

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
    canvas.removeLayerGroup('DummyText');
  }
}

function drawTag(canvas, tagNo, tagData) {
  var tagNamePadding = 10;
  var tagNameMaxWidth = 100;

  // Draws a dummy text to get its dimensions
  canvas.drawText({
    layer: true,
    name: 'dummyText' + tagNo,
    groups: ['DummyText'],
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
    width: canvas.measureText('dummyText' + tagNo).width + tagNamePadding,
    height: canvas.measureText('dummyText' + tagNo).height + tagNamePadding
  })

  // Rewrites the text over the recatngle
  .drawText({
    layer: true,
    name: 'tagName' + tagNo,
    groups: ['TagsName'],
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
      window.open('http://www.dexonline.ro/definitie/' + tagData[5], '_self');
    }
  });
}
