{$link=$link|default:false}
{$colors=$colors|default:true}

{if $t->public || User::can(User::PRIV_ANY)}

  {strip}
  <span class="tag {if $t->tooltip}tag-tooltip{/if}" title="{$t->tooltip}">
    <a
      href="{Router::link('tag/edit')}?id={$t->id}"
      class="badge {if !$link}disabled{/if}"
      {if !$link} disabled tabindex="-1"{/if}
      {if $colors}{$t->getCssStyle()}{/if}>
      {if $t->icon}
        {include "bits/icon.tpl" i=$t->icon}
        {if !$t->iconOnly}&nbsp;{/if}
      {/if}
      {if !$t->iconOnly}
        {$t->value}
      {/if}
    </a>
  </span>
  {/strip}
{/if}
