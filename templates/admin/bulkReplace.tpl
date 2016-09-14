{extends "layout-admin.tpl"}

{block name=title}Înlocuire în masă{/block}

{block name=content}
  <h3>Înlocuire în masă: {$searchResults|count} de definiții</h3>

  <div class="panel-admin">
    {foreach $searchResults as $row}
      <div class="defWrapper">
        <p class="def">{$row->definition->htmlRep}</p>

        <p class="defDetails text-muted">
          ID: {$row->definition->id} |
          sursa: {$row->source->shortName|escape} |
          starea: {$row->definition->getStatusName()} |

          <a href="definitionEdit.php?definitionId={$row->definition->id}">editează</a>
        </p>
      </div>
    {/foreach}
  </div>

  <form>
    <input type="hidden" name="search" value="{$search|escape}"/>
    <input type="hidden" name="replace" value="{$replace|escape}"/>
    <input type="hidden" name="sourceId" value="{$sourceId}"/>
    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>
  </form>
{/block}
