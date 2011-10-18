    <Definition>
      <Id>{$def->id}</Id>
      <Timestamp>{$def->modDate}</Timestamp>
      {if $def->status == 0}
        <UserName>{$user->nick|escape}</UserName>
        <SourceId>{$def->sourceId}</SourceId>
        <Text>{$def->internalRep}</Text>
        {foreach from=$lexemIds item=lid}
          <LexemId>{$lid}</LexemId>
        {/foreach}
      {else}
        <Deleted/>
      {/if}
    </Definition>

