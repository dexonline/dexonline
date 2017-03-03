{extends "layout-admin.tpl"}

{block "title"}
  Contorizare contribuții
{/block}

{block "content"}
  <h3>Contorizare contribuții</h3>

  {include "bits/phpConstants.tpl"}

  <form class="form-horizontal" method="post">

    <div class="form-group {if isset($errors.userId)}has-error{/if}">
      <label for="userId" class="col-sm-2 control-label">utilizator</label>
      <div class="col-sm-10">
        <select id="userId" name="userId" class="form-control">
          <option value="{$userId|default:''}" selected></option>
        </select>
        {include "bits/fieldErrors.tpl" errors=$errors.userId|default:null}
      </div>
    </div>

    <div class="form-group {if isset($errors.startDate)}has-error{/if}">
      <label for="startDate" class="col-sm-2 control-label">dată de început</label>
      <div class="col-sm-10">
        <input type="text"
               id="startDate"
               name="startDate"
               value="{$startDate|default:''}"
               class="form-control"
               placeholder="AAAA-LL-ZZ" />
        {include "bits/fieldErrors.tpl" errors=$errors.startDate|default:null}
      </div>
    </div>

    <div class="form-group {if isset($errors.endDate)}has-error{/if}">
      <label for="endDate" class="col-sm-2 control-label">dată de sfârșit</label>
      <div class="col-sm-10">
        <input type="text"
               id="endDate"
               name="endDate"
               value="{$endDate|default:''}"
               class="form-control"
               placeholder="AAAA-LL-ZZ" />
        {include "bits/fieldErrors.tpl" errors=$errors.endDate|default:null}
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <div class="checkbox">
          <label>
            <input type="checkbox"
                   name="showChanges"
                   {if $showChanges}checked{/if}
                   value="1">
            contorizează corecturile față de originalul OCR
          </label>
          <span class="text-muted">
            (poate fi lent și/sau poate da eroare pentru volume mari de date)
          </span>
        </div>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button class="btn btn-primary" type="submit" name="submitButton">
          raportează
        </button>
      </div>
    </div>

  </form>

  {if isset($results)}
    <div class="panel panel-default">
      <div class="panel-heading">Rezultate</div>

      <table class="table">

        <thead>
          <tr>
            <th>sursă</th>
            <th>caractere</th>
            <th>corecturi față de OCR</th>
          </tr>
        </thead>

        <tbody>
          {foreach $results as $row}
            <tr>
              <td>{$row->shortName}</td>
              <td>{$row->length|number_format:0:',':'.'}</td>
              <td>
                {$changes[$row->id]|default:0|number_format:0:',':'.'}
              </td>
            </tr>
          {/foreach}
        </tbody>

        <tfoot>
          <tr>
            <th>total</th>
            <th>{$sumLength|number_format:0:',':'.'}</th>
            <th>{$sumChanges|default:0|number_format:0:',':'.'}</th>
          </tr>
        </tfoot>

      </table>
    </div>
  {/if}

{/block}
