{$accent=$accent|default:false}
{$boxed=$boxed|default:false}
{$model=$model|default:true}

{strip}
{if $boxed}<span class="linkBox">{/if}
<a
  href="{Router::link('lexeme/edit')}?lexemeId={$lexeme->id}"
  title="editează">
  {include "bits/lexemeName.tpl"}
</a>
{/strip}

{if $model}
  ({$lexeme->modelType}{$lexeme->modelNumber}{$lexeme->restriction})
{/if}
{if $boxed}</span>{/if}
