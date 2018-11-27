{$total=$total|default:$displayed}
{$common=$common|default:''}

{if !$total}
  {$none} {$common}
{elseif $total == 1}
  {$one} {$common}
{elseif $total % 100 > 0 && $total % 100 < 20}
  {$total} {$many} {$common}
{else}
  {$total} {'&#x200b;'|_}{$many} {$common}
{/if}

{if $total > $displayed}
  {t 1=$displayed}(at most %1 shown){/t}
{/if}
