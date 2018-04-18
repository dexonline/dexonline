(function(){

  function scrabbleAutoSearch(){

    function clearSearch() {
      searchInput.val(null);
      queueSearch();
    }

    function getGlyph(){
      var el = $('<span class="form-control-feedback glyphicon">');
      el.css({'pointer-events': 'initial'}); // enable click
      el.on('click', clearSearch);
      return el;
    }

    var searchForm = $('form[action="scrabble"]');
    var searchInput = $('input.scrabbleSearchField', searchForm);
    var locVersion = $('select', searchForm);
    var results = $('#scrabble-results');

    var feedback = $('.scrabbleSearchDiv .form-group');
    var feedbackGlyph = $('#scrabble-feedback-glyph');

    var lastSearchedKey;
    var nextSearch;
    var searchCache = {};

    var queryURL = wwwRoot + 'scrabble.php';


    function updatePage(data) {
      // update feedback indicators
      feedback.removeClass('has-feedback has-error has-success');
      feedbackGlyph.html(null);

      var icon, glyphIcon;

      if (data.count > 0) {
        icon = 'has-success';
        glyphIcon = 'glyphicon-ok';
      }

      else {
        icon = 'has-error';
        glyphIcon = 'glyphicon-remove';
      }

      // update results
      if (data.template) {
        feedback.addClass('has-feedback ' + icon);
        glyph = getGlyph();
        glyph.addClass(glyphIcon);
        feedbackGlyph.append(glyph);
      }

      results.html(data.template);
      searchInput.focus();
    }

    function doSearch(cacheKey, params){
      var cached = searchCache[cacheKey];
      if (!cached){
        $.getJSON(queryURL, params, function(data){
          searchCache[cacheKey] = data;
          updatePage(data);
        })
      }
      else if (cached && lastSearchedKey !== cacheKey) {
        lastSearchedKey = cacheKey;
        updatePage(cached);
      }
    }

    function queueSearch() {
      function reducer(acc, el){
        acc[el.name] = el.value;
        return acc;
      }
      var cacheKey = searchForm.serialize();
      var formData = searchForm.serializeArray().reduce(reducer, {ajax: true});
      nextSearch = {key: cacheKey, params: formData};
    }

    // execute queued search
    function runner() {
      if (nextSearch){
        doSearch(nextSearch.key, nextSearch.params);
      }
    }

    searchInput.on('input', queueSearch);
    locVersion.on('change', queueSearch);

    setInterval(runner, 500);
  }

  window.addEventListener('load', scrabbleAutoSearch)

})();
