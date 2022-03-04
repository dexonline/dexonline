{if count($sources)}
  <span class="meaning-sources ms-1">
    <a href="#" title="{t}show sources{/t}">
      {include "bits/icon.tpl" i=book}
    </a>
    <span class="tag-group">
      {foreach $sources as $s}
        <span class="badge badge-source" title="{$s->name}, {$s->year}">
          {$s->shortName}
        </span>
      {/foreach}
    </span>
  </span>
{/if}
