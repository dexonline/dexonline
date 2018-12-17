{if isset($answer)}
  {if $answer}
    <div class="alert alert-success">
      {t 1=$form 2=$version}The form <strong>%1</strong> exists in LOC %2.{/t}
    </div>
  {else}
    <div class="alert alert-danger">
      {t 1=$form 2=$version}The form <strong>%1</strong> does not exist in LOC %2.{/t}
    </div>
  {/if}
{/if}
