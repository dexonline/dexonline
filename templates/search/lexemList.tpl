<p class="entryList">
  {foreach $lexems as $l}
    <span>
      <a href="{$wwwRoot}lexem/{$l->formNoAccent}/{$l->id}">
        {include "bits/lexemName.tpl" lexem=$l}
      </a>
    </span>
  {/foreach}
</p>
