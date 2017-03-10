{extends "layout-admin.tpl"}

{block "title"}Verificarea acurateței{/block}

{block "content"}
  <h3>Verificarea acurateței</h3>

  <div class="panel panel-default">
    <div class="panel-heading">Proiectele mele</div>

    <div class="panel-body">
      <div class="checkbox">
        <label>
          <input type="checkbox"
                 id="includePublic"
                 {if $includePublic}checked{/if}
                 value="1">
          include proiectele publice ale altor moderatori
        </label>
      </div>
    </div>

    <table id="projectTable" class="table">

      <thead>
        <tr>
          <th>nume</th>
          <th>autor proiect</th>
          <th>editor</th>
          <th>sursă</th>
          <th>definiții</th>
          <th>erori/KB</th>
          <th>car/oră</th>
        </tr>
      </thead>

      <tbody>
        {foreach $projects as $proj}
          <tr>
            <td><a href="acuratete-eval?projectId={$proj->id}">{$proj->name}</a></td>
            <td>{$proj->getOwner()}</td>
            <td>{$proj->getUser()}</td>
            <td>{$proj->getSource()->shortName|default:'&mdash;'}</td>
            <td>{$proj->defCount|number_format:0:',':'.'}</td>
            <td>{$proj->errorRate|string_format:"%.2f"}</td>
            <td>{$proj->getSpeed()|number_format:0:',':'.'}</td>
          </tr>
        {/foreach}
      </tbody>

      {include "bits/pager.tpl" id="projectPager" colspan="7"}
    </table>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Creează un proiect nou</div>
    <div class="panel-body">

      <form class="form-horizontal" method="post">

        <div class="form-group">
          <label for="f_name" class="col-sm-3 control-label">nume</label>
          <div class="col-sm-9">
            <input type="text" id="f_name" class="form-control" name="name" value="{$p->name}" />
          </div>
        </div>

        <div class="form-group">
          <label for="userId" class="col-sm-3 control-label">utilizator</label>
          <div class="col-sm-9">
            <select id="userId" name="userId" class="form-control">
              <option value="{$p->userId}" selected></option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="sourceDropDown" class="col-sm-3 control-label">sursă (opțional)</label>
          <div class="col-sm-9">
            {include "bits/sourceDropDown.tpl" name="sourceId" sourceId=$p->sourceId}
          </div>
        </div>

        <div class="form-group">
          <label for="f_startDate" class="col-sm-3 control-label">dată de început (opțional)</label>
          <div class="col-sm-9">
            <input type="text" id="f_startDate" name="startDate" value="{$p->startDate}" class="form-control" placeholder="AAAA-LL-ZZ" />
          </div>
        </div>

        <div class="form-group">
          <label for="f_endDate" class="col-sm-3 control-label">dată de sfârșit (opțional)</label>
          <div class="col-sm-9">
            <input type="text" id="f_endDate" name="endDate" value="{$p->endDate}" placeholder="AAAA-LL-ZZ" class="form-control" />
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label">metodă</label>
          <div class="col-sm-9">
            {include "bits/dropdown.tpl"
            name="method"
            data=AccuracyProject::getMethodNames()
            selected=$p->method}
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label">vizibilitate</label>
          <div class="col-sm-9">
            {include "bits/dropdown.tpl"
            name="visibility"
            data=AccuracyProject::$VIS_NAMES
            selected=$p->visibility}
          </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-9">
            <button class="btn btn-primary" type="submit" name="submitButton">
              creează
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
{/block}
