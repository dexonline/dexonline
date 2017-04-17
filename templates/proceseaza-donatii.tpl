{extends "layout-admin.tpl"}

{block "title"}
  Procesează donații
{/block}

{block "content"}
  {$includeOtrs=$includeOtrs|default:1}
  <h3>Procesează donații</h3>

  <form class="form" method="post">

    <div class="panel panel-default">
      <div class="panel-heading">Donații OTRS</div>
      <div class="panel-body">
        <div class="checkbox">
          <label>
            <input type="checkbox"
                   name="includeOtrs"
                   value="1"
                   {if $includeOtrs}checked{/if}>
            preia tichetele din OTRS
          </label>
        </div>
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Donații introduse manual</div>
      <div class="panel-body">
        {section rowLoop start=0 loop=5}
          {$i=$smarty.section.rowLoop.index}
          {$donor=$manualDonors[$i]|default:null}
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
                       min="0"
                       step="1"
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
    </div>

  </form>
{/block}
