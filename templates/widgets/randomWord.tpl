<a id="randomWordLink" class="widget random-word row" href="{$wwwRoot}definitie/">
  <div class="col-md-8 col-sm-12 col-xs-6">
    <h4>Cuvânt aleator</h4><br/>
    <script>
     $.ajax({
       url: '{$wwwRoot}ajax/randomWord.php',
       success: function(cuv) {
         oldHref = $('#randomWordLink').attr('href');
         $('#randomWordLink').attr('href', oldHref + cuv);
         $('.random-value').text(cuv);
       }
     });
    </script>
    <span class="widget-value random-value"></span>
  </div>
  <div class="col-md-4 col-sm-12 col-xs-6 widget-thumbnail">
    <img alt="cuvânt aleator" src="{$cfg.static.url}img/wotd/thumb/misc/aleator.jpg" class="widget-icon">
  </div>
</a>
