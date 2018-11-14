{if !count($results)}

  {if isset($extra.unofficialHidden)}
    <p class="text-warning">
      {'There are definitions in unofficial dictionaries, which
      <a href="%spreferinte">you have chosen to hide</a>.'|_|sprintf:$wwwRoot}
    </p>
  {/if}

  {if isset($extra.sourcesHidden)}
    <p class="text-warning">
      {'There are definitions in dictionaries for which dexonline has no
      publishing rights:'|_}
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
