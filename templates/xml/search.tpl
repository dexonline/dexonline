{assign var="results" value=$results|default:null}
<?xml version="1.0" encoding="UTF-8" ?>
<searchResults>
    <word>{$cuv}</word>
    <definitions>
        {foreach from=$results item=row key=i}
        <definition id="{$row->definition->id}">
            <internalRep>{$row->definition->internalRep|escape:html}</internalRep>
            <htmlRep>{$row->definition->htmlRep}</htmlRep>
            <userNick>{$row->user->nick}</userNick>
            <sourceName>{$row->source->shortName}</sourceName>
            <createDate>{$row->definition->createDate}</createDate>
            <modDate>{$row->definition->modDate}</modDate>
        </definition>
        {/foreach}
    </definitions>
</searchResults>
