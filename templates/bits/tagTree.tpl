{* Recursively displays a tag tree (or forest). The id, if not empty, is only set for the root <ul>. *}
{if $tags || $id}
  <ul {if $id}id="{$id}"{/if}>
    {foreach from=$tags item=t}
      <li>
        <div class="expand {if count($t->children)}closed{/if}"></div>
        {strip}
        <div class="value"
             data-id="{$t->id}"
             data-can-delete="{$t->canDelete}">
          {$t->value}
        </div>
        {/strip}
        {include file="bits/tagTree.tpl" tags=$t->children id=""}
      </li>
    {/foreach}
  </ul>
{/if}
