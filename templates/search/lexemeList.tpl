<p class="entryList">
  {foreach $lexemes as $l}
    <span>
      <a href="{Config::URL_PREFIX}lexem/{$l->formNoAccent}/{$l->id}">
        {include "bits/lexemeName.tpl" lexeme=$l}
      </a>
    </span>
  {/foreach}
</p>
