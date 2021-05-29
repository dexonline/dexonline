{extends "layout-admin.tpl"}

{block "title"}Adaugă abrevieri pentru dicționar{/block}


{block "content"}
  <h3>Adaugă abrevieri pentru dicționar</h3>

  <form class="form-horizontal" method="post" enctype="multipart/form-data">

    {* No abbreviations loaded yet -- show file selector and legend *}
    {if empty($abbrevs)}
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
            <strong>enforced|ambiguous|caseSensitive|short|internalRep</strong>,
            iar pe celelate rânduri cinci câmpuri delimitate, conform
            explicațiilor:
          </p>

          <dl class="row">
            <dt class="col-xl-3">abreviere impusă</dt>
            <dd class="col-xl-9">
              nu ia în considerare forma editată și impune forma din câmpul
              short - <i>valoare booleană 0/1</i>
            </dd>

            <dt class="col-xl-3">abreviere ambiguă</dt>
            <dd class="col-xl-9">
              <i>valoare booleană 0/1</i>
            </dd>

            <dt class="col-xl-3">diferențiere majuscule-minuscule</dt>
            <dd class="col-xl-9">
              <i>valoare booleană 0/1</i>
            </dd>

            <dt class="col-xl-3">abrevierea</dt>
            <dd class="col-xl-9">
              permite și alte semne de punctuație (nu doar <kbd>.</kbd>) și formatare internă
              <kbd>$</kbd>, <kbd>@</kbd>, <kbd>%</kbd>, <kbd>_{}</kbd>, <kbd>^{}</kbd>
            </dd>

            <dt class="col-xl-3">detalierea abrevierii</dt>
            <dd class="col-xl-9">
              permite formatare internă <kbd>$</kbd>, <kbd>@</kbd>, <kbd>%</kbd>,
              <kbd>_{}</kbd>, <kbd>^{}</kbd>
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
    {if !empty($abbrevs)}
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

        <table class="table mb-0">
          <thead>
            <tr>
              <th>Nr.</th>
              <th>Imp.</th>
              <th>Amb.</th>
              <th>CS</th>
              <th>Abreviere</th>
              <th>Detalierea abrevierii</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$abbrevs key=k item=a}
              <tr>
                <td><span class="badge bg-secondary">{$k+1}</span></td>
                <td>
                  {if $a->enforced}
                    {include "bits/icon.tpl" i=done}
                  {/if}
                </td>
                <td>
                  {if $a->ambiguous}
                    {include "bits/icon.tpl" i=done}
                  {/if}
                </td>
                <td>
                  {if $a->caseSensitive}
                    {include "bits/icon.tpl" i=done}
                  {/if}
                </td>
                <td>{$a->short}</td>
                <td>{HtmlConverter::convert($a)}</td>
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
