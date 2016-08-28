{extends file="layout.tpl"}

{block name=title}Editare definiție{/block}

{block name=content}
  {if $isOCR}
    {$title='Adăugare definiție OCR'}
  {else}
    {$title="Editare definiție {$def->id}"}
  {/if}
  <h3>
    {$title}
    <span class="pull-right">
      <small>
        <a href="http://wiki.dexonline.ro/wiki/Editarea_defini%C8%9Biilor">
          <i class="glyphicon glyphicon-question-sign"></i>
          instrucțiuni
        </a>
      </small>
    </span>
  </h3>

  <form action="definitionEdit.php" method="post" class="form-horizontal">
    <input type="hidden" name="definitionId" value="{$def->id}"/>
    <input type="hidden" name="isOCR" value="{$isOCR}"/>

    <div class="form-group"">
      <label class="col-sm-2 control-label">sursă</label>
      <div class="col-sm-10">
        {if $source->canModerate}
          {include file="bits/sourceDropDown.tpl" sources=$allModeratorSources src_selected=$def->sourceId skipAnySource=true}
        {else}
          <input type="hidden" name="source" value="{$def->sourceId}"/>
          {$source->shortName}
        {/if}
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">stare</label>
      <div class="col-sm-10">
        {include file="bits/statusDropDown.tpl" name="status" selectedStatus=$def->status}
      </div>
    </div>

    {if count($typos)}
      <div class="form-group">
        <label class="col-sm-2 control-label">greșeli de tipar</label>
        <div class="col-sm-10">
          {foreach from=$typos item=typo}
            <p class="bg-danger voffset1">{$typo->problem|escape}</p>
          {/foreach}
        </div>
      </div>
    {/if}

    <div class="form-group"">
      <label for="entryIds" class="col-sm-2 control-label">intrări</label>
      <div class="col-sm-10">
        <select id="entryIds" name="entryIds[]" style="width: 100%" multiple>
          {foreach $entryIds as $e}
            <option value="{$e}" selected></option>
          {/foreach}
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">conținut</label>
      <div class="col-sm-10">
        <textarea id="internalRep" name="internalRep" class="form-control" rows="10"
                  >{$def->internalRep|escape}</textarea>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">
        comentariu (opțional)
      </label>

      <div class="col-sm-10">
        <textarea id="comment" name="commentContents" class="form-control" rows="3">{if $comment}{$comment->contents|escape}{/if}</textarea>

        {if $commentUser}
          <div class="checkbox">
            <label>
              <input type="checkbox" name="preserveCommentUser" value="1" checked="checked">
              Păstrează autorul comentariului original ({$commentUser->nick|escape})
            </label>
          </div>
        {/if}
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">etichete</label>
      <div class="col-sm-10">
        <select id="tagIds" name="tagIds[]" class="form-control" multiple>
          {foreach $tagIds as $t}
            <option value="{$t}" selected></option>
          {/foreach}
        </select>

        {** These aren't logically connected, but we like them vertically compressed **}
        <div class="checkbox" {if !$sim->source}style="display:none"{/if}>
          <label>
            <input type="checkbox" name="similarSource" value="1" {if $def->similarSource}checked="checked"{/if}>
            Definiție identică cu cea din <span class="similarSourceName"></span>
          </label>
        </div>

        <div class="checkbox">
          <label>
            <input type="checkbox" name="structured" value="1" {if $def->structured}checked="checked"{/if}>
            Definiția a fost structurată
          </label>
        </div>

      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label"></label>
      <div class="col-sm-10">

        <div class="btn-group">
          <input type="button" class="btn btn-default" id="refreshButton" value="Reafișează"/>
        </div>

        <div class="btn-group">
          <input type="submit" class="btn btn-primary" name="but_accept" value="Salvează"/>
          {if $isOCR}
            <input type="submit" class="btn btn-primary" name="but_next_ocr" value="Salvează și preia următoarea definiție OCR"/>
          {/if}
        </div>

        <div class="btn-group pull-right" id="tinymceButtonWrapper">
          <button id="tinymceToggleButton" type="button" class="btn btn-default" data-other-text="ascunde TinyMCE" href="#"
                  title="TinyMCE este un editor vizual (cu butoane de bold, italic etc.).">
            arată TinyMCE
          </button>
        </div>
      </div>
    </div>

  </form>

  <div class="panel panel-default">
    <div class="panel-heading">
      Previzualizare
    </div>

    <div class="panel-body">
      <div id="defPreview">{$def->htmlRep}</div>
      <span class="defDetails">
        Id: {$def->id} |
        Sursa: {$source->shortName|escape} |
        Trimisă de {$user->nick|escape}, {$def->createDate|date_format:"%e %b %Y"} |
        Starea: {$def->getStatusName()}
      </span>
    </div>

    <div class="panel-footer">
      <i class="glyphicon glyphicon-comment"></i>
      <span id="commentPreview">{$comment->htmlContents|default:''}</span>
    </div>
  </div>

  <pre id="similarRecord"><!--{$sim->getJson()}--></pre>

  <div id="similarSourceMessageYes">
    <div class="panel panel-default">

      <div class="panel-heading">
        Definiția corespunzătoare din <span class="similarSourceName"></span>
        <a class="pull-right" id="similarDefinitionEdit" href="?definitionId={$sim->definition->id|default:''}" target="_blank">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      </div>

      <div class="panel-body">
        <div id="similarRep"></div>
      </div>
    </div>

    <div class="panel panel-default">

      <div class="panel-heading" id="similarNotIdentical">
        <i class="glyphicon glyphicon-remove text-danger"></i>
        Diferențe față de definiția din <span class="similarSourceName"></span>:
      </div>

      <div class="panel-heading" id="similarIdentical">
        <i class="glyphicon glyphicon-ok text-success"></i>
        Definiția este identică cu cea din <span class="similarSourceName"></span>.
      </div>

      <div class="panel-body" id="similarDiff"></div>
    </div>
  </div>

  <div id="similarSourceMessageNoSource">
    Nu există o sursă anterioară.
  </div>

  <div id="similarSourceMessageNoDefinition">
    Nu există o definiție similară în <span class="similarSourceName"></span>.
  </div>

{/block}
