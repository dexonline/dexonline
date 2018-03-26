{$showTypos=$showTypos|default:false}

<div class="defWrapper">
  <p>
    <span class="def" title="Clic pentru a naviga la acest cuvânt">
      {$row->definition->htmlRep}
    </span>
    {foreach $row->tags as $t}
      {include "bits/tag.tpl"}
    {/foreach}
  </p>

  {include "bits/footnotes.tpl" footnotes=$row->footnotes}
  {include "bits/definitionMenu.tpl"}


  {if $showTypos}
    {if count($row->typos)}
      <ul>
        {foreach $row->typos as $typo}
          <li id="typo{$typo->id}">

            <span class="text-warning">
              {$typo->problem|escape}
            </span>
            <span class="text-muted">
              [{$typo->userName}]
            </span>
            <a href="#"
               title="Ignoră această raportare"
               onclick="return ignoreTypo('typo{$typo->id}', {$typo->id});">
              ignoră
            </a>

          </li>
        {/foreach}
      </ul>
    {/if}
  {/if}
</div>
