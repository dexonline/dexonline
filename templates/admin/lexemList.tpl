{foreach $lexems as $l}
  {include "bits/lexemLink.tpl" lexem=$l}
  ({$l->modelType}{$l->modelNumber}{$l->restriction}) |
{/foreach}    
