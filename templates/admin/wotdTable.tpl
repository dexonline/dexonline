{extends "layout-admin.tpl"}

{block "title"}Cuvântul zilei{/block}

{block "content"}

  <h3>Cuvântul zilei</h3>

  <table id="wotdGrid" class="table"></table>
  <div id="wotdPaging"></div>

  <select id="imageList">
    {foreach $imageList as $image}
      <option value="{$image}">{$image}</option>
    {/foreach}
  </select>

  <div class="voffset3"></div>

  <div class="panel panel-default quickNav">
    <div class="panel-heading">
      Pagini asociate
    </div>

    <ul class="list-group">
      <li class="list-group-item">
        <a href="wotdImages.php">imagini pentru cuvântul zilei</a>
      </li>

      <li class="list-group-item">
        <a href="http://wiki.dexonline.ro/wiki/Imagini_pentru_cuv%C3%A2ntul_zilei">instrucțiuni</a>
      </li>

    </ul>
  </div>

  <div class="panel panel-default quickNav">
    <div class="panel-heading">
      Descarcă lista de cuvinte
    </div>

    <div class="panel-body">
      <form class="form-inline" action="wotdExport.php">
        <div class="form-group">
          <label>luna</label>
          {include file="bits/numericDropDown.tpl" name="month" start=1 end=13 selected=$downloadMonth}
        </div>

        <div class="form-group">
          <label>anul</label>
          {include file="bits/numericDropDown.tpl" name="year" start=$downloadYear-3 end=$downloadYear+3 selected=$downloadYear}
        </div>

        <button type="submit" class="btn btn-primary" name="submitButton">
          <i class="glyphicon glyphicon-download-alt"></i>
          descarcă
        </button>
  </form>
{/block}
