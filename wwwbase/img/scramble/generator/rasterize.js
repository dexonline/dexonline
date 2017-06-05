var page = require('webpage').create();
page.open('letters.html', function() {
  page.render('letters.png');
  phantom.exit();
});
