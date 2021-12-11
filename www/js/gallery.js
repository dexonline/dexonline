$(function() {

  // overlayed on the image; resized on image display
  var canvas;

  // take HiDPI devices into account
  var dpr = window.devicePixelRatio || 1;

  // page being viewed
  var word = $('input[name="cuv"]').val();

  function prepareCanvas(width, height) {
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    canvas.style.display = 'block'; // in case it was hidden
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';
    document.getElementById('cboxLoadedContent').appendChild(canvas);
  }

  function toggleTags() {
    var tmp = $(this).data('altText');
    $(this).data('altText', $(this).text());
    $(this).text(tmp);

    canvas.style.display = (canvas.style.display == 'none') ? 'block' : 'none';
    $('.img-label').toggle();
  }

  // make a new button each time so it doesn't have the wrong show/hide state
  function addToggleButton() {
    var btn = $(sprintf(
      '<button \
        id="toggle-tags" \
        class="btn btn-sm btn-secondary" \
        data-alt-text="%s" \
        type="button">%s\
       </button>',
      _('show tags'), _('hide tags')));
    btn.appendTo($('#cboxLoadedContent'));
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

  function drawTags(data) {
    var widthScale = canvas.width / data.size.width,
        heightScale = canvas.height / data.size.height;

    for (var i = 0; i < data.tags.length; i++) {
      var t = data.tags[i];
      var highlight = (t.label == decodeURI(word));

      t.labelX *= widthScale;
      t.tipX *= widthScale;
      t.labelY *= heightScale;
      t.tipY *= heightScale;

      // show the label
      var b = $(sprintf('<a class="badge img-label" href="%sintrare/%s/%s">%s</a>',
                        wwwRoot, t.entry, t.entryId, t.label));
      b.css({
        color: highlight ? '#f00' : '#212529',
        left: t.labelX / dpr,
        top: t.labelY / dpr,
      }).appendTo($('#cboxLoadedContent'));

      // draw the line
      drawLine(highlight ? '#f00' : '#444',
               t.labelX, t.labelY, t.tipX, t.tipY);
    }
  }

  function imageLoaded() {
    // resize and activate the canvas
    var img = $('.cboxPhoto');
    prepareCanvas(img.width(), img.height());

    // show the toggle button
    addToggleButton();

    // Draw the tags and lines. Don't use data('tagInfo'). This passes data
    // by reference and it gets scaled up with every call.
    var tagInfo = JSON.parse($.colorbox.element().attr('data-tag-info'));
    drawTags(tagInfo);
  }

  function init() {
    canvas = document.createElement('canvas');
    canvas.getContext('2d').setTransform(dpr, 0, 0, dpr, 0, 0);

    $('.gallery').colorbox({
      maxHeight: '84%',
      maxWidth: '84%',
      onComplete: imageLoaded,
      rel: 'gallery',
    });

    $(document).on('click', '#toggle-tags', toggleTags);
  }

  init();
});
