$(function() {

  // Overlayed on the image. Resized on image display.
  var canvas, dpr; // take HiDPI devices into account

  function resizeCanvas(width, height) {
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';
  }

  function drawLine(color, x1, y1, x2, y2) {
    var c = canvas.getContext('2d');

    c.lineWidth = 1.5;
    c.strokeStyle = color;

    c.beginPath();
    c.moveTo(x1, y1);
    c.lineTo(x2, y2);
    c.stroke();
  }

  function drawOnCanvas(visualId) {
    // The colorbox plugin title is made up of two parts:
    // 1. the unique id of the image from the Visual table
    // 2. the name of the label corresponding to the image
    $.ajax({
      type: 'POST',
      url: wwwRoot + 'ajax/visualGetImageTags.php',
      data: { visualId: visualId, usage: 'gallery' }
    }).done(function(data) {
      var widthScale = canvas.width / data.dims.width,
          heightScale = canvas.height / data.dims.height,
          word = $('input[name="cuv"]').val();

      for (var i = 0; i < data.tags.length; i++) {
        var t = data.tags[i];
        var b = $(sprintf('<a class="badge" href="%sintrare/%s/%s">%s</a>',
                          wwwRoot, t.entry, t.entryId, t.label));

        t.textXCoord *= widthScale;
        t.imgXCoord *= widthScale;
        t.textYCoord *= heightScale;
        t.imgYCoord *= heightScale;
        var highlight = (t.label == decodeURI(word))

        b.css({
          color: highlight ? '#f00' : '#212529',
          left: t.textXCoord / dpr,
          top: t.textYCoord / dpr,
        }).appendTo($('#cboxLoadedContent'));

        drawLine(highlight ? '#f00' : '#444',
                 t.textXCoord, t.textYCoord, t.imgXCoord, t.imgYCoord);
      }
    });
  }

  function imageLoaded() {
    // resize and activate the canvas
    var img = $('.cboxPhoto');
    resizeCanvas(img.width(), img.height());
    document.getElementById('cboxLoadedContent').appendChild(canvas);

    // show the toggle button
    var tagsToggle = $('#prototypeTagsToggleButton');
    tagsToggle.clone().css('display', 'block').attr('id', 'tagsToggle')
      .on('click', function() {
        $(canvas).toggle();
      }).appendTo($('#cboxLoadedContent'));

    // draw the tags and lines
    var visualId = $.colorbox.element().data('visualId');
    drawOnCanvas(visualId);
  }

  function imageCleanup() {
    canvas = canvas.parentNode.removeChild(canvas);
  }

  function init() {
    dpr = window.devicePixelRatio || 1;
    canvas = document.createElement('canvas');
    canvas.getContext('2d').setTransform(dpr, 0, 0, dpr, 0, 0);

    $('.gallery').colorbox({
      maxHeight: '84%',
      maxWidth: '84%',
      onCleanup: imageCleanup,
      onComplete: imageLoaded,
      rel: 'gallery',
    });
  }

  init();
});
