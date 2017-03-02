{assign var="results" value=$results|default:null}
{
    "word": "{$cuv|escape:javascript}",
    "definitions": [
        {foreach $results as $row}
        {
            "internalRep": "{$row->definition->internalRep|escape:html|escape:javascript}",
            "htmlRep": "{$row->definition->htmlRep|escape:javascript}",
            "userNick": "{$row->user->nick|escape:javascript}",
            "sourceName": "{$row->source->shortName|escape:javascript}",
            "createDate": "{$row->definition->createDate|escape:javascript}",
            "modDate": "{$row->definition->modDate|escape:javascript}"
        }{if !$row@last},{/if}
        {/foreach}
    ]
}
