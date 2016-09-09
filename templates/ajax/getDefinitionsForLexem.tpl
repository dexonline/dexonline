{foreach $results as $row}
  <div class="defWrapper">
    <p class="def">{$row.htmlRep}</p>
    <p class="defDetails text-muted">
      id: {$row.id} | sursa: {$row.shortName} | starea: {$row.status}
    </p>
  </div>
{/foreach}
