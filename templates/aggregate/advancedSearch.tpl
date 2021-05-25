{extends "layout-admin.tpl"}

{block "title"}Căutare avansată{/block}

{block "content"}

  {if $stats.numResults}

    <form method="post">
      {foreach $args as $name => $value}
        {if is_array($value)}
          {foreach $value as $element}
            <input type="hidden" name="{$name}[]" value="{$element}">
          {/foreach}
        {else}
          <input type="hidden" name="{$name}" value="{$value}">
        {/if}
      {/foreach}

      <div class="card voffset2">
        <div class="card-header">
          {include "bits/advancedSearchToolbar.tpl"}
        </div>

        <div class="card-body card-admin">

          {if $view == 'Entry'}
            {include "bits/adminEntryList.tpl" entries=$data}
          {elseif $view == 'Lexeme'}
            {include "bits/lexemeList.tpl" lexemes=$data}
          {elseif $view == 'Definition'}
            {foreach $data as $row}
              {include "bits/definition.tpl"}
            {/foreach}
          {/if}

        </div>

        {if $stats.numPages > 1}
          <div class="card-footer">
            {include "bits/advancedSearchToolbar.tpl"}
          </div>
        {/if}
      </div>
    </form>

  {else}
    <h3>Nu există rezultate</h3>
  {/if}

{/block}
