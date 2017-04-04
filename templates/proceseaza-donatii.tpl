{extends "layout-admin.tpl"}

{block "title"}
  Procesează donații
{/block}

{block "content"}
  <h3>Procesează donații</h3>

  <form class="form" method="post">

    <div class="panel panel-default">
      <div class="panel-heading">Donații OTRS</div>
      <div class="panel-body">
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Donații introduse manual</div>
      <div class="panel-body">
        {section rowLoop start=0 loop=5}
          {$donor=$manualDonors[$smarty.section.rowLoop.index]|default:null}
          <div>
            <div class="row form-group">
              <div class="col-md-4">
                <input type="email"
                       name="email[]"
                       value="{$donor->email|default:''}""
                       class="form-control"
                       placeholder="email">
              </div>
              <div class="col-md-4">
                <input type="number"
                       name="amount[]"
                       value="{$donor->amount|default:''}""
                       class="form-control"
                       placeholder="suma">
              </div>
              <div class="col-md-4">
                <input type="date"
                       name="date[]"
                       value="{$donor->date|default:''}""
                       class="form-control"
                       placeholder="data">
              </div>
            </div>
          </div>
        {/section}
      </div>
    </div>

    <div>

      <button type="submit" class="btn btn-default" name="previewButton">
        previzualizează
      </button>

      <button type="submit" class="btn btn-success" name="saveButton">
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>

    </div>

  </form>
{/block}
