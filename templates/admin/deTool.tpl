{extends file="admin/layout.tpl"}

{block name=title}Definiție din DE{/block}

{block name=headerTitle}
  Definiție din Dicționarul enciclopedic: {$def->lexicon} ({$def->id})
{/block}

{block name=content}
  <form action="deTool.php" method="post">
    Sari la prefixul:
    <input type="text" name="jumpPrefix" value="">
  </form>
  <br>

  {$def->htmlRep}
  <br><br>

  <form action="deTool.php" method="post">
    <input type="hidden" name="definitionId" value="{$def->id}">

    <table id="detLexems">
      <tr>
        <th>lexem</th>
        <th>modele</th>
      </tr>
      <tr id="detRowStem" style="display: none">
        <td>
          <input id="detLexemStem" class="detLexem" type="text" name="lexemId[]" value="">
        </td>
        <td>
          <input id="detModelsStem" class="detModels" type="text" name="models[]" value="">
        </td>
      </tr>
      {foreach from=$lexemIds item=l key=i}
        <tr>
          <td>
            <input class="detLexem" type="text" name="lexemId[]" value="{$l}">
          </td>
          <td>
            {$m=$models[$i]}
            <input class="detModels" type="text" name="models[]" value="{','|implode:$m}">
          </td>
        </tr>
      {/foreach}
    </table>
    <a id="detAddRow" href="#">adaugă o linie</a>
    <br><br>

    <input type="submit" name="butTest" value="testează">
    &nbsp;
    <input type="submit" name="butSave" value="salvează">
    &nbsp;
    <button id="butNext" {if $nextId}data-definition-id="{$nextId}"{else}disabled{/if}>următoarea »</button>
  </form>

  <script>
   $(deToolInit);
  </script>
{/block}
