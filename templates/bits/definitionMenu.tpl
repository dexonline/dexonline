{* Wrapper around the actual menu that allows for some extra actions *}
{$showEntryToggles=$showEntryToggles|default:false}
{$showSelectCheckbox=$showSelectCheckbox|default:false}

{if $showSelectCheckbox || $showEntryToggles}
  {$def=$row->definition}

  <div class="defDetails small text-muted row">

    <div class="col-xs-12 col-md-7 column">
      {include "bits/definitionMenuProper.tpl"}
    </div>

    <div class="col-xs-12 col-md-5 column">
      {if $showSelectCheckbox}
        <label class="checkbox-inline">
          <input type="checkbox" name="selectedDefIds[]" value="{$def->id}">
          selectează
        </label>
      {/if}

      {if $showEntryToggles}
        | <a href="#" class="toggleRepLink doubleText" title="comută între notația internă și HTML"
            data-value="1" data-order="1" data-other-text="html">text</a>
        | <a href="#" class="toggleRepLink doubleText" title="contractează sau expandează abrevierile"
            data-value="1" data-order="2" data-other-text="abreviat">expandat</a>

        |
        <a href="#"
          title="comută definiția între structurată și nestructurată"
        >
          <span class="toggleStructuredLink" {if !$def->structured}style="display: none"{/if}>
            <i class="glyphicon glyphicon-ok"></i> structurată
          </span>
          <span class="toggleStructuredLink" {if $def->structured}style="display: none"{/if}>
            <i class="glyphicon glyphicon-remove"></i> nestructurată
          </span>
        </a>
      {/if}
    </div>

  </div>
{else}
  <div class="defDetails small text-muted">
    {include "bits/definitionMenuProper.tpl"}
  </div>
{/if}
