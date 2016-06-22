{extends file="layout.tpl"}

{block name=title}Editare definiție{/block}

{block name=content}
  {if $isOCR}
    {$title='Adăugare definiție OCR'}
  {else}
    {$title="Editare definiție {$def->id}"}
  {/if}
  <h3>{$title}</h3>

  <form action="definitionEdit.php" method="post" class="form-horizontal">
    <input type="hidden" name="definitionId" value="{$def->id}"/>
    <input type="hidden" name="isOCR" value="{$isOCR}"/>

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

    <div class="form-group"">
      <label class="col-sm-2 control-label">sursă</label>
      <div class="col-sm-10">
        {if $source->canModerate}
          {include file="bits/sourceDropDown.tpl" sources=$allModeratorSources src_selected=$def->sourceId skipAnySource=true}
        {else}
          <input type="hidden" name="source" value="{$def->sourceId}"/>
          {$source->shortName}
          
          <span class="tooltip2" title="Sursa nu este deschisă pentru moderare și nu poate fi modificată.">&nbsp;</span>
        {/if}
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">stare</label>
      <div class="col-sm-9">
        {include file="bits/statusDropDown.tpl" name="status" selectedStatus=$def->status}
      </div>

      <div class="col-sm-1">
        <span class="tooltip2" title="Dacă treceți o definiție în starea ștearsă, ea va fi automat disociată de orice intrare. Notă: Definiția va
                                      fi imposibil de găsit la o căutare ulterioară, tocmai din cauza disocierii. Definiția este încă disponibilă în
                                      panoul de pagini recent vizitate.">&nbsp;</span>
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

        <span class="tooltip2" title="Comentariul va fi vizibil public într-un subalineat al definiției. Folosiți acest câmp pentru a face adnotări pe
                                      marginea unei definiții fără a altera forma originală a definiției.">&nbsp;</span>
      </label>

      <div class="col-sm-10">
        <textarea id="comment" name="commentContents" class="form-control" rows="3">{if $comment}{$comment->contents|escape}{/if}</textarea>
      </div>
    </div>

    {if $commentUser}
      <div class="form-group">
        <label class="col-sm-2 control-label"></label>

        <div class="col-sm-10">
          <input id="preserveCommentUser" type="checkbox" name="preserveCommentUser" value="1" checked="checked">
          <label for="preserveCommentUser">Păstrează autorul comentariului original ({$commentUser->nick|escape})</label>
        
          <span class="tooltip2" title="Dacă modificați un comentariu existent, puteți alege să vă treceți drept autor al comentariului sau să păstrați
                                        autorul versiunii anterioare. Sistemul nu ia automat această decizie. Nu fiți modești; dacă considerați că ați îmbunătățit semnificativ
                                        comentariul, însușiți-vi-l!">&nbsp;</span>
        </div>
      </div>
    {/if}

    <div class="form-group">
      <label class="col-sm-2 control-label">etichete</label>
      <div class="col-sm-10">
        <select id="tagIds" name="tagIds[]" class="form-control" multiple>
          {foreach $tagIds as $t}
            <option value="{$t}" selected></option>
          {/foreach}
        </select>
      </div>
    </div>

    <div id='similarSourceRow' class="form-group" {if !$sim->source}style="display:none"{/if}>
      <label class="col-sm-2 control-label"></label>
      <div class="col-sm-10">

        <input type="checkbox" id="similarSource" name="similarSource" value="1" {if $def->similarSource}checked="checked"{/if}/>
        <label for="similarSource">Definiție identică cu cea din <span class="similarSourceName"></span></label>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label"></label>
      <div class="col-sm-10">

        <div class="btn-group">
          <input type="button" class="btn btn-default" id="refreshButton" value="Reafișează"/>
          <span class="tooltip2" title="Tipărește definiția și comentariul cu modificările făcute. Modificările nu sunt încă salvate.">&nbsp;</span>
        </div>

        <div class="btn-group">
          <input type="submit" class="btn btn-primary" name="but_accept" value="Salvează"/>
          {if $isOCR}
            <input type="submit" name="but_next_ocr" value="Salvează și preia următoarea definiție OCR"/>
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

  <h3>Previzualizare</h3>

  <div class="panel panel-default">
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
