{* Argument: $lexeme *}
<div>
  {$lexeme->getPartOfSpeech()}

  {if $lexeme->compound}
    {t}compound{/t}
  {else}
    {strip}
    (
    <a href="{Router::link('model/view')}/{$lexeme->modelType}{$lexeme->modelNumber}">
      {$lexeme->modelType}{$lexeme->modelNumber}
    </a>
    )
    {/strip}
  {/if}
</div>

{if $lexeme->notes}
  <div class="lexemeNotes">{$lexeme->notes|escape}</div>
{/if}

{assign var=s value=$lexeme->getSourceNames()}
{if $s}
  <div class="lexemeSources">
    {t}Inflection sources{/t}: {$s}
  </div>
{/if}
