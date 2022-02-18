{if count($sourceTypes) >= 2}
  <ul class="list-inline small ms-5">
    {foreach $sourceTypes as $st}
      <li class="list-inline-item">
        <a href="#cat-{$st->id}">
          {SourceType::getShortName($st->id)}
        </a>
        ({$st->count})
      </li>
    {/foreach}
  </ul>
{/if}
