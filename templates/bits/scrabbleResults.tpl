{if isset($answer)}
  {if $answer}
    {notice icon="done"}
      {t 1=$form|escape 2=$version}The form <strong>%1</strong> exists in LOC %2.{/t}
    {/notice}
  {else}
    {notice icon="error"}
      {t 1=$form|escape 2=$version}The form <strong>%1</strong> does not exist in LOC %2.{/t}
    {/notice}
  {/if}
{/if}
