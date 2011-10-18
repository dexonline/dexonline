    <Entry>
      <Id>{$def->id}</Id>
      {if $version == "1.0"}
        <Name>{$lexemLatinNames.0|escape}</Name>
      {else}
        {foreach from=$lexemLatinNames item=name}
          <Name>{$name|escape}</Name>
        {/foreach}
      {/if}
      <Definition>{$def->internalRep}</Definition>
      <Source>{$source->shortName|escape}</Source>
      <Author>{$user->nick|escape}</Author>
      <Timestamp>{$def->modDate}</Timestamp>
      {if $version == "2.0"}
        {foreach from=$lexemNames item=name}
          <Dname>{$name|escape}</Dname>
        {/foreach}
      {elseif $includeNameWithDiacritics}
        <Dname>{$lexemNames.0|escape}</Dname>
      {/if}
    </Entry>

