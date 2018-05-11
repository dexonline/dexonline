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
          </td>
        </tr>
      {/foreach}

      <tr>
        <td>
          <div class="form-group">
            <span data-model-dropdown>
              <input type="hidden" name="locVersion" value="6.0" data-loc-version>
              <select class="form-control" name="modelType" data-model-type>
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
          </td>
        </tr>
      {/foreach}

      <tr>
        <td>
          <div class="form-group">
            <span data-model-dropdown>
              <input type="hidden" name="locVersion" value="6.0" data-loc-version>
              <select class="form-control" name="modelType" data-model-type>
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
              <input type="hidden" name="locVersion" value="6.0" data-loc-version>
              <select class="form-control" name="newModelType" data-model-type>
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

{/block}
