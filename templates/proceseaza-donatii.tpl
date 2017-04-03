{extends "layout-admin.tpl"}

{block "title"}
  Procesează donații
{/block}

{block "content"}
  <h3>Procesează donații</h3>

  <form class="form-inline" method="post">

    <div class="panel panel-default">
      <div class="panel-heading">Donații OTRS</div>
      <div class="panel-body">
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Donații introduse manual</div>
      <div class="panel-body">
        {section rowLoop start=0 loop=5}
          <div>
            <div class="form-group">
              <input type="email" name="email[]" class="form-control" placeholder="email">
              <input type="number" name="amount[]" class="form-control" placeholder="suma">
              <input type="date" name="date[]" class="form-control" placeholder="data">
            </div>
          </div>
        {/section}
      </div>
    </div>
  </form>
{/block}
