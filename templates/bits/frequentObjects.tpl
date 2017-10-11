{**
 * Arguments:
 * $name: a unique name for cookie saving/loading
 * $type: what data to autocomplete (tags, sources etc.)
 * $target: target select2 element
 **}
<div class="clearfix">
  <div class="btn-toolbar pull-right frequentObjects"
       data-name="{$name}"
       data-type="{$type}"
       data-target="{$target}">

    <div class="btn-group btn-group-xs frequentObjectAddDiv">
      <button type="button"
              class="btn btn-default"
              data-toggle="modal"
              data-target="#frequentObjectModal"
              title="adaugă un obiect folosit frecvent">
        <i class="glyphicon glyphicon-plus"></i>
      </button>
    </div>
  </div>
</div>

{* one-time only components *}
{if !$FREQUENT_OBJECT_ONCE}
  {$FREQUENT_OBJECT_ONCE=1 scope="global"}

  {* Bootstrap modal *}
  <div class="modal fade" id="frequentObjectModal" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Adaugă un obiect...</h4>
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

  {* stem object (to be cloned for additions) *}

  <div id="frequentObjectStem" class="btn-group btn-group-xs">
    <button class="btn btn-default frequentObject" type="button">
    </button>
    <button type="button"
            class="btn btn-default frequentObjectDelete"
            title="șterge obiectul din listă">
      <i class="glyphicon glyphicon-trash"></i>
    </button>
  </div>
{/if}
