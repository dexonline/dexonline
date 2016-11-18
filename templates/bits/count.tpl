{if !$number}
  {$none} {$common}
{elseif $number == 1}
  {$one} {$common}
{elseif $number % 100 < 20}
  {$number} {$many} {$common}
{else}
  {$number} de {$many} {$common}
{/if}
