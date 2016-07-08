{extends file="layout.tpl"}

{block name=title}
  {if $e->id}
    Intrare {$e->description}
  {else}
    Intrare nouă
  {/if}
{/block}

{block name=content}
  <h3>
    {if $e->id}
      Editează intrarea
    {else}
      Adaugă o intrare
    {/if}
  </h3>

  {include file="bits/phpConstants.tpl"}

  <form action="editEntry.php" method="post" role="form">
    <input type="hidden" name="id" value="{$e->id}">

    <div class="row">

      <div class="col-md-6">
        {include "bits/fgf.tpl" field="description" value=$e->description label="descriere"}

        <div class="form-group {if isset($errors.structStatus)}has-error{/if}">
          <label for="structStatus">structurare</label>
          {include file="bits/structStatus.tpl" selected=$e->structStatus canEdit=$canEdit.structStatus}
          {include "bits/fieldErrors.tpl" errors=$errors.structStatus|default:null}
        </div>

        <div class="form-group">
          <label for="structuristId">structurist</label>
          <select id="structuristId" name="structuristId">
            {if $e->structuristId}
              <option value="{$e->structuristId}" selected></option>
            {/if}
          </select>
          {include "bits/fieldErrors.tpl" errors=$errors.structuristId|default:null}
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group"">
          <label for="lexemIds">lexeme</label>
          <select id="lexemIds" name="lexemIds[]" style="width: 100%" multiple>
            {foreach $lexemIds as $l}
              <option value="{$l}" selected></option>
            {/foreach}
          </select>

          Tipuri de model:
          {foreach $modelTypes as $mt}
            <span class="label label-default">{$mt}</span>
          {/foreach}
        </div>

        <div class="form-group"">
          <label for="treeIds">arbori de sensuri</label>
          <select id="treeIds" name="treeIds[]" style="width: 100%" multiple>
            {foreach $treeIds as $t}
              <option value="{$t}" selected></option>
            {/foreach}
          </select>
        </div>
      </div>

    </div>

    <div class="form-group {if isset($errors.structStatus)}has-error{/if}">
      <label for="structStatus">structurare</label>
      {include file="bits/structStatus.tpl" selected=$e->structStatus canEdit=$canEdit.structStatus}
      {include "bits/fieldErrors.tpl" errors=$errors.structStatus|default:null}
    </div>

    <div class="form-group">
      <label for="structuristId">structurist</label>
      <select id="structuristId" name="structuristId">
        {if $e->structuristId}
          <option value="{$e->structuristId}" selected></option>
        {/if}
      </select>
      {include "bits/fieldErrors.tpl" errors=$errors.structuristId|default:null}
    </div>

    <div class="form-group"">
      <label for="treeIds">arbori de sensuri</label>
      <select id="treeIds" name="treeIds[]" style="width: 100%" multiple>
        {foreach $treeIds as $t}
          <option value="{$t}" selected></option>
        {/foreach}
      </select>
    </div>

    <button type="submit" class="btn btn-primary" name="save">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      salvează
    </button>

    <button type="submit" class="btn btn-default" name="createTree">
      <i class="glyphicon glyphicon-tree-deciduous"></i>
      creează un arbore de sensuri
    </button>

    <a href="{if $e->id}?id={$e->id}{/if}">
      anulează
    </a>

    <button type="submit" class="btn btn-danger pull-right" name="delete">
      <i class="glyphicon glyphicon-trash"></i>
      șterge
    </button>
  </form>

  {if $e->id}
    <h3>Arbori de sensuri asociați ({$e->getTrees()|count})</h3>

    {foreach $e->getTrees() as $t}
      <div class="panel panel-default">
        <div class="panel-heading">
          {$t->description}
          <a href="editTree.php?id={$t->id}" class="pull-right">
            <i class="glyphicon glyphicon-pencil"></i>
            editează
          </a>
        </div>
        <div class="panel-body">
          {include file="bits/meaningTree.tpl" meanings=$t->getMeanings() id="meaningTree-{$t->id}"}
        </div>
      </div>
    {/foreach}

    <h3>Definiții asociate ({$searchResults|count})</h3>

    <form class="form-inline">
      <div class="form-group">

        <select id="defFilterSelect" class="form-control">
          <option value="">toate</option>
          <option value="structured">structurate</option>
          <option value="unstructured">nestructurate</option>
        </select>

        <select class="toggleRepSelect form-control" data-order="1">
          <option value="0">text</option>
          <option value="1" selected>html</option>
        </select>

        <select class="toggleRepSelect form-control" data-order="2">
          <option value="0">expandat</option>
          <option value="1" selected>abreviat</option>
        </select>

      </div>
    </form>

    {foreach from=$searchResults item=row}
      {$def=$row->definition}
      <div class="defWrapper {if $def->structured}structured{else}unstructured{/if}" id="def_{$def->id}">
        <div data-code="0" class="rep internal hiddenRep">{$def->internalRepAbbrev|escape}</div>
        <div data-code="1" class="rep hiddenRep">{$def->htmlRepAbbrev}</div>
        <div data-code="2" class="rep internal hiddenRep">{$def->internalRep|escape}</div>
        <div data-code="3" data-active class="rep">{$def->htmlRep}</div>
        <span class="defDetails">
          id: {$def->id}
          | sursa: {$row->source->shortName|escape}
          | starea: {$def->getStatusName()}
          | <a href="{$wwwRoot}admin/definitionEdit.php?definitionId={$def->id}" target="_blank">editează</a>
          | <a href="?id={$e->id}&amp;dissociateDefinitionId={$def->id}"
               class="dissociateLink"
               title="disociază definiția de lexem"
               >disociază</a>
          | <a href="#" class="toggleRepLink" title="comută între notația internă și HTML"
               data-value="1" data-order="1" data-other-text="html">text</a>
          | <a href="#" class="toggleRepLink" title="contractează sau expandează abrevierile"
               data-value="1" data-order="2" data-other-text="abreviat">expandat</a>

          |
          <a href="#"
             title="comută definiția între structurată și nestructurată"
             >
            <span class="toggleStructuredLink" {if !$def->structured}style="display: none"{/if}>
              <i class="glyphicon glyphicon-ok"></i> structurată
            </span>
            <span class="toggleStructuredLink" {if $def->structured}style="display: none"{/if}>
              <i class="glyphicon glyphicon-remove"></i> nestructurată
            </span>
          </a>
        </span>

        {if $row->comment}
          <div class="commentInternalRep">
            Comentariu: {$row->comment->contents} -
            <a href="{$wwwRoot}utilizator/{$row->commentAuthor->nick|escape:"url"}">{$row->commentAuthor->nick|escape}</a>
          </div>
        {/if}
      </div>
    {/foreach}
  {/if}
{/block}
