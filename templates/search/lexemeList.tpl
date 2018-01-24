<p class="entryList">
  {foreach $lexemes as $l}
    <span>
      <a href="{$wwwRoot}lexem/{$l->formNoAccent}/{$l->id}">
        {include "bits/lexemeName.tpl" lexem=$l}
      </a>
    </span>
  {/foreach}
</p>
