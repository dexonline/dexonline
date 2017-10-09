{extends "layout-admin.tpl"}

{block "title"}
  {if $t->id}
    Arbore {$t->description}
  {else}
    Arbore nou
  {/if}
{/block}

{block "content"}
  {include "bits/phpConstants.tpl"}

  <h3>
    {if $t->id}
      Editează arborele
    {else}
      Adaugă un arbore
    {/if}
  </h3>

  {* Allow the JS editor to run *}
  <div id="editable" style="display: none"></div>

  {* Stem meaning editor that we clone whenever we append a new meaning *}
  <ul id="stemNode">
    <li>
      <div class="meaningContainer">
        <span class="id"></span>
        <span class="bc"></span>

        {* if this were empty, no radio button would be selected for new meanings *}
        <span class="type">{Meaning::TYPE_MEANING}</span>

        <span class="typeName"></span>
        <span class="tags"></span>
        <span class="tagIds"></span>
        <span class="internalRep"></span>
        <span class="htmlRep"></span>
        <span class="sources"></span>
        <span class="sourceIds"></span>
        {for $type=1 to Relation::NUM_TYPES}
          <span class="relation" data-type="{$type}"></span>
          <span class="relationIds" data-type="{$type}"></span>
        {/for}
      </div>
    </li>
  </ul>

  <form class="form-horizontal" method="post" role="form">
    <input type="hidden" name="id" value="{$t->id}">
    <input type="hidden" name="jsonMeanings" value="">

    <div class="row">
      <div class="col-md-6">
        {include "bits/fhf.tpl" field="description" value=$t->description label="descriere"}

        <div class="form-group {if isset($errors.status)}has-error{/if}">
          <label class="col-md-2 control-label">stare</label>
          <div class="col-md-10">
            <select name="status" class="form-control">
              {foreach $statusNames as $i => $s}
                <option value="{$i}" {if $i == $t->status}selected{/if}>{$s}</option>
              {/foreach}
            </select>
            {include "bits/fieldErrors.tpl" errors=$errors.status|default:null}
          </div>
        </div>

      </div>

      <div class="col-md-6">
        <div class="form-group">
          <label for="entryIds" class="col-md-2 control-label">intrări</label>
          <div class="col-md-10">
            <select id="entryIds" name="entryIds[]" style="width: 100%" multiple>
              {foreach $entryIds as $e}
                <option value="{$e}" selected></option>
              {/foreach}
            </select>

            Tipuri de model:
            {foreach $modelTypes as $mt}
              <span class="label label-default">{$mt->modelType}</span>
            {/foreach}
          </div>
        </div>

        {if $homonyms}
          <div class="form-group">
            <label class="col-md-2">omonime</label>
            <div class="col-md-10">

              {foreach $homonyms as $h}
                <div>
                  <a href="editTree.php?id={$h->id}">{$h->description}</a>
                </div>
              {/foreach}

            </div>
          </div>
        {/if}

      </div>
    </div>

    <div class="form-group">
      <div class="col-md-offset-1 col-md-11">
        <button type="submit" class="btn btn-success" name="saveButton">
          <i class="glyphicon glyphicon-floppy-disk"></i>
          <u>s</u>alvează
        </button>

        <button type="button"
                class="btn btn-default"
                {if !count($entryTrees)}disabled{/if}
                data-toggle="modal"
                data-target="#mergeModal">
          <i class="glyphicon glyphicon-resize-small"></i>
          unifică cu...
        </button>

        <button type="submit" class="btn btn-default" name="clone">
          <i class="glyphicon glyphicon-duplicate"></i>
          clonează
        </button>

        <a class="btn btn-link" href="{if $t->id}?id={$t->id}{/if}">
          anulează
        </a>

        <button type="submit"
                class="btn btn-danger pull-right"
                name="delete"
                {if !$canDelete}
                disabled
                title="Nu puteți șterge acest arbore, deoarece el are sensuri și/sau relații."
                {/if}>
          <i class="glyphicon glyphicon-trash"></i>
          șterge
        </button>
      </div>
    </div>

  </form>

  <div class="modal fade" id="mergeModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" role="form">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Unifică arborele cu...</h4>
          </div>

          <div class="modal-body">
            <p>
              Iată arborii asociați cu oricare din intrările acestui arbore.
            </p>
            <input type="hidden" name="id" value="{$t->id}">
            <select name="mergeTreeId" class="form-control">
              {foreach $entryTrees as $other}
                <option value="{$other->id}">{$other->description}</option>
              {/foreach}
            </select>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="mergeButton">
              <i class="glyphicon glyphicon-resize-small"></i>
              unifică
            </button>
            <button type="button" class="btn btn-link" data-dismiss="modal">renunță</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {if count($relatedMeanings)}
    <div class="panel panel-default">
      <div class="panel-heading">Arbori în relație cu acesta</div>

      <table class="table table-condensed table-bordered">
        <thead>
          <tr>
            <th>arbore</th>
            <th>sens</th>
            <th>tip</th>
            <th>text</th>
          </tr>
        </thead>

        <tbody>
          {foreach $relatedMeanings as $m}
            <tr>
              <td>
                <a href="editTree.php?id={$m->getTree()->id}">
                  {$m->getTree()->description}
                </a>
              </td>
              <td>
                <strong>{$m->breadcrumb}</strong>
              </td>
              <td>
                {Relation::$TYPE_NAMES[$m->relationType]}
              </td>
              <td>
                {$m->htmlRep}
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {/if}

  {if count($treeMentions)}
    <div class="panel panel-default">
      <div class="panel-heading">
        {$treeMentions|count} mențiuni despre acest arbore
      </div>

      <table class="table table-condensed table-bordered">
        <thead>
          <tr>
            <th>arbore-sursă</th>
            <th>sens</th>
          </tr>
        </thead>

        <tbody>
          {foreach $treeMentions as $m}
            <tr>
              <td>
                <a href="{$wwwRoot}editTree.php?id={$m->srcId}">{$m->srcDesc}</a>
              </td>
              <td><b>{$m->breadcrumb}</b> {$m->htmlRep}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {/if}

  {if count($meaningMentions)}
    <div class="panel panel-default">
      <div class="panel-heading">
        {$meaningMentions|count} mențiuni despre sensuri din acest arbore
      </div>

      <table class="table table-condensed table-bordered">
        <thead>
          <tr>
            <th>arbore-sursă</th>
            <th>sens-sursă</th>
            <th>sens-destinație</th>
          </tr>
        </thead>

        <tbody>
          {foreach $meaningMentions as $m}
            <tr>
              <td>
                <a href="{$wwwRoot}editTree.php?id={$m->tsrcId}">{$m->tsrcDesc}</a>
              </td>
              <td><b>{$m->srcBreadcrumb}</b> {$m->srcRep}</td>
              <td><b>{$m->destBreadcrumb}</b></td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {/if}

  <form>
    <div class="panel panel-default">
      <div class="panel-heading">Sensuri</div>
      <div class="panel-body">
        <div class="treeWrapper">
          {include "bits/editableMeaningTree.tpl"
          meanings=$t->getMeanings()
          id="meaningTree"}
        </div>

        <div>
          {if $canEdit}
            <div class="btn-group">
              <button type="button"
                      class="btn btn-default btn-sm"
                      id="addMeaningButton"
                      title="Adaugă un sens ca frate al sensului selectat. Dacă nici un sens nu este selectat, adaugă un sens la sfârșitul listei.">
                adaugă sens
              </button>
              <button type="button"
                      class="btn btn-default btn-sm meaningAction"
                      id="addSubmeaningButton"
                      disabled
                      title="Adaugă un sens ca ultimul fiu al sensului selectat">
                adaugă subsens
              </button>
              <button type="button"
                      class="btn btn-danger btn-sm meaningAction"
                      id="deleteMeaningButton"
                      data-toggle="popover"
                      role="button"
                      tabindex="0"
                      disabled>
                <i class="glyphicon glyphicon-trash"></i>
                șterge sens
              </button>

              <div id="deletePopoverContent" style="display: none">
                <button type="button"
                        class="btn btn-danger btn-sm meaningAction deleteMeaningConfirmButton">
                  <i class="glyphicon glyphicon-trash"></i>
                  confirm
                </button>
                <button type="button"
                        class="btn btn-default btn-sm meaningAction deleteMeaningCancelButton">
                  <i class="glyphicon glyphicon-remove"></i>
                  m-am răzgândit
                </button>
              </div>

            </div>

            <div class="btn-group">
              <button type="button"
                      class="btn btn-default btn-sm meaningAction"
                      id="meaningLeftButton"
                      disabled
                      title="Sensul devine fratele următor al tatălui său.">
                <i class="glyphicon glyphicon-arrow-left"></i>
              </button>
              <button type="button"
                      class="btn btn-default btn-sm meaningAction"
                      id="meaningRightButton"
                      disabled
                      title="Sensul devine fiu al fratelui său anterior.">
                <i class="glyphicon glyphicon-arrow-right"></i>
              </button>
              <button type="button"
                      class="btn btn-default btn-sm meaningAction"
                      id="meaningDownButton"
                      disabled
                      title="Sensul schimbă locurile cu fratele său următor.">
                <i class="glyphicon glyphicon-arrow-down"></i>
              </button>
              <button type="button"
                      class="btn btn-default btn-sm meaningAction"
                      id="meaningUpButton"
                      disabled
                      title="Sensul schimbă locurile cu fratele său anterior.">
                <i class="glyphicon glyphicon-arrow-up"></i>
              </button>
            </div>
          {/if}
        </div>
      </div>
    </div>

    {if $canEdit}
      <div class="panel panel-default">
        <div class="panel-heading">Editorul de sensuri</div>
        <div class="panel-body">

          <div class="row">

            <div class="col-md-8">
              <div class="form-group">
                <label>tip</label>
                <div>
                  {foreach Meaning::$FIELD_NAMES as $i => $tn}
                    <label class="radio-inline">
                      <input type="radio"
                             name="editorType"
                             class="editorObj editorType"
                             value="{$i}"
                             disabled>
                      {$tn}
                    </label>
                  {/foreach}
                </div>
              </div>
              <div class="form-group">
                <label>sens</label>
                <textarea id="editorRep"
                          class="form-control editorObj"
                          rows="6"
                          disabled></textarea>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <div>
                  <label for="editorSources">surse</label>

                  {include "bits/frequentObjects.tpl"
                           list=$frequentSources
                           text="shortName"
                           classes="frequentSource editorObj"
                           disabled=true}
                </div>

                <select id="editorSources" class="editorObj" multiple disabled>
                  {foreach $sources as $s}
                    <option value="{$s->id}">{$s->shortName}</option>
                  {/foreach}
                </select>
              </div>

              <div class="form-group">
                <div>
                  <label for="editorTags">etichete</label>

                  {include "bits/frequentObjects.tpl"
                           list=$frequentTags
                           text="value"
                           classes="frequentTag editorObj"
                           disabled=true}
                </div>
                
                <select id="editorTags" class="editorObj" multiple disabled></select>
              </div>

              <div class="form-group">
                <label>relații:</label>

                <select id="relationType" class="form-control editorObj" disabled>
                  {foreach Relation::$TYPE_NAMES as $type => $name}
                    <option value="{$type}" title="{$name}">{$name}</option>
                  {/foreach}
                </select>

                <span class="relationWrapper" data-type="1">
                  <select class="form-control editorRelation editorObj" multiple disabled>
                  </select>
                </span>
                <span class="relationWrapper" data-type="2">
                  <select class="form-control editorRelation editorObj" multiple disabled>
                  </select>
                </span>
                <span class="relationWrapper" data-type="3">
                  <select class="form-control editorRelation editorObj" multiple disabled>
                  </select>
                </span>
                <span class="relationWrapper" data-type="4">
                  <select class="form-control editorRelation editorObj" multiple disabled>
                  </select>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    {/if}

  </form>
{/block}
