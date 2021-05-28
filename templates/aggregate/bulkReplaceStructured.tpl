{$finished=$finished|default:true}
{extends "layout-admin.tpl"}

{block "title"}Lista intrărilor{/block}

{block "content"}
  <h3>
    Lista {if !$finished}temporară a{/if} definițiilor structurate modificate
    și a intrărilor posibil afectate de înlocuirea în masă
  </h3>

  {if $finished}
    <div class="alert alert-danger">
      Accesarea <b>directă</b>, exceptând modalitățile de deschidere în altă
      filă/fereastră, a altor legături decât cele ale <b>intrărilor</b> (care
      deschid <b>o nouă filă</b> pentru editare) duce la imposibilitatea
      revenirii la această pagină.
    </div>
  {else}
    <div class="alert alert-success">
      Cea finală va fi disponibilă la terminarea înlocuirii în masă. Puteți
      închide oricând această filă.
    </div>
  {/if}

  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <span class="flex-grow-1">
        {include "bits/icon.tpl" i=person}
        {$modUser}
      </span>

      <span>Legendă intrări:</span>
      <label class="btn btn-primary btn-sm ms-2">terminată</label>
      <label class="btn btn-warning btn-sm ms-2">în lucru</label>
    </div>

    <div class="card-body">
      {foreach $defResults as $row}
        {$objId=$row->definition->id}

        {include "bits/definition.tpl" showStatus=1 showFlagTypo=1 showUser=0}

        <div class="entryWrapper">
          {foreach $entryResults[$objId] as $entry}
            {$btnClass = "{if $entry->structStatus==4}primary{else}warning{/if}"}
            {include "bits/entry.tpl"
              editLink=1
              editLinkClass="btn btn-sm btn-{$btnClass}"
              target="_blank"}
          {/foreach}
        </div>
      {/foreach}
    </div>
  </div>

  {if $finished}
    <a href="{Router::link('aggregate/dashboard')}" class="btn btn-link">
      {include "bits/icon.tpl" i=arrow_back}
      înapoi la pagina moderatorului
    </a>
  {/if}
{/block}
