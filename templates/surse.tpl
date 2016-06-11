{extends file="layout.tpl"}

{block name=title}Surse{/block}

{block name=content}
  <div class="alert alert-info">
    <p>Duceți cursorul deasupra unui nume de dicționar pentru a vedea mai multe detalii</p>
    {if $sUser && $sUser->moderator & $smarty.const.PRIV_EDIT}
      <p>Pentru a reordona sursele, apucați de un rând, dar nu de o zonă cu text. La sfârșit, nu uitați să salvați.</p>
    {/if}
  </div>

  <form method="post" action="surse">
    <table id="sources" class="table table-striped ">
      <thead>
        <tr>
          <th class="abbreviation">Nume scurt</th>
          <th class="nick">Nume</th>
          <th>% utilizat</th>
          {if $sUser && $sUser->moderator & $smarty.const.PRIV_EDIT}
            <th>Acțiuni</th>
          {/if}
        </tr>
      </thead>
      <tbody>
        {foreach from=$sources item=s}
          {if $s->isActive || ($sUser && $sUser->moderator & $smarty.const.PRIV_EDIT)}
            <tr>
              <td class="abbreviation">
                {if $s->link && $sUser && $sUser->moderator & $smarty.const.PRIV_EDIT}
                  <a href="{$s->link}" target="_blank"><span class="sourceShortName">{$s->shortName}</span></a>
                {else}
                  <span class="sourceShortName">{$s->shortName}</span>
                {/if}
              </td>
              <td class="nick">
                <input type="hidden" name="ids[]" value="{$s->id}"/>
                <span class="sourceName">
                  {$s->name}
                  <div class="popover bottom">
                    <div class="arrow"></div>
                    <h3 class="popover-title">{$s->name}</h3>
                    <div class="popover-content">
                      Autor: {$s->author}<br/>
                      Editură: {$s->publisher}<br/>
                      Anul apariției: {$s->year}<br/>
                      Tipul:
                      {if $s->isOfficial==3}Ascuns{/if}
                      {if $s->isOfficial==2}Oficial{/if}
                      {if $s->isOfficial==1}Specializat{/if}
                      {if $s->isOfficial==0}Neoficial{/if}
                    </div>
                  </div>
                </span>
              </td>
              <td data-text="{$s->percentComplete}">{include file="bits/sourcePercentComplete.tpl" s=$s}</td>
              {if $sUser && $sUser->moderator & $smarty.const.PRIV_EDIT}
                <td><a href="editare-sursa?id={$s->id}">editează</a></td>
              {/if}
            </tr>
          {/if}
        {/foreach}
      </tbody>
    </table>
    {if $sUser && $sUser->moderator & $smarty.const.PRIV_EDIT}
      <input type="submit" name="submitButton" value="Salvează"/> &nbsp;
      <a href="editare-sursa">adaugă o sursă</a> &nbsp;
      <a href="">renunță</a>
    {/if}
  </form>

  <script>
   $(document).ready(function() {
       $("#sources").tablesorter();
   });
  </script>

  {* Drag-and-drop reordering of rows, only for admins *}
  {if $sUser && $sUser->moderator & $smarty.const.PRIV_EDIT}
    <script>
     jQuery(document).ready(function() {
         $("#sources").tableDnD();
     });
    </script>
  {/if}
{/block}
