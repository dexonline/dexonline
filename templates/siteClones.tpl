{extends file="layout.tpl"}

{block name=title}Site Clones{/block}

{block name=content}
  <h2>Site Clones </h2>

  <p> {$definition} </p>
  <h3>All results </h3>
  <ul>
    {foreach from=$listAll  item=list}
	    <li><a href="{$list}" >{$list}</a></li> 
    {/foreach}
  </ul>

  <h3>Message Alert </h3> 
  <ul>
    {foreach from=$alert item=msg}
	    <li> {$msg} </li>
    {/foreach}
  </ul>

  <h3>BlackList </h3>
  <ul>
    {foreach from=$blackList item=url}
	    <li><a href="{$url}" >{$url}</a></li>
    {/foreach}
  </ul>
{/block}
