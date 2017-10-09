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
<div class="pull-right">
  {foreach $list as $i}
    <button class="btn btn-default btn-xs {$classes}"
            type="button"
            data-id="{$i->$id}"
            data-text="{$i->$text}"
            {if $disabled}disabled{/if}>
      {$i->$text}
    </button>
  {/foreach}
</div>
