{extends file="admin/layout.tpl"}

{block name=title}Înlocuire în masă{/block}

{block name=headerTitle}
  Înlocuire în masă ({$searchResults|count})
{/block}

{block name=content}
  {foreach from=$searchResults item=row}
    {$row->definition->htmlRep}<br/>
    <span class="defDetails">
      Id: {$row->definition->id} |
      Sursa: {$row->source->shortName|escape} |
      Trimisă de {$row->user->nick|escape}</a>,
      {$row->definition->createDate|date_format:"%e %b %Y"} |
      Starea: {$row->definition->getStatusName()} |

      <a href="definitionEdit.php?definitionId={$row->definition->id}">Editează</a>
    </span>
    <br/>
    <br/>
  {/foreach}

  <form action="bulkReplace.php" method="get">
    <input type="hidden" name="search" value="{$search|escape}"/>
    <input type="hidden" name="replace" value="{$replace|escape}"/>
    <input type="hidden" name="source" value="{$sourceId}"/>
    <input type="hidden" name="realRun" value="1"/>
    <input type="submit" name="submitButton" value="Confirmă" onclick="return hideSubmitButton(this)"/>
  </form>
{/block}
