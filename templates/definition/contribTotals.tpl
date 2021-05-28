{extends "layout-admin.tpl"}

{block "title"}
  Contorizare contribuții
{/block}

{block "content"}
  <h3>Contorizare contribuții</h3>

  <form method="post">

    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">utilizator</label>
      <div class="col-sm-10">
        <select
          name="userId"
          class="form-select select2Users {if isset($errors.userId)}is-invalid{/if}">
          <option value="{$userId|default:''}" selected></option>
        </select>
        {include "bits/fieldErrors.tpl" errors=$errors.userId|default:null}
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">dată de început</label>
      <div class="col-sm-10">
        <input
          type="text"
          id="startDate"
          name="startDate"
          value="{$startDate|default:''}"
          class="form-control {if isset($errors.startDate)}is-invalid{/if}""
          placeholder="AAAA-LL-ZZ">
        {include "bits/fieldErrors.tpl" errors=$errors.startDate|default:null}
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">dată de sfârșit</label>
      <div class="col-sm-10">
        <input
          type="text"
          id="endDate"
          name="endDate"
          value="{$endDate|default:''}"
          class="form-control {if isset($errors.endDate)}is-invalid{/if}"
          placeholder="AAAA-LL-ZZ">
        {include "bits/fieldErrors.tpl" errors=$errors.endDate|default:null}
      </div>
    </div>

    <div class="row mb-3">
      <div class="offset-sm-2 col-sm-10">

        <div class="form-check">
          <label class="form-check-label">
            <input
              type="checkbox"
              class="form-check-input"
              name="showChanges"
              {if $showChanges}checked{/if}
              value="1">
            contorizează corecturile față de originalul OCR
          </label>
        </div>
        <span class="form-text">
          poate fi lent și/sau poate da eroare pentru volume mari de date
        </span>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-sm-offset-2 col-sm-10">
        <button class="btn btn-primary" type="submit" name="submitButton">
          raportează
        </button>
      </div>
    </div>

  </form>

  {if isset($results)}
    <div class="card mb-3">
      <div class="card-header">Rezultate</div>

      <table class="table mb-0">

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
              <td>{$row->length|nf}</td>
              <td>{$changes[$row->id]|default:0|nf}</td>
            </tr>
          {/foreach}
        </tbody>

        <tfoot>
          <tr>
            <th>total</th>
            <th>{$sumLength|nf}</th>
            <th>{$sumChanges|default:0|nf}</th>
          </tr>
        </tfoot>

      </table>
    </div>
  {/if}

{/block}
