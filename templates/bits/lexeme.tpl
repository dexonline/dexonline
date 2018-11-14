{* Argument: $lexeme *}
<div>
  {$lexeme->getPartOfSpeeech()}

  {if $lexeme->compound}
    {'compound'|_}
  {else}
    {strip}
    (
    <a href="{$wwwRoot}model-flexiune/{$lexeme->modelType}{$lexeme->modelNumber}">
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
    {'Inflection sources'|_}: {$s}
  </div>
{/if}
