{$finished=$finished|default:true}
{extends "layout-admin.tpl"}

{block "title"}Lista intrărilor{/block}

{block "content"}
  <h3>Lista {if !$finished}temporară a {/if}definițiilor structurate modificate și a intrărilor posibil afectate de înlocuirea în masă</h3>
  {if $finished}
    <p class="alert alert-danger">Accesarea <b>directă</b>, exceptând modalitățile de deschidere în altă filă/fereastră, a altor legături decât cele ale <b>intrărilor</b> (care deschid <b>o nouă filă</b> pentru editare) duce la imposibilitatea revenirii la această pagină.</p>
  {else}
    <p class="alert alert-success">Cea finală va fi disponibilă la terminarea înlocuirii în masă. Puteți închide oricând această filă.</p>
  {/if}
  <div class="panel-admin">
    <div class="panel panel-default">
      <div class="panel-heading clearfix" id="panel-heading">
      <div class="pull-right">
        <span>Coduri de culoare intrări:</span>
        <label class="btn btn-primary btn-xs">Terminată</label>
        <label class="btn btn-warning btn-xs">În lucru</label>
      </div>

        <i class="glyphicon glyphicon-user"></i>
        {$modUser}
      </div>

      <div class="panel-body" id="panel-body">
        {foreach $defResults as $row}
          {$objId=$row->definition->id}

          {include "bits/definition.tpl" showStatus=1 showFlagTypo=1 showUser=0}

          <div class="entryWrapper">
            {foreach $entryResults[$objId] as $entry}
              {$btnClass = "btn btn-{if $entry->structStatus==4}primary
                                    {else}warning{/if} btn-xs"}
              {include "bits/entry.tpl" editLink=1 editLinkClass=$btnClass target="_blank"}
            {/foreach}
          </div>
        {/foreach}
      </div>

      <div class="panel-footer">
      </div>
    </div>
  </div>
  {if $finished}
  <a href="index.php" class="btn btn-primary">
      <i class="glyphicon glyphicon-step-backward"></i>
      înapoi la pagina moderatorului
  </a>
  {/if}
{/block}
