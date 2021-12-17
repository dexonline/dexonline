{extends "layout-admin.tpl"}

{block "title"}Cuvântul zilei{/block}

{block "content"}

  <h3>Cuvântul zilei</h3>

  <div id="wotd-grid"></div>

  <button
    class="btn btn-secondary btn-sm float-start"
    id="add-button"
    type="button">
    {include "bits/icon.tpl" i=add}
    adaugă
  </button>

  <div class="card my-3">
    <div class="card-header">Legături</div>

    <div class="card-body">

      <ul>
        <li>
          asistent CZ:
          {foreach $assistantDates as $timestamp}
            <a
              class="ms-3"
              href="{Router::link('wotd/assistant')}?for={$timestamp|date_format:"%Y-%m"}">
              {$timestamp|date_format:"%B %Y"}
            </a>
          {/foreach}
        </li>

        <li>
          <a href="{Router::link('wotd/images')}">imagini pentru cuvântul zilei</a>
        </li>

        <li>
          <a href="https://wiki.dexonline.ro/wiki/Imagini_pentru_cuv%C3%A2ntul_zilei">instrucțiuni</a>
        </li>

      </ul>
    </div>
  </div>

  <div id="edit-modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Editează</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form>
            <div class="row mb-3">
              <label for="edit-definitionId" class="col-3 col-form-label">
                definiție
              </label>
              <div class="col-9">
                <select id="edit-definitionId" name="definitionId"></select>
              </div>
            </div>

            <div class="row mb-3">
              <label for="edit-displayDate" class="col-3 col-form-label">
                data afișării
              </label>
              <div class="col-9">
                <input class="form-control" id="edit-displayDate" name="displayDate" type="text">
                <span class="form-text">
                  Format: AAAA-LL-ZZ. Anul poate fi 0000.
                </span>
              </div>
            </div>

            <div class="row mb-3">
              <label for="edit-priority" class="col-3 col-form-label">
                prioritate
              </label>
              <div class="col-9">
                <input
                  class="form-control"
                  id="edit-priority"
                  max="10"
                  min="0"
                  name="priority"
                  type="number">
                <span class="form-text">
                  Incrementați cu 1 dacă aveți obiecții despre această definiție.
                </span>
              </div>
            </div>

            <div class="row mb-3">
              <label for="edit-image" class="col-3 col-form-label">
                imagine
              </label>
              <div class="col-9">
                <select id="edit-image" name="image">
                  {foreach $imageList as $image}
                    <option value="{$image}">{$image}</option>
                  {/foreach}
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label for="edit-description" class="col-3 col-form-label">
                motiv
              </label>
              <div class="col-9">
                <textarea
                  class="form-control"
                  id="edit-description"
                  name="description"
                  rows="8">
                </textarea>
              </div>
            </div>

          </form>
        </div>

        <div class="modal-footer">
          <button class="btn btn-link" data-bs-dismiss="modal" type="button">
            renunță
          </button>
          <button class="btn btn-danger" id="delete-btn" type="button">
            {include "bits/icon.tpl" i=delete}
            șterge
          </button>
          <button class="btn btn-primary" id="save-btn" type="button">
            {include "bits/icon.tpl" i=save}
            salvează
          </button>
        </div>

      </div>
    </div>
  </div>

{/block}
