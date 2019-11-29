<div class="panel-body">
  <form
    class="form-horizontal"
    action="{$bulkReplaceLink}"
    method="post">

    <div class="row">

      <div class="col-md-6">

        <div class="form-group">
          <label class="control-label col-xs-3">înlocuiește</label>
          <div class="col-xs-9">
            <input class="form-control" type="text" name="search">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-xs-3">cu</label>
          <div class="col-xs-9">
            <input class="form-control" type="text" name="replace">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-xs-3">în</label>
          <div class="col-xs-9">
            <select class="form-control" name="target">
              <option value="1">definiții</option>
              <option value="2">sensuri</option>
            </select>
          </div>
        </div>
      </div>

      <div class="col-md-6">

        <div class="form-group">
          <label class="control-label col-xs-3">sursa</label>
          <div class="col-xs-9">
            {include "bits/sourceDropdown.tpl" id="sourceDropdownBulk"}
            <small class="text-muted">
              se aplică numai definițiilor
            </small>
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-xs-3">rezultate</label>
          <div class="col-xs-9">
            <div class="input-group spinner">
              <input type="numeric"
                name="limit"
                class="form-control"
                value="1000"
                min="100"
                max="1000"
                step="100"
                tabindex="-1">
              <div class="input-group-btn-vertical">
                <button class="btn btn-default" type="button" tabindex="-1">
                  <i class="glyphicon glyphicon-chevron-up"></i>
                </button>
                <button class="btn btn-default" type="button" tabindex="-1">
                  <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
              </div>
            </div>
            <p class="help-block">Min 100 - Max 1000.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">

      <div class="col-md-6">

        <div class="form-group">
          <div class="col-xs-9 col-xs-offset-3">
            <button type="submit" class="btn btn-primary" name="previewButton">
              previzualizează
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>

  <p class="text-muted">
    Folosiți cu precauție această unealtă. Ea înlocuiește primul text cu al
    doilea în toate definițiile, incluzând notele de subsol, făcând diferența între litere mari și mici
    (case-sensitive) și fără expresii regulate (textul este căutat ca
    atare). Vor fi modificate maximum 1.000 de definiții. Veți putea vedea
    lista de modificări propuse și să o acceptați.
  </p>
  <p class="text-danger">
    Evitați pe cât posibil definițiile cu note de subsol și cele structurate, debifându-le.
  </p>
</div>
