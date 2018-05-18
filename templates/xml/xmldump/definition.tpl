  <Definition id="{$def->id}">
    <Timestamp>{$def->modDate}</Timestamp>
    {if $def->status == 0}
      <UserName>{$nick|escape}</UserName>
      <SourceId>{$def->sourceId}</SourceId>
      <Text>{$def->internalRep}</Text>
    {else}
      <Deleted></Deleted>
    {/if}
  </Definition>
