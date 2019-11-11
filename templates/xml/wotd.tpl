{assign var="row" value=$searchResult|default:null}
<?xml version="1.0" encoding="UTF-8" ?>
<results>
    <day>{$day}</day>
    <month>{$month}</month>
    <requested>
        <record>
            <year>{$year}</year>
            <word>{$row->definition->lexicon}</word>
            <reason>{$reason|escape:html}</reason>
            <image>{$wotd->getLargeThumbUrl()}</image>
            <definition>
                <id>{$row->definition->id}</id>
                <internalRep>{$row->definition->internalRep|escape:html}</internalRep>
                <htmlRep>{HtmlConverter::convert($row->definition)}</htmlRep>
                <userNick>{$row->user->nick}</userNick>
                <sourceName>{$row->source->shortName}</sourceName>
                <createDate>{$row->definition->createDate}</createDate>
                <modDate>{$row->definition->modDate}</modDate>
            </definition>
        </record>
    </requested>
    <others>
        {foreach $otherYears as $row}
        <record>
            <year>{$row.wotd->displayDate|date_format:'%Y'}</year>
            <word>{$row.word}</word>
            <reason>{$row.wotd->description|escape:html}</reason>
            <image>{$row.wotd->getMediumThumbUrl()}</image>
        </record>
        {/foreach}
    </others>
</results>
