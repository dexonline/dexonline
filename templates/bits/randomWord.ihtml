<a id="randomWordLink" href="{$wwwRoot}definitie/"></a>

<script type="text/javascript">
  {literal}
    $.ajax({
      url: '{/literal}{$wwwRoot}{literal}ajax/randomWord.php',
      success: function(cuv) {
        oldHref = $('#randomWordLink').attr('href');
        $('#randomWordLink').attr('href', oldHref + cuv);
        $('#randomWordLink').text(cuv);
      }
    });
  {/literal}
</script>
