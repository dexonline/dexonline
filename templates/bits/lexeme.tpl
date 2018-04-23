{* Argument: $lexeme *}
{if User::can(User::PRIV_EDIT)}
  <div>
    {$lexeme->getPartOfSpeeech()}

    {if $lexeme->compound}
      compus
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
{/if}

{if $lexeme->notes}
  <div class="lexemeNotes">{$lexeme->notes|escape}</div>
{/if}

{assign var=s value=$lexeme->getSourceNames()}
{if $s}
  <div class="lexemeSources">
    Surse flexiune: {$s}
  </div>
{/if}
