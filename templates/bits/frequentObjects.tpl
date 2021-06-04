{**
  * Arguments:
  * $name: a unique name for cookie saving/loading
  * $type: what data to autocomplete (tags, sources etc.)
  * $target: target select2 element
  * $focusTarget: element to focus on object click; defaults to $target
  **}
{$focusTarget=$focusTarget|default:$target}
{$pull=$pull|default:'pull-right'}
<div class="clearfix">
  <div
    class="btn-toolbar {$pull} frequentObjects"
    data-name="{$name}"
    data-type="{$type}"
    data-target="{$target}"
    data-focus-target="{$focusTarget}">

    <div class="btn-group btn-group-sm frequentObjectAddDiv">
      <button type="button"
        class="btn btn-light"
        data-bs-toggle="modal"
        data-bs-target="#frequentObjectModal"
        title="adaugă o valoare folosită frecvent">
        {include "bits/icon.tpl" i=add}
      </button>
    </div>
  </div>
</div>

{* one-time only components *}
{if !isset($FREQUENT_OBJECT_ONCE)}
  {$FREQUENT_OBJECT_ONCE=1 scope="global"}

  {* Bootstrap modal *}

  <div class="modal fade" id="frequentObjectModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Adaugă o valoare...</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          </button>
        </div>

        <div class="modal-body">
          <select id="addObjectId">
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="frequentObjectAdd">
            {include "bits/icon.tpl" i=add}
            adaugă
          </button>
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">renunță</button>
        </div>
      </div>
    </div>
  </div>

  <div id="frequentObjectsTrash">
    <button class="btn btn-lg btn-danger">
      {include "bits/icon.tpl" i=delete}
    </button>
  </div>

  {* stem object (to be cloned for additions) *}

  <div id="frequentObjectStem" class="btn-group btn-group-sm">
    <button class="btn btn-light frequentObject" type="button">
    </button>
  </div>
{/if}
