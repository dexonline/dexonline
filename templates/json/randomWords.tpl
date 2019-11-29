{
"type": "randomWord",
"word": {$row->definition->lexicon|@json_encode},
"definition": {
    {include "json/bits/definition.tpl"}
  }
}