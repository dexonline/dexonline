{assign var="withCharmap" value=false scope=parent}
{extends "layout-admin.tpl"}

{block "title"}Intrări cu discrepanțe la lexemele principale{/block}

{block "content"}

  <h3>
    Intrări cu discrepanțe la lexemele principale
    {if count($entries) == $numEntries}
      ({$numEntries})
    {else}
      ({$entries|count} din {$numEntries} afișate)
    {/if}
  </h3>

  <p>
    Sunt listate intrările pentru care numărul de lexeme principale și bifa
    „lexeme principale multiple” sunt în dezacord. Puteți edita valorile
    bifelor (nu uitați să salvați la final).
  </p>

  {if count($entries)}
    <form method="post">
      <table class="table tablesorter" role="grid">
        <thead>
          <tr>
            <th scope="col">descriere</th>
            <th class="text-center" scope="col">bifă</th>
            <th scope="col">lexeme</th>
            <th scope="col">modificată</th>
            <th scope="col">la data</th>
          </tr>
        </thead>
        <tbody>
          {foreach $entries as $e}
            <tr id="{$e->id}">
              <td class="col-md-2">
                {include "bits/entry.tpl" entry=$e editLink=true}
              </td>
              <td class="col-md-1 text-center" data-text="{$e->multipleMains}">
                <input type="hidden" name="entryIds[]" value="{$e->id}">
                {include "bs/checkbox.tpl"
                  divClass='form-check-inline'
                  name="multipleMains[]"
                  label=""
                  checked=$e->multipleMains
                  value=$e->id}
              </td>
              <td class="col-md-7 p-1">
                {foreach $e->getMainLexemes() as $lexeme}
                  {include "bits/lexemeLink.tpl" boxed=true}
                {/foreach}
              </td>
              <td class="col-md-1 userNick">{$e->nick}</td>
              <td class="col-md-1" data-text="{$e->modDate}">
                {$e->modDate|date:'dd.MM.yyyy'}
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <button type="submit" class="btn btn-primary" name="saveButton">
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>
    </form>
  {/if}

{/block}
