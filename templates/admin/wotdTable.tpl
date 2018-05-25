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

  <div class="panel panel-default">
    <div class="panel-heading">
      Legături
    </div>

    <ul class="list-group">

      <li class="list-group-item">
        asistent CZ:
        <ul class="list-inline">
          {foreach $assistantDates as $timestamp}
            <li>
              <a href="wotdAssistant.php?for={$timestamp|date_format:"%Y-%m"}">
                {$timestamp|date_format:"%B %Y"}
              </a>
            </li>
          {/foreach}
        </ul>
      </li>

      <li class="list-group-item">
        <a href="wotdImages.php">imagini pentru cuvântul zilei</a>
      </li>

      <li class="list-group-item">
        <a href="https://wiki.dexonline.ro/wiki/Imagini_pentru_cuv%C3%A2ntul_zilei">instrucțiuni</a>
      </li>

    </ul>
  </div>

{/block}
