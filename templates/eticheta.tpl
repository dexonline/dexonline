{extends "layout-admin.tpl"}

{block "title"}Eticheta {$t->value}{/block}

{block "content"}
  <h3>Eticheta {$t->value}</h3>

  {include "bits/tagAncestors.tpl" tag=$t}

  {if count($homonyms)}
    <h3>Ononime</h3>

    {foreach $homonyms as $h}
      <div class="voffset">
        {include "bits/tagAncestors.tpl" tag=$h}
      </div>
    {/foreach}
  {/if}

  {if count($lexems)}
    <h3>
      Lexeme asociate
      {if $lexemCount > count($lexems)}
        ({count($lexems)} din {$lexemCount} afișate)
      {else}
        ({count($lexems)})
      {/if}
    </h3>

    {include "bits/lexemList.tpl"}
  {/if}

  {if count($meanings)}
    <h3>
      Sensuri asociate
      {if $meaningCount > count($meanings)}
        ({count($meanings)} din {$meaningCount} afișate)
      {else}
        ({count($meanings)})
      {/if}
    </h3>

    <table class="table table-condensed table-bordered">
      <thead>
        <tr>
          <th>arbore</th>
          <th>sens</th>
        </tr>
      </thead>

      <tbody>
        {foreach $meanings as $m}
          <tr>
            <td>
              <a href="editTree.php?id={$m->getTree()->id}">
                {$m->getTree()->description}
              </a>
            </td>
            <td>
              <strong>{$m->breadcrumb}</strong>
              {$m->htmlRep}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}

  {if count($searchResults)}
    <h3>
      Definiții asociate
      {if $defCount > count($searchResults)}
        ({count($searchResults)} din {$defCount} afișate)
      {else}
        ({count($searchResults)})
      {/if}
    </h3>

    {foreach $searchResults as $row}
      {include "bits/definition.tpl"
      showDropup=0
      showStatus=1}
    {/foreach}
  {/if}
{/block}
