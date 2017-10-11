{**
 * Arguments:
 * name: a unique name for cookie saving/loading
 * $list: list of suggestions;
 * $text: text (display value) field;
 * $target: target select2 element
 **}
<div class="clearfix">
  <div class="btn-toolbar pull-right frequentObjects"
       data-target="{$target}"
       data-name="{$name}">

    <div class="btn-group btn-group-xs frequentObjectAddDiv">
      <button type="button"
              class="btn btn-default"
              data-toggle="modal"
              data-target="#frequentObjectModal"
              title="adaugă un obiect favorit">
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
    <button type="button" class="btn btn-default frequentObjectDelete">
      <i class="glyphicon glyphicon-trash"></i>
    </button>
  </div>
{/if}
