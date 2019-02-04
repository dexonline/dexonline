var page = require('webpage').create();
page.open('tileset.html', function() {
  page.render('tileset.png');
  phantom.exit();
});
