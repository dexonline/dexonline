{$total=$total|default:$displayed}
{$common=$common|default:''}

{if !$total}
  {$none} {$common}
{elseif $total == 1}
  {$one} {$common}
{elseif $total % 100 < 20}
  {$total} {$many} {$common}
{else}
  {$total} de {$many} {$common}
{/if}

{if $total > $displayed}
  (maximum {$displayed} afișate)
{/if}
