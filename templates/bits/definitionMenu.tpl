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
        {include "bs/checkbox.tpl"
          divClass="form-check-inline"
          name="selectedDefIds[]"
          value=$def->id
          label="selectează"}
      {/if}

      {if $showEntryToggles}
        | <a href="#" class="toggleRepLink doubleText" title="comută între notația internă și HTML"
            data-value="1" data-order="1" data-other-text="html">text</a>
        | <a href="#" class="toggleRepLink doubleText" title="contractează sau expandează abrevierile"
            data-value="1" data-order="2" data-other-text="abreviat">expandat</a>
        | <a href="#" class="toggleTypoLink doubleText" title="semnalează erori" data-other-text="anulează">semnalează</a>
        |
        <a href="#"
          title="comută definiția între structurată și nestructurată"
        >
          <span class="toggleStructuredLink text-success" {if !$def->structured}style="display: none"{/if}>
            {include "bits/icon.tpl" i=done}
            structurată
          </span>
          <span class="toggleStructuredLink" {if $def->structured}style="display: none"{/if}>
            {include "bits/icon.tpl" i=clear}
            nestructurată
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
