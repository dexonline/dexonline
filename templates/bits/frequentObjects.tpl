{**
 * Arguments:
 * $list: list of suggestions;
 * $id: ID field;
 * $text: text (display value) field;
 * $classes: CSS classes;
 * $disabled: whether the field starts disabled;
 **}
{$id=$id|default:'id'}
{$disabled=$disabled|default:false}
<div class="pull-right frequentObjects">

  <div class="btn-group frequentObjectStem">
    <button class="btn btn-default btn-xs {$classes}"
            type="button"
            data-id="{$i->$id}"
            data-text="{$i->$text}"
            {if $disabled}disabled{/if}>
      {$i->$text}
    </button>
    <button type="button" class="btn btn-default btn-xs frequentObjectDelete">
      <i class="glyphicon glyphicon-trash"></i>&nbsp;
    </button>
  </div>

  {foreach $list as $i}
    <div class="btn-group">
      <button class="btn btn-default btn-xs {$classes}"
              type="button"
              data-id="{$i->$id}"
              data-text="{$i->$text}"
              {if $disabled}disabled{/if}>
        {$i->$text}
      </button>
      <button type="button" class="btn btn-default btn-xs frequentObjectDelete">
        <i class="glyphicon glyphicon-trash"></i>&nbsp;
      </button>
    </div>
  {/foreach}
</div>
