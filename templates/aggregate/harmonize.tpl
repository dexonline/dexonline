{extends "layout-admin.tpl"}

{block "title"}
  Armonizare lexem-etichetă
{/block}

{block "content"}
  <h3 class="mb-3">Armonizare lexem-etichetă</h3>

  <h4>Adăugarea unei etichete</h4>

  <form class="mb-4" method="post">
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
            <a class="btn btn-sm btn-outline-danger deleteRuleLink"
              href="?deleteHarmonizeTagId={$ht->id}">
              {include "bits/icon.tpl" i=delete}
            </a>
            {if $ht->countPending()}
              <a class="btn btn-sm btn-outline-secondary"
                href="?applyHarmonizeTagId={$ht->id}"
                title="aplică regula lexemelor pentru care ea nu este respectată">
                aplică ({$ht->countPending()})
              </a>
            {/if}
          </td>
        </tr>
      {/foreach}

      <tr class="align-middle">
        <td>
          {include "bits/modelDropDown.tpl" allOption="oricare|"}
        </td>
        <td>
          <select class="tagLookup" name="tagId"></select>
        </td>
        <td>
          <button class="btn btn-sm btn-primary" type="submit" name="saveHarmonizeTagButton">
            {include "bits/icon.tpl" i=save}
            salvează
          </button>
        </td>
      </tr>

    </table>
  </form>

  <h4>Schimbarea tipului de model</h4>

  {notice icon="warning"}
    Folosiți cu prudență aceste reguli. Aplicarea lor nu regenerează
    paradigmele lexemelor afectate. Evitați schimbările care ar necesita
    schimbarea paradigmei (cum ar fi A1 → A2).
  {/notice}

  <form method="post">
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
            <a class="btn btn-sm btn-outline-danger deleteRuleLink"
              href="?deleteHarmonizeModelId={$hm->id}">
              {include "bits/icon.tpl" i=delete}
            </a>
            {if $hm->countPending()}
              <a class="btn btn-sm btn-outline-secondary"
                href="?applyHarmonizeModelId={$hm->id}"
                title="aplică regula lexemelor pentru care ea nu este respectată">
                aplică ({$hm->countPending()})
              </a>
            {/if}
          </td>
        </tr>
      {/foreach}

      <tr class="align-middle">
        <td>
          {include "bits/modelDropDown.tpl" allOption="oricare|"}
        </td>
        <td>
          <select class="tagLookup" name="tagId"></select>
        </td>
        <td>
          {include "bits/modelDropDown.tpl"
            modelTypeName="newModelType"
            modelNumberName="newModelNumber"
            allOption="neschimbat|"}
        </td>
        <td>
          <button class="btn btn-sm btn-primary" type="submit" name="saveHarmonizeModelButton">
            {include "bits/icon.tpl" i=save}
            salvează
          </button>
        </td>
      </tr>

    </table>
  </form>

{/block}
