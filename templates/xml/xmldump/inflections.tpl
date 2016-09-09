<?xml version="1.0" encoding="UTF-8"?>
<Inflections>
  {foreach $inflections as $i}
    <Inflection id="{$i->id}">
      <Description>{$i->description|escape}</Description>
    </Inflection>
  {/foreach}
</Inflections>
