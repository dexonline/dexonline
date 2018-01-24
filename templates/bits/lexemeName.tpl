{$accent=$accent|default:false}
{strip}
  {if $accent}
    {$lexeme->form|escape}
  {else}
    {$lexeme->formNoAccent|escape}
  {/if}
  {if $lexeme->description} ({$lexeme->description|escape}){/if}
  {if !$lexeme->formNoAccent && !$lexeme->description} [ID = {$lexeme->id}]{/if}
{/strip}
