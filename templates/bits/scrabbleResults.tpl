{if isset($answer)}
  {if $answer}
    <div class="alert alert-success">
      Forma <strong>{$form|escape}</strong> există în LOC {$version}.
    </div>
  {else}
    <div class="alert alert-danger">
      Forma <strong>{$form|escape}</strong> nu există în LOC {$version}.
    </div>
  {/if}
{/if}
