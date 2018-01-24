{$accent=$accent|default:false}
{strip}
  {if $accent}
    {$lexem->form|escape}
  {else}
    {$lexem->formNoAccent|escape}
  {/if}
  {if $lexem->description} ({$lexem->description|escape}){/if}
  {if !$lexem->formNoAccent && !$lexem->description} [ID = {$lexem->id}]{/if}
{/strip}
