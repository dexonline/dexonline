$(document).ready(function() {
  $('.gallery').colorbox({
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

/* Once the plugin is loaded, it clones the canvas element and prepares it for display.
   The canvas element is overlayed on the element with .cboxPhoto class, and thus it
   has to have its dimensions. It also adds a button to toggle the tags visibility. */
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
  // The colorbox plugin title is made up of two parts:
  // 1. the unique id of the image from the Visual table
  // 2. the name of the lexeme corresponding to the image
  var title = $('#cboxTitle').html();
  $('#cboxTitle').html(title.match(/[^\d]+$/));
  var imageId = parseInt(title);

  $.ajax({
    type: 'POST',
    url: wwwRoot + 'ajax/getImageTags.php',
    data: {imageId: imageId}
  }).done(function(data) {
    data = JSON.parse(data);
    var widthScale = parseInt(canvas.attr('width')) / data.dims.width,
        heightScale = parseInt(canvas.attr('height')) / data.dims.height;

    for(var i = 0; i < data.tags.length; i++) {
      data.tags[i].textX *= widthScale;
      data.tags[i].imgX *= widthScale;
      data.tags[i].textY *= heightScale;
      data.tags[i].imgY *= heightScale;

      drawTag(canvas, i, data.tags[i]);
    }
  });

    // Removes only the dummy text layer, used only for getting dimensions
    canvas.removeLayerGroup('DummyText');
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
    text: tagData.label,
    maxWidth: tagNameMaxWidth,
    x: tagData.textX, y: tagData.textY
  })

  // Draws the line between the two points
  .drawLine({
    layer: true,
    name: 'tag' + tagNo,
    groups: ['Tags'],
    strokeStyle: '#000',
    strokeWidth: 1,
    x1: tagData.textX, y1: tagData.textY,
    x2: tagData.imgX, y2: tagData.imgY
  })

  // Draws a rectangle that has the dimensions of the dummy text + tagNamePadding
  .drawRect({
    layer: true,
    name: 'tagBackground' + tagNo,
    groups: ['TagsBackground'],
    fromCenter: true,
    fillStyle: '#fff',
    x: tagData.textX, y: tagData.textY,
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
    text: tagData.label,
    maxWidth: tagNameMaxWidth,
    x: tagData.textX, y: tagData.textY,
    cursors: {
      mouseover: 'pointer'
    },
    click: function() {
      window.open('http://www.dexonline.ro/definitie/' + tagData.lexeme, '_self');
    }
  });
}
