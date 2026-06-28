{extends "layout-admin.tpl"}

{block "title"}Informații despre definițiile OCR{/block}

{block "content"}
  <h3>Informații despre definițiile OCR</h3>

  {if $message}
    {notice type=$msgType}
    {$message}
    {/notice}
  {/if}

  <div class="card mb-3">
    <div class="card-header">
      Împrospătează statisticile (ultima actualizare la {$statsTS})
    </div>

    <div class="card-body">
      <form>
        <button type="submit" class="btn btn-primary" name="refreshStatsButton">
          regenerează statisticile
        </button>
      </form>
    </div>
  </div>

  {* Am unificat blocul stats cu blocul content *}
  <h4 class="mt-4">Alocarea definițiilor OCR</h4>

  <table id="alocare" class="table table-sm table-hover">
    <thead>
    <tr>
      <th>Moderator</th>
      <th>Definiții finalizate</th>
      <th>Dicționar în lucru</th>
      <th>Definiții alocate</th>
      <th>Caractere alocate</th>
      <th>Ultima acțiune la</th>
    </tr>
    <tr class="filters">
      <th></th>
      <th></th>
      <th></th>
      <th>
        <select id="filter-alocare" class="form-select form-select-sm">
            <option value="">toate</option>
            <option value="1" selected>în lucru</option>
            <option value="0">fără alocare</option>
        </select>
      </th>
      <th></th>
      <th></th>
    </tr>
    </thead>

    <tbody>
    {foreach $statsEditors as $i}
      <tr>
        <td>{$i.0}</td>
        <td>{$i.1}</td>
        <td>{$i.5}</td>
        <td>{$i.2}</td>
        <td>{$i.4}</td>
        <td><time datetime="{$i.6}" title="{$i.6}">{$i.6|truncate:10:""}</time></td>
      </tr>
    {/foreach}
    </tbody>

  </table>

  {* if empty($statsStudents) *}
  <h4 class="mt-4">Studenți</h4>

  <table id="practica" class="table table-sm table-hover">

    <thead>
    <tr>
      <th>Student</th>
      <th>Stare</th>
      <th>Ultima acțiune la</th>
      <th>An</th>
      <th>Definiții finalizate</th>
      <th>Dicționar în lucru</th>
      <th>Definiții alocate</th>
      <th>Caractere alocate</th>
    </tr>

    <tr class="filters">
      <th></th>
      <th>
        <select id="filter-stare" class="form-select form-select-sm">
          <option value="">toate</option>
          <option value="activ" selected>activ</option>
          <option value="inactiv">inactiv</option>
        </select>
      </th>
      <th></th>
      <th>
        <select id="filter-an" class="form-select form-select-sm">
          <option value="">toți</option>
          <option value="2026" selected>2026</option>
          <option value="2025">2025</option>
        </select>
      </th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
    </tr>
    </thead>

    <tbody>
    {foreach $statsStudents as $i}
      <tr>
        <td>{$i.0}</td>
        <td>{$i.7}</td>
        <td><time datetime="{$i.8}" title="{$i.8}">{$i.8|truncate:10:""}</time></td>
        <td>{$i.6}</td>
        <td>{$i.1}</td>
        <td>{$i.5}</td>
        <td>{$i.2}</td>
        <td>{$i.4}</td>
      </tr>
    {/foreach}
    </tbody>

  </table>
  {* /if *}


  <h4 class="mt-4">Dicționare prelucrate OCR</h4>

  <table id="preparare" class="table table-sm table-hover">

    <thead>
    <tr>
      <th>Preparator</th>
      <th>Dicționar</th>
      <th>Definiții preparate</th>
      <th>Definiții în lucru</th>
      <th>Nr. caractere preparate</th>
      <th>Nr. caractere în lucru</th>
    </tr>
    <tr class="filters">
      <th></th>
      <th></th>
      <th></th>
      <th>
        <select id="filter-preparare" class="form-select form-select-sm">
          <option value="">toate</option>
          <option value="1" selected>în lucru</option>
          <option value="0">fără alocare</option>
        </select>
      </th>
      <th></th>
      <th></th>
    </thead>

    <tbody>
    {foreach $statsPrep as $i}
      <tr>
        <td>{$i.0}</td>
        <td>{$i.1}</td>
        <td>{$i.2}</td>
        <td>{$i.3}</td>
        <td>{$i.4}</td>
        <td>{$i.5}</td>
      </tr>
    {/foreach}
    </tbody>

  </table>

  <script>
    function filtreaza1() {
      const alocare = $("#filter-alocare").val();

      $("#alocare tbody tr").each(function () {
        const alocareRow = parseInt($("td:eq(3)", this).text(), 10) || 0;

        let okAlocare = true;
        if (alocare === "1")
          okAlocare = alocareRow > 0;
        if (alocare === "0")
          okAlocare = alocareRow === 0;

        $(this).toggle(okAlocare);
      });
    }

    function filtreaza2() {
      const stare = $("#filter-stare").val().toLowerCase();
      const an = $("#filter-an").val();

      $("#practica tbody tr").each(function () {
        const stareRow = $("td:eq(1)", this).text().trim().toLowerCase();
        const anRow = $("td:eq(3)", this).text().trim();

        const okStare = !stare || stareRow === stare;
        const okAn = !an || anRow === an;

        $(this).toggle(okStare && okAn);
      });
    }

    function filtreaza3() {
      const preparare = $("#filter-preparare").val();

      $("#preparare tbody tr").each(function () {
        const preparareRow = parseInt($("td:eq(3)", this).text(), 10) || 0;

        let okPreparare = true;
        if (preparare === "1")
          okPreparare = preparareRow > 0;
        if (preparare === "0")
          okPreparare = preparareRow === 0;

        $(this).toggle(okPreparare);
      });
    }

    $("#filter-alocare").on("change", filtreaza1);
    $("#filter-stare, #filter-an").on("change", filtreaza2);
    $("#filter-preparare").on("change", filtreaza3);

    // aplicare implicită la load
    $(document).ready(function () {
      filtreaza1();
      filtreaza2();
      filtreaza3();
    });
  </script>

{/block}
