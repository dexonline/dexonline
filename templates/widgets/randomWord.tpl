<a
  id="randomWordLink"
  href="definitie/"
  class="widget random-word d-flex flex-md-column flex-xl-row">
  <div class="flex-grow-1">
    <h4>{t}random word{/t}</h4><br>
    <script>
     $.ajax({
       url: 'ajax/randomWord.php',
       success: function(cuv) {
         oldHref = $('#randomWordLink').attr('href');
         $('#randomWordLink').attr('href', oldHref + cuv);
         $('.random-value').text(cuv);
       }
     });
    </script>
    <span class="widget-value random-value"></span>
  </div>
  <div>
    <img alt="cuvânt aleator"
         src="{Config::STATIC_URL}img/wotd/thumb88/misc/aleator.jpg"
         class="widget-icon">
  </div>
</a>
