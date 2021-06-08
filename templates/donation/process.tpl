{$includeOtrs=$includeOtrs|default:1}
{extends "layout-admin.tpl"}

{block "title"}
  Procesează donații
{/block}

{block "content"}
  <h3>Procesează donații</h3>

  <form class="form" method="post">

    <div class="card mb-3">
      <div class="card-header">Donații OTRS</div>
      <div class="card-body">
        {include "bs/checkbox.tpl"
          name=includeOtrs
          label='preia tichetele din OTRS'
          checked=$includeOtrs}
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Donații introduse manual</div>
      <div class="card-body">
        {section rowLoop start=0 loop=5}
          {$i=$smarty.section.rowLoop.index}
          {$donor=$manualDonors[$i]|default:null}
          <div class="row mb-2">
            <div class="col-12 col-md-4">
              <input
                type="email"
                name="email[]"
                value="{$donor->email|default:''}"
                class="form-control"
                placeholder="email">
            </div>
            <div class="col-12 col-md-4">
              <input
                type="number"
                min="0"
                step="1"
                name="amount[]"
                value="{$donor->amount|default:''}"
                class="form-control"
                placeholder="suma">
            </div>
            <div class="col-12 col-md-4">
              <input
                type="date"
                name="date[]"
                value="{$donor->date|default:''}"
                class="form-control"
                placeholder="data">
            </div>
          </div>
        {/section}
      </div>
    </div>

    <div>
      <button type="submit" class="btn btn-primary" name="previewButton">
        previzualizează
      </button>
    </div>

  </form>
{/block}
