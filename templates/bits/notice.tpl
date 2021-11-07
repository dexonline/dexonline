{* Do not include this explicitly. Use the {notice} tag. *}

{$type=$type|default:''}   {* optionally, one of info/success/warning/danger *}
{$class=$class|default:''} {* optionally, additional classes *}

<div class="notice d-flex align-items-center small w-75 mx-auto {$class}">
  {if $type}
    {if $type == 'info'}
      {$color='text-body'}
      {$icon='info'}
    {elseif $type == 'success'}
      {$color='text-success'}
      {$icon='done'}
    {elseif $type == 'warning'}
      {$color='text-warning'}
      {$icon='warning'}
    {else} {* danger *}
      {$color='text-danger'}
      {$icon='error'}
    {/if}

    <div class="notice-icon d-flex align-items-center me-3 {$color}">
      {include "bits/icon.tpl" i=$icon}
    </div>
  {/if}

  <div class="notice-body">
    {$contents}
  </div>

</div>
