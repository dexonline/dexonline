{assign var="row" value=$searchResult|default:null}
{
  "type": "results",
  "month": {$month|@json_encode},
  "requested": {
    "record": {
      "year": {$year|@json_encode},
      "word": {$row->definition->lexicon|@json_encode},
      "reason": {$reason|@json_encode},
      "image": {$imageUrl|@json_encode},
      "imageAuthor": {$artist->name|default:''|@json_encode},
      "definition": {
        "id": {$row->definition->id|@json_encode},
        "internalRep": {$row->definition->internalRep|escape:html|@json_encode},
        "htmlRep": {HtmlConverter::convert($row->definition)|@json_encode},
        "userNick": {$row->user->nick|@json_encode},
        "sourceName": {$row->source->shortName|@json_encode},
        "createDate": {$row->definition->createDate|@json_encode},
        "modDate": {$row->definition->modDate|@json_encode}
      }
    }
  }
}
