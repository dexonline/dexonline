<input type="hidden" name="id" value="{$e->id}">

{include "bs/checkbox.tpl"
  name=cloneDefinitions
  label='copiază asocierile cu definiții'
  checked=true}

{include "bs/checkbox.tpl"
  name=cloneLexemes
  label='copiază asocierile cu lexeme'
  checked=true}

{include "bs/checkbox.tpl"
  name=cloneTrees
  label='copiază asocierile cu arbori'
  checked=true}

{include "bs/checkbox.tpl"
  name=cloneStructurist
  label='copiază starea structurării și structuristul'
  checked=true}
