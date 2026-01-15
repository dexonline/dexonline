{if count($sourceTypes) > 1 && count($results) > 10}
  <ul class="d-inline list-inline ms-2">
    {foreach $sourceTypes as $st}
      <li class="list-inline-item">
        <a class="cat-link" href="#cat-{$st->id}">
          {SourceType::getShortName($st->id)}
        </a>
        <span class="text-muted">({$st->count})</span>
      </li>
    {/foreach}
  </ul>
{/if}
