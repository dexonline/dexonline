<a id="randomWordLink" href="{$wwwRoot}definitie/"></a>

<script>
 $.ajax({
   url: '{$wwwRoot}ajax/randomWord.php',
   success: function(cuv) {
     oldHref = $('#randomWordLink').attr('href');
     $('#randomWordLink').attr('href', oldHref + cuv);
     $('#randomWordLink').text(cuv);
   }
 });
</script>
