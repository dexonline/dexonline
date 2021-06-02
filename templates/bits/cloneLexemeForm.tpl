<input type="hidden" name="id" value="{$lexeme->id}">

{include "bs/checkbox.tpl"
  name='cloneEntries[1][]'
  label='copiază intrările unde este lexem principal'
  checked=true}

{include "bs/checkbox.tpl"
  name='cloneEntries[0][]'
  label='copiază intrările unde este lexem variantă'
  checked=true}

{include "bs/checkbox.tpl"
  name=cloneInflectedForms
  label='copiază modelul și formele flexionare'
  checked=true}

{include "bs/checkbox.tpl"
  name=cloneTags
  label='copiază etichetele'
  checked=true}

{include "bs/checkbox.tpl"
  name=cloneSources
  label='copiază sursele'
  checked=true}
