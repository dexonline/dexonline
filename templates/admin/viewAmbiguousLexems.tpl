{extends file="layout-admin.tpl"}

{block name=title}Lexeme ambigue{/block}

{block name=content}

  <h3>{$lexems|count} lexeme ambigue (cu nume și descriere identice)</h3>
  
  {include file="admin/lexemList.tpl"}

{/block}
