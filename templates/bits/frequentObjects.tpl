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
    class="btn-toolbar {$pull} voffset frequentObjects"
    data-name="{$name}"
    data-type="{$type}"
    data-target="{$target}"
    data-focus-target="{$focusTarget}">

    <div class="btn-group btn-group-xs frequentObjectAddDiv">
      <button type="button"
        class="btn btn-default"
        data-toggle="modal"
        data-target="#frequentObjectModal"
        title="adaugă o valoare folosită frecvent">
        <i class="glyphicon glyphicon-plus"></i>
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
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Adaugă o valoare...</h4>
        </div>

        <div class="modal-body">
          <select id="addObjectId">
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="frequentObjectAdd">
            <i class="glyphicon glyphicon-plus"></i>
            adaugă
          </button>
          <button type="button" class="btn btn-link" data-dismiss="modal">renunță</button>
        </div>
      </div>
    </div>
  </div>

  <div id="frequentObjectsTrash">
    <button class="btn btn-lg btn-danger">
      <i class="glyphicon glyphicon-trash"></i>
    </button>
  </div>

  {* stem object (to be cloned for additions) *}

  <div id="frequentObjectStem" class="btn-group btn-group-xs">
    <button class="btn btn-default frequentObject" type="button">
    </button>
  </div>
{/if}
