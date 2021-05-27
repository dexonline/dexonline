{$link=$link|default:false}
{$colors=$colors|default:true}

{if $t->public || User::can(User::PRIV_ANY)}

  {$style="color: {$t->getColor()}; background: {$t->getBackground()};"}

  {strip}
  <span {if $t->tooltip}class="tag-tooltip"{/if} title="{$t->tooltip}">
    <a
      href="{Router::link('tag/edit')}?id={$t->id}"
      class="badge bg-secondary {if !$link}disabled{/if}"
      {if !$link} disabled tabindex="-1"{/if}
      {if $colors} style="{$style}"{/if}>
      {if $t->icon}
        {include "bits/icon.tpl" i=$t->icon}
      {/if}
      {if $t->icon && !$t->iconOnly}
        &nbsp;
      {/if}
      {if !$t->iconOnly}
        {$t->value}
      {/if}
    </a>
  </span>
  {/strip}
{/if}
