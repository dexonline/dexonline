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
  <a href="definitionEdit?definitionId={$def->id}">editează</a>
  <br><br>

  <form action="deTool.php" method="post">
    <input type="hidden" name="definitionId" value="{$def->id}">

    <table id="detLexems">
      <tr>
        <th>lexem</th>
        <th>modele</th>
      </tr>
      <tr id="detStemRow" style="display: none">
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
            <input class="detModels" type="text" name="models[]" value="{$models[$i]}">
          </td>
        </tr>
      {/foreach}
    </table>
    <a id="detAddRow" href="#">adaugă o linie</a>
    <br><br>

    <input id="capitalize" type="checkbox" name="capitalize" value="1" {if $capitalize}checked{/if}>
    <label for="capitalize">scrie cu majusculă lexemele I3</label>
    <br>
    <input id="deleteOrphans" type="checkbox" name="deleteOrphans" value="1" {if $deleteOrphans}checked{/if}>
    <label for="deleteOrphans">șterge lexemele care devin neasociate</label>
    <br><br>

    <input type="submit" name="butPrev" value="« anterioara">
    <input id="butTest" type="submit" name="butTest" value="testează">
    <input id="butSave" type="submit" name="butSave" value="salvează" {if !$passedTests}disabled{/if}>
    <input type="submit" name="butNext" value="următoarea »">
  </form>

  <script>
   $(deToolInit);
  </script>
{/block}
