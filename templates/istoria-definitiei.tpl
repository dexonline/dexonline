{extends file="layout.tpl"}

{block name=title}Istoria definiției {$def->lexicon}{/block}

{block name=content}
  <div class="istoriaDefinitieiContainer">
    <h4>Istoria definiției {$def->lexicon}</h4>
    <div class="changeSets">
      {if $changeSets|@count > 0}
        {foreach from=$changeSets item=changeSet key=row_id}
          <div class="changeSet">
            <div class="changeSetHeader">Modificat la {$changeSet.NewDate|date_format:"%d %b %Y %T"} de {$changeSet.NewModUserNick|default:"NULL"}<!-- (changeset: {$changeSet.OldVersion} - {$changeSet.NewVersion}) --></div>
            <div class="changeSetContent">
              {if isset($changeSet.diff)}
                <div class="changeSetDiff">
                  <strong>Modificări la reprezentarea internă:</strong>
                  <div class="internalRepDiff">{$changeSet.diff}</div>
                </div>
              {/if}
              {if $changeSet.changesCount > 1 || ($changeSet.changesCount == 1 && !isset($changeSet.diff)) }
                <ul>
                  {if $changeSet.OldUserId != $changeSet.NewUserId}
                    <li><strong>userul</strong> s-a modificat din <em>{$changeSet.OldUserNick|default:"NULL"}</em> în <em>{$changeSet.NewUserNick|default:"NULL"}</em></li>
                  {/if}
                  {if $changeSet.OldModUserId != $changeSet.NewModUserId}
                    <li><strong>userul modificării</strong> s-a modificat din <em>{$changeSet.OldModUserNick|default:"NULL"}</em> în <em>{$changeSet.NewModUserNick|default:"NULL"}</em></li>
                  {/if}
                  {if $changeSet.OldSourceId != $changeSet.NewSourceId}
                    <li><strong>sursa</strong> s-a modificat din <em>{$changeSet.OldSourceName|default:"NULL"}</em> în <em>{$changeSet.NewSourceName|default:"NULL"}</em></li>
                  {/if}
                  {if $changeSet.OldStatus != $changeSet.NewStatus}
                    <li><strong>starea</strong> s-a modificat din <em>{$changeSet.OldStatusName|default:"NULL"}</em> în <em>{$changeSet.NewStatusName|default:"NULL"}</em></li>
                  {/if}
                  {if $changeSet.OldLexicon != $changeSet.NewLexicon}
                    <li><strong>lexiconul</strong> s-a modificat din <em>{$changeSet.OldLexicon|default:"NULL"}</em> în <em>{$changeSet.NewLexicon|default:"NULL"}</em></li>
                  {/if}
                </ul>
              {/if}
            </div>
          </div>
        {/foreach}
      {else}
        Nu există modificări la această definiție.
      {/if}
    </div>
  </div>
{/block}
