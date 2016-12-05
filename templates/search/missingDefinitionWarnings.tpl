{if !count($results) && !count($structuredResults)}

  {if isset($extra.unofficialHidden)}
    <p class="text-warning">
      Există definiții din dicționare neoficiale, pe care ați ales
      <a href="{$wwwRoot}preferinte">să le ascundeți</a>.
    </p>
  {/if}

  {if isset($extra.sourcesHidden)}
    <p class="text-warning">
      Există definiții din dicționare pentru care dexonline nu are drepturi de redistribuire:
    </p>

    <ul>
      {foreach $extra.sourcesHidden as $sh}
        <li>{$sh->name}, {$sh->publisher}, {$sh->year}</li>
      {/foreach}
    </ul>
  {/if}
{/if}

