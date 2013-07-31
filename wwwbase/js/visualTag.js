jQuery(document).ready(function() {
  
  var jcrop_api;
  var coords = new Object();
  
  initJcrop();
  resetCoords();

  function initJcrop() {
    $('#jcrop').Jcrop({
      boxHeight: 500,
      boxWidth: 500,
      onSelect: showCoords,
      onChange: showCoords,
      onRelease: resetCoords
    }, function() {
      jcrop_api = this;
    });
  };

  //Shows centre of the selection coordinates
  function showCoords(c) {
    setCoords(c);

    $('#x').val(coords.cx);
    $('#y').val(coords.cy);
  };

  function setCoords(c) {
    var q = new Array();
    q = calculateCentre(c);

    coords.x = c.x;
    coords.y = c.y;
    coords.w = c.w;
    coords.h = c.h;
    coords.cx = q[0];
    coords.cy = q[1];
  };

  function calculateCentre(c) {
    var centre = new Array();

    centre[0] = Math.round((2 * c.x + c.w) / 2);
    centre[1] = Math.round((2 * c.y + c.h) / 2);

    return centre;
  };

  //Clears the actual selection
  $('#clrSel').click(function(e) {
    jcrop_api.release();

    resetCoords();
  });

  function resetCoords() {
    coords.x = 0;
    coords.y = 0;
    coords.w = 0;
    coords.h = 0;
    coords.cx = 0;
    coords.cy = 0;

    $('#x').val('');
    $('#y').val('');
  };

  $('#setCoordTag').click(function() {
    $('#xTag').val(coords.cx);
    $('#yTag').val(coords.cy);
  });

  $('#setCoordImg').click(function() {
    $('#xImg').val(coords.cx);
    $('#yImg').val(coords.cy);
  });
});
