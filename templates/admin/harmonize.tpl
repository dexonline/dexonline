{extends "layout-admin.tpl"}

{block "title"}
  Armonizare lexem-etichetă
{/block}

{block "content"}
  <h3>Armonizare lexem-etichetă</h3>

  <div class="voffset4"></div>

  <h4>Adăugarea unei etichete</h4>

  <form class="form-inline" method="post">
    <table class="table table-hover">
      <tr>
        <th>când modelul este...</th>
        <th>... aplică eticheta</th>
        <th>acțiuni</th>
      </tr>

      {foreach $harmonizeTags as $ht}
        <tr>
          <td>
            {$ht->modelType}{$ht->modelNumber|default:' (orice număr)'}
          </td>
          <td>
            {include "bits/tag.tpl" t=$ht->getTag()}
          </td>
          <td>
            <a class="btn btn-sm btn-danger deleteRuleLink"
              href="?deleteHarmonizeTagId={$ht->id}">
              <i class="glyphicon glyphicon-trash"></i>
            </a>
            {if $ht->countPending()}
              <a class="btn btn-sm btn-default"
                href="?applyHarmonizeTagId={$ht->id}"
                title="aplică regula lexemelor pentru care ea nu este respectată">
                aplică ({$ht->countPending()})
              </a>
            {/if}
          </td>
        </tr>
      {/foreach}

      <tr>
        <td>
          <div class="form-group">
            <span data-model-dropdown>
              <select class="form-control" name="modelType" data-model-type>
                {foreach $modelTypes as $mt}
                  <option value="{$mt->code}">{$mt->code}</option>
                {/foreach}
              </select>
              <select
                class="form-control"
                name="modelNumber"
                data-model-number
                data-all-option="oricare|">
              </select>
            </span>
          </div>
        </td>
        <td>
          <select class="tagLookup" name="tagId"></select>
        </td>
        <td>
          <button class="btn btn-sm btn-primary" type="submit" name="saveHarmonizeTagButton">
            <i class="glyphicon glyphicon-floppy-disk"></i>
            salvează
          </button>
        </td>
      </tr>

    </table>
  </form>

  <div class="voffset4"></div>

  <h4>Schimbarea tipului de model</h4>

  <form class="form-inline" method="post">
    <table class="table table-hover">
      <tr>
        <th>când modelul este...</th>
        <th>... și există eticheta...</th>
        <th>... schimbă modelul în</th>
        <th>acțiuni</th>
      </tr>

      {foreach $harmonizeModels as $hm}
        <tr>
          <td>
            {$hm->modelType}{$hm->modelNumber|default:' (orice număr)'}
          </td>
          <td>
            {include "bits/tag.tpl" t=$hm->getTag()}
          </td>
          <td>
            {$hm->newModelType}{$hm->newModelNumber|default:' (păstrează numărul)'}
          </td>
          <td>
            <a class="btn btn-sm btn-danger deleteRuleLink"
              href="?deleteHarmonizeModelId={$hm->id}">
              <i class="glyphicon glyphicon-trash"></i>
            </a>
            {if $hm->countPending()}
              <a class="btn btn-sm btn-default"
                href="?applyHarmonizeModelId={$hm->id}"
                title="aplică regula lexemelor pentru care ea nu este respectată">
                aplică ({$hm->countPending()})
              </a>
            {/if}
          </td>
        </tr>
      {/foreach}

      <tr>
        <td>
          <div class="form-group">
            <span data-model-dropdown>
              <select class="form-control" name="modelType" data-model-type>
                {foreach $modelTypes as $mt}
                  <option value="{$mt->code}">{$mt->code}</option>
                {/foreach}
              </select>
              <select
                class="form-control"
                name="modelNumber"
                data-model-number
                data-all-option="oricare|">
              </select>
            </span>
          </div>
        </td>
        <td>
          <select class="tagLookup" name="tagId"></select>
        </td>
        <td>
          <div class="form-group">
            <span data-model-dropdown>
              <select class="form-control" name="newModelType" data-model-type>
                {foreach $modelTypes as $mt}
                  <option value="{$mt->code}">{$mt->code}</option>
                {/foreach}
              </select>
              <select
                class="form-control"
                name="newModelNumber"
                data-model-number
                data-all-option="neschimbat|">
              </select>
            </span>
          </div>
        </td>
        <td>
          <button class="btn btn-sm btn-primary" type="submit" name="saveHarmonizeModelButton">
            <i class="glyphicon glyphicon-floppy-disk"></i>
            salvează
          </button>
        </td>
      </tr>

    </table>
  </form>

  <div class="alert alert-warning" role="alert">
    Folosiți cu prudență aceste reguli. Aplicarea lor nu regenerează
    paradigmele lexemelor afectate. Evitați schimbările care ar necesita
    schimbarea paradigmei (cum ar fi A1 → A2).
  </div>

{/block}
