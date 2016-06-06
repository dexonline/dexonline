{foreach $entries as $e}
  <a href="{$wwwRoot}editEntry.php?id={$e->id}">{$e->description}</a> ·
{/foreach}    
