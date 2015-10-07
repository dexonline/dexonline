{foreach from=$searchResults item=row}
  {$def=$row->definition}
  <div id="def{$def->id}">
    {$def->htmlRep}<br/>
    <span class="defDetails">
      Id: {$def->id} |
      Sursa: {$row->source->shortName|escape} |
      Trimisă de {$row->user->nick|escape},
      {$def->createDate|date_format:"%e %b %Y"} |
      Starea: {$def->getStatusName()} |

      <a href="definitionEdit.php?definitionId={$def->id}">Editează</a>
      {if $def->status == Definition::ST_PENDING}
        | <a href="#" title="Șterge această definiție" onclick="return deleteDefinition('def{$def->id}', {$def->id});">Șterge</a>
      {/if}
    </span>
    <br/>
    {foreach from=$row->typos item=typo}
      <div id="typo{$typo->id}">
        <span class="typo">* {$typo->problem|escape}</span>
        <span class="defDetails">
          <a href="#" title="Ignoră această raportare" onclick="return ignoreTypo('typo{$typo->id}', {$typo->id});">Ignoră</a>
        </span>
      </div>
    {/foreach}
    <br/>
  </div>
{/foreach}
