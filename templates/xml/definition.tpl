<definition id="{$row->definition->id}">
  <internalRep>{$row->definition->internalRep|escape:html}</internalRep>
  <htmlRep>{HtmlConverter::convert($row->definition)}</htmlRep>
  <userNick>{$row->user->nick}</userNick>
  <sourceName>{$row->source->shortName}</sourceName>
  <createDate>{$row->definition->createDate}</createDate>
  <modDate>{$row->definition->modDate}</modDate>
</definition>