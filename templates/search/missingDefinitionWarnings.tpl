{if !count($results)}

  {if isset($extra.unofficialHidden)}
    <p class="text-warning">
      {$url = Router::link('user/preferences')}
      {t 1=$url}There are definitions in unofficial dictionaries,
      which <a href="%1">you have chosen to hide</a>.{/t}
    </p>
  {/if}

  {if isset($extra.sourcesHidden)}
    <p class="text-warning">
      {t}There are definitions in dictionaries for which dexonline has no
      publishing rights:{/t}
    </p>

    <ul>
      {foreach $extra.sourcesHidden as $sh}
        <li>
          {strip}
          <b>[{$sh->shortName}] </b>
          {$sh->name}
          {if $sh->publisher}, {$sh->publisher}{/if}
          {if $sh->year}, {$sh->year}{/if}
          {/strip}
        </li>
      {/foreach}
    </ul>
  {/if}
{/if}
