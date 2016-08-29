{extends file="layout.tpl"}

{block name=title}Site Clones{/block}

{block name=content}
  <h3>Site Clones </h3>

  <p> {$definition} </p>
  <h4>All results </h4>
  <ul>
    {foreach from=$listAll  item=list}
	    <li><a href="{$list}" >{$list}</a></li> 
    {/foreach}
  </ul>

  <h4>Message Alert </h4> 
  <ul>
    {foreach from=$alert item=msg}
	    <li> {$msg} </li>
    {/foreach}
  </ul>

  <h4>BlackList </h4>
  <ul>
    {foreach from=$blackList item=url}
	    <li><a href="{$url}" >{$url}</a></li>
    {/foreach}
  </ul>
{/block}
