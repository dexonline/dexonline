{* Argument: $lexem *}
{assign var=s value=$lexem->getSourceNames()}
{if $s}
  <br>
  <span class="lexemSources">
    Surse flexiune: {$s}
  </span>
{/if}
