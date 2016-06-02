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

    <table id="lexemsTable">
      <tr>
        <th>lexem</th>
        <th>modele</th>
        <th>scurtături</th>
      </tr>
      <tr id="stem">
        <td>
          <select class="lexem" name="lexemId[]" style="width: 300px;">
          </select>
        </td>
        <td>
          <select class="model" name="model[]" style="width: 500px;">
            <option value="I3" selected>I3 (nume proprii)</option>
          </select>
        </td>
        <td>
          <a class="shortcutI3" href="#">I3</a>
        </td>
      </tr>
      {foreach $lexemIds as $i => $l}
        <tr>
          <td>
            <select class="lexem" name="lexemId[]" style="width: 300px;">
              <option value="{$l}" selected></option>
            </select>
          </td>
          <td>
            <select class="model" name="model[]" style="width: 500px;">
              <option value="{$models[$i]}" selected></option>
            </select>
          </td>
        <td>
          <a class="shortcutI3" href="#">I3</a>
        </td>
        </tr>
      {/foreach}
    </table>
    <a id="addRow" href="#">adaugă o linie</a>
    <br><br>

    <input id="capitalize" type="checkbox" name="capitalize" value="1" {if $capitalize}checked{/if}>
    <label for="capitalize">scrie cu majusculă lexemele I3 și SP*</label>
    <br>
    <input id="deleteOrphans" type="checkbox" name="deleteOrphans" value="1" {if $deleteOrphans}checked{/if}>
    <label for="deleteOrphans">șterge lexemele care devin neasociate</label>
    <br><br>

    <input type="submit" name="butPrev" value="« anterioara">
    <input id="butTest" type="submit" name="butTest" value="testează">
    <input id="butSave" type="submit" name="butSave" value="salvează" {if !$passedTests}disabled{/if}>
    <input type="submit" name="butNext" value="următoarea »">
  </form>

  <h3>Note</h3>

  <ul>
    <li>
      Legăturile de pe coloana „scurtături” sunt echivalente cu selectarea
      modelului respectiv. Sunt doar scurtături mai comode.
    </li>
    <li>
      Din această pagină nu puteți adăuga restricții la modelele de flexiune.
    </li>
    <li>
      Transcrierea cu majusculă nu apare la testare, numai la salvare.
    </li>
  </ul>
{/block}
