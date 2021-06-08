<ul class="list-inline list-inline-bullet list-inline-bullet-sm">
  {foreach $lexemes as $l}
    <li class="list-inline-item">
      <a href="{Config::URL_PREFIX}lexem/{$l->formNoAccent}/{$l->id}">
        {include "bits/lexemeName.tpl" lexeme=$l}
      </a>
    </li>
  {/foreach}
</ul>
