{extends "layout-admin.tpl"}

{block "title"}Adaugă indecși de pagină pentru dicționar{/block}


{block "content"}
  <h3>Adaugă indecși de pagină pentru dicționar</h3>

  <form method="post" enctype="multipart/form-data">

    {* No page index loaded yet -- show file selector and legend *}
    {if empty($indexes)}
      <div class="card mb-3">

        <div class="card-header">
          Selectare fișier
        </div>

        <div class="card-body pb-0">

          <div class="row mb-3">
            <label class="col-md-1 col-form-label">fișier</label>
            <div class="col-md-6">
              <input class="form-control" type="file" name="file">
            </div>
            <label class="col-md-2 col-form-label">delimitator</label>
            <div class="col-md-2">
              <input class="form-control"
                     type="text"
                     name="delimiter"
                     placeholder="implicit |">
            </div>
            <span class="offset-md-1 col-md-8 text-danger">
              Important! Asigurați-vă că fișierul este codificat ASCII sau UTF-8.
            </span>
          </div>

          <p>
            Fișierul sursă trebuie să aibă pe primul rând capul de tabel
            <strong>volume|page|word|number</strong>, iar pe celelate rânduri
            patru câmpuri delimitate, conform explicațiilor:
          </p>

          <dl class="row">
            <dt class="col-md-2">volum</dt>
            <dd class="col-md-10">
              întreg (pozitiv), valoarea implicită 1
            </dd>

            <dt class="col-md-2">pagină</dt>
            <dd class="col-md-10">
              întreg (pozitiv)
            </dd>

            <dt class="col-md-2">intrare</dt>
            <dd class="col-md-10">
              șir de caractere
            </dd>

            <dt class="col-md-2">număr</dt>
            <dd class="col-md-10">
              întreg (pozitiv), valoarea implicită 0
            </dd>
          </dl>
        </div>
      </div>

      <div>
        <button class="btn btn-primary" type="submit" name="submit">
          previzualizează
        </button>
      </div>
    {/if}

    {* Abbreviations loaded -- show source dropdown and preview *}
    {if !empty($indexes)}
      <div class="card mb-3">

        <div class="card-header">
          Alegere sursă
        </div>

        <div class="card-body">
          <div class="row">
            <label class="col-lg-1 form-label">sursa</label>
            <div class="col-lg-11">
              {include "bits/sourceDropDown.tpl" skipAnySource=true}
            </div>
          </div>
        </div>

      </div>

      <div class="card mb-3">
        <div class="card-header">
          {include "bits/icon.tpl" i=person}
          {$modUser}
        </div>

        <table class="table mb-0 ">
          <thead>
            <tr>
              <th>Nr.</th>
              <th>Vol.</th>
              <th>Pag.</th>
              <th>Intrare</th>
              <th>Nr.cuv.</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$indexes key=k item=pi}
              <tr>
                <td><span class="badge bg-secondary">{$k+1}</span></td>
                <td>{$pi->volume}</td>
                <td>{$pi->page}</td>
                <td>{$pi->word}</td>
                <td>{$pi->number}</td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>

      <div>
        <button type="submit" class="btn btn-primary" name="saveButton">
          {include "bits/icon.tpl" i=save}
          <u>s</u>alvează
        </button>
        <button type="submit" class="btn btn-link" name="cancelButton">
          {include "bits/icon.tpl" i=clear}
          renunță
        </button>
      </div>
    {/if}
  </form>

{/block}
