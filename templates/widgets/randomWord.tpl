<a id="randomWordLink" class="widget row" href="{$wwwRoot}definitie/">
  <div class="col-md-8">
    <h4>Cuvânt aleator</h4><br/>
    <script>
     $.ajax({
       url: '{$wwwRoot}ajax/randomWord.php',
       success: function(cuv) {
         oldHref = $('#randomWordLink').attr('href');
         $('#randomWordLink').attr('href', oldHref + cuv);
         $('.widget-value').text(cuv);
       }
     });
    </script>
    <span class="widget-value"></span>
  </div>
  <div class="col-md-4">
    <img alt="cuvânt aleator" src="{$cfg.static.url}img/wotd/thumb/misc/aleator.jpg" class="widget-icon">
  </div>
</a>
