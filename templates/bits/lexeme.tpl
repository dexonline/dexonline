{* Argument: $lexeme *}
{if User::can(User::PRIV_EDIT)}
  <div>
    {$lexeme->getPartOfSpeeech()}

    {if $lexeme->compound}
      compus
    {else}
      {strip}
      (
      <a href="{"%sadmin/dispatchModelAction.php?editModel=1&amp;modelType=%s&amp;modelNumber=%s"|sprintf
               :$wwwRoot:$lexeme->modelType:$lexeme->modelNumber}"
         title="editează modelul">
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
