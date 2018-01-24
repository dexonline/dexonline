{* Argument: $lexem *}
{if User::can(User::PRIV_EDIT)}
  <div>
    {$lexem->getPartOfSpeeech()}

    {if $lexem->compound}
      compus
    {else}
      {strip}
      (
      <a href="{"%sadmin/dispatchModelAction.php?editModel=1&amp;modelType=%s&amp;modelNumber=%s"|sprintf
               :$wwwRoot:$lexem->modelType:$lexem->modelNumber}"
         title="editează modelul">
        {$lexem->modelType}{$lexem->modelNumber}
      </a>
      )
      {/strip}
    {/if}
  </div>
{/if}

{if $lexem->notes}
  <div class="lexemNotes">{$lexem->notes|escape}</div>
{/if}

{assign var=s value=$lexem->getSourceNames()}
{if $s}
  <div class="lexemSources">
    Surse flexiune: {$s}
  </div>
{/if}
