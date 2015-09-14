<p>
  <span class="def" title="Clic pentru a naviga la acest cuvânt">
    {$row->definition->htmlRep}
  </span>
  <br/>

  <span class="defDetails">
    Sursa: <a class="ref" href="{$wwwRoot}surse" title="{$row->source->name|escape}, {$row->source->year|escape}"
    >{$row->source->shortName|escape}
    {if $row->source->year}
    ({$row->source->year|regex_replace:"/ .*$/":""})
    {/if}
    </a> |
    
    {* TODO de generalizat *}
    {if $row->source->shortName == 'DCR2'}
        Furnizată de <a class="ref" href="{$wwwRoot}spre/logos">Editura Logos</a> |
    {/if}

    {if $row->user->id}
      Adăugată de 
      <a href="{$wwwRoot}utilizator/{$row->user->nick|escape:"url"}">{$row->user->nick|escape}</a>
    {else}
      Adăugată de anonim
    {/if}

    {if $sUser && ($sUser->moderator & $smarty.const.PRIV_EDIT)} 
      | Id: {$row->definition->id}
    {/if}

    {if $skinVariables.typo}
      | <a class="typoLink" id="typoLink-{$row->definition->id}" href="#">Semnalează o greșeală</a>
    {/if}

    {if $sUser}
      {if $row->bookmark}
        | Adăugat la favorite 
      {else}
        | <a class="bookmarkAddButton" href="{$wwwRoot}ajax/bookmarkAdd.php?definitionId={$row->definition->id}">Adaugă la favorite</a>
      {/if}
    {/if}

    {if $skinVariables.permalink}
      | <a href="{$wwwRoot}definitie/{$row->definition->lexicon}/{$row->definition->id}" title="Link doar către această definiție">Permalink</a>
    {/if}

    {if $sUser && ($sUser->moderator & $smarty.const.PRIV_EDIT) && !$cfg.global.mirror}
      | <a target="edit_window" href="{$wwwRoot}admin/definitionEdit.php?definitionId={$row->definition->id}">Editează</a>
    {/if}

    {if $sUser && ($sUser->moderator & $smarty.const.PRIV_EDIT) && !$cfg.global.mirror}
      | <a href="{$wwwRoot}istoria-definitiei?id={$row->definition->id}">Istoria definiției</a>
    {/if}

    {if $sUser && ($sUser->moderator & $smarty.const.PRIV_WOTD) && !$cfg.global.mirror}
      {if $row->definition->status == 0}
        {if $row->wotd}
        | În lista de WotD {if $row->wotd!==true}({$row->wotd}){/if}
        {else}
        | <a href="{$wwwRoot}wotdAdd.php?defId={$row->definition->id}">Adaugă WotD</a>
        {/if}
      {else}
        | Definiție ascunsă
      {/if}
    {/if}
  </span>

  {if $row->comment}
    <span class="defComment">
      Comentariu: {$row->comment->htmlContents} -
      <a href="{$wwwRoot}utilizator/{$row->commentAuthor->nick|escape:"url"}"
      >{$row->commentAuthor->nick|escape}</a>
    </span>
  {/if}
</p>
