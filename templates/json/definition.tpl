"id": {$row->definition->id|@json_encode},
"internalRep": {$row->definition->internalRep|escape:html|@json_encode},
"htmlRep": {HtmlConverter::convert($row->definition)|@json_encode},
"userNick": {$row->user->nick|@json_encode},
"sourceName": {$row->source->shortName|@json_encode},
"createDate": {$row->definition->createDate|@json_encode},
"modDate": {$row->definition->modDate|@json_encode}
