<h3>Cuvântul zilei de {$timestamp|date_format:'%e %B'} în alți ani:</h3>
{foreach from=$wotds item=w}
  <div class="panel panel-default">
    <div class="panel-body">
      <img class="pull-right" src="{$w.img}" alt="iconița cuvântului zilei" />
      <p><strong>{$w.year}:&nbsp;</strong><a href="{$w.href}">{$w.word}</a></p>
      {if $w.tip}
        {$w.tip}
      {/if}
    </div>
  </div>
{/foreach}
