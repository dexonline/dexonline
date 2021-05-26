(function(){

  function scrabbleAutoSearch() {

    var searchForm = $('#scrabbleForm');
    var searchInput = $('input.scrabbleSearchField', searchForm);
    var version = $('select', searchForm);
    var results = $('#scrabble-results');

    var feedback = $('.scrabbleSearchDiv input[name="form"]');

    var lastSearchedKey;
    var nextSearch;
    var searchCache = {};

    var queryURL = wwwRoot + 'scrabble';

    function updatePage(data) {
      // update feedback indicators
      feedback.removeClass('is-valid is-invalid');

      // update results
      if (data.template) {
        feedback.addClass((data.answer) ? 'is-valid' : 'is-invalid');
      }

      results.html(data.template);
      searchInput.focus();
    }

    function doSearch(cacheKey, params){
      var cached = searchCache[cacheKey];
      if (!cached){
        $.getJSON(queryURL, params, function(data) {
          searchCache[cacheKey] = data;
          updatePage(data);
        });
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
      if (nextSearch) {
        doSearch(nextSearch.key, nextSearch.params);
      }
    }

    searchInput.on('input', queueSearch);
    version.on('change', queueSearch);

    setInterval(runner, 500);
  }

  window.addEventListener('load', scrabbleAutoSearch);

})();
