{foreach $searchResults as $row}
  {$def=$row->definition}
  <div class="defWrapper" id="def{$def->id}">

    <p class="def">{$def->htmlRep}</p>

    <p class="defDetails text-muted">
      Id: {$def->id} |
      sursa: {$row->source->shortName|escape} |
      trimisă de {$row->user->nick|escape},
      {$def->createDate|date_format:"%e %b %Y"} |
      starea: {$def->getStatusName()} |

      <a href="definitionEdit.php?definitionId={$def->id}">editează</a>
      {if $def->status == Definition::ST_PENDING}
        |
        <a href="#"
             title="Șterge această definiție"
             onclick="return deleteDefinition('def{$def->id}', {$def->id});">
        șterge
        </a>
      {/if}
    </p>

    {if count($row->typos)}
      <ul>
        {foreach $row->typos as $typo}
          <li id="typo{$typo->id}">

            <span class="text-warning">
              {$typo->problem|escape}
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
  </div>
{/foreach}
