{extends "layout.tpl"}

{block "title"}{$source->name|escape}{/block}

{block "content"}
  <h3>Sursă: {$source->name|escape}</h3>

  <h4>
    {$source->author|escape}
  </h4>

  <h5>
    {$source->getPublisherDetails()|escape}
  </h5>

  {if $source->defCount > 0}
    <p>
      {$source->ourDefCount|nf} din
      {$source->defCount|nf} definiții importate
      ({$source->percentComplete|nf:2}% complet)
    </p>
  {/if}

  {$authors=$source->getAuthorMap()}
  {foreach $authors as $rec}
    <h3 class="rolePriority{$rec.role->priority}">
      {cap}{$rec.role->getName(count($rec.authors))|escape}{/cap}
    </h3>

    {foreach $rec.authors as $author}
      <p class="rolePriority{$rec.role->priority}">
        <small class="text-muted">{$author->title}</small>
        {$author->name|escape}
        <small class="text-muted">{$author->academicRank}</small>
      </p>
    {/foreach}
  {/foreach}

  <div>
    {if User::can(User::PRIV_ADMIN)}
      <a class="btn btn-primary" href="{Router::link('source/edit')}?id={$source->id}">
        {include "bits/icon.tpl" i=edit}
        editează
      </a>
    {/if}

    <a class="btn btn-link" href="{Router::link('source/list')}">
      {include "bits/icon.tpl" i=arrow_back}
      lista de surse
    </a>
  </div>

{/block}
