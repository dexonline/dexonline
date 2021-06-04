<div class="paraLexeme">
  <div>
    <span class="lexemeName">{include "bits/lexemeName.tpl" lexeme=$lexeme}</span>

    {foreach $lexeme->getTags() as $t}
      {include "bits/tag.tpl"}
    {/foreach}

    {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
      <a class="btn btn-link" href="{Router::link('lexeme/edit')}?lexemeId={$lexeme->id}">
        {include "bits/icon.tpl" i=edit}
        editează
      </a>
    {/if}
  </div>

  <ul class="lexemeMorphology">
    {if $lexeme->hyphenations}
      <li>
        {t}hyphenation{/t}:
        <span class="value">{$lexeme->hyphenations}</span>
        <span class="ms-1" title="{t}Hyphenation splits the word into syllables.{/t}">
          {include "bits/icon.tpl" i=info}
        </span>
      </li>
    {/if}

    {if $lexeme->pronunciations}
      <li>
        {t}pronunciation{/t}: <span class="value">{$lexeme->getDisplayPronunciations()}</span>
      </li>
    {/if}
  </ul>

  {include "paradigm/paradigm.tpl" lexeme=$lexeme}
</div>
