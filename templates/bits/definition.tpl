{$showBookmark=$showBookmark|default:false}
{$showRemoveBookmark=$showRemoveBookmark|default:false}
{$showCourtesyLink=$showCourtesyLink|default:false}
{$showDate=$showDate|default:false}
{$showDeleteLink=$showDeleteLink|default:false}
{$showDropup=$showDropup|default:true}
{$showEditLink=$showEditLink|default:true}
{$showFlagTypo=$showFlagTypo|default:false}
{$showHistory=$showHistory|default:false}
{$showId=$showId|default:true}
{$showPermalink=$showPermalink|default:true}
{$showSource=$showSource|default:true}
{$showStatus=$showStatus|default:false}
{$showTypos=$showTypos|default:false}
{$showUser=$showUser|default:true}
{$showWotd=$showWotd|default:false}

{if $showFlagTypo}
  {include "bits/typoForm.tpl"}  
{/if}

{$def=$row->definition}

<div class="defWrapper">
  <p>
    <span class="def" title="Clic pentru a naviga la acest cuvânt">
      {$def->htmlRep}
    </span>
    {foreach $row->tags as $t}
      {include "bits/tag.tpl"}
    {/foreach}
  </p>

  <div class="defDetails small text-muted">
    <ul class="list-inline dropup">
      {if $showSource}
        <li>
          sursa:
          <a class="ref"
             href="{$wwwRoot}surse"
             title="{$row->source->name|escape}, {$row->source->year|escape}"
             >{$row->source->shortName|escape}
            {if $row->source->year}
              ({$row->source->year|regex_replace:"/ .*$/":""})
            {/if}
          </a>
        </li>
      {/if}

      {if $showCourtesyLink}
        {if $row->source->courtesyLink}
          <li>
            furnizată de
            <a class="ref" href="{$wwwRoot}spre/{$row->source->courtesyLink}">
              {$row->source->courtesyText}
            </a>
          </li>
        {/if}
      {/if}

      {if $showStatus}
        <li>
          starea: {$def->getStatusName()}
        </li>
      {/if}

      {if $showUser}
        {if $row->user->id}
          <li>
            adăugată de
            {strip}
            <a href="{$wwwRoot}utilizator/{$row->user->nick|escape:"url"}">
              {$row->user->nick|escape}
            </a>
            {if $showDate}
              , {$def->createDate|date_format:"%e %b %Y"}
            {/if}
            {/strip}
          </li>
        {/if}
      {/if}

      {if $showId}
        {if User::can(User::PRIV_EDIT)}
          <li>
            ID: {$def->id}
          </li>
        {/if}
      {/if}

      {if $showEditLink}
        {if User::can(User::PRIV_EDIT) && !$cfg.global.mirror}
          <li>
            <a href="{$wwwRoot}admin/definitionEdit.php?definitionId={$def->id}">
              editează
            </a>
          </li>
        {/if}
      {/if}

      {if $showWotd}
        <li>
          <i class="glyphicon glyphicon-calendar"></i>
          {if $def->status == Definition::ST_HIDDEN}
            definiție ascunsă
          {else if $row->wotdType == SearchResult::WOTD_NOT_IN_LIST}
            <a href="{$wwwRoot}wotdAdd.php?defId={$def->id}">
              adaugă WotD
            </a>
          {else if $row->wotdType == SearchResult::WOTD_IN_LIST}
            în lista de WotD {if $row->wotdDate}({$row->wotdDate}){/if}
          {else} {* a related definition is in WotD *}
            <span class="text-warning">
              o definiție similară este în WotD
              {if $row->wotdDate}({$row->wotdDate}){/if}
            </span>
          {/if}
        </li>
      {/if}

      {if $showDeleteLink}
        {if $def->status == Definition::ST_PENDING}
          <li>
            <a href="#"
               class="deleteLink"
               title="Șterge această definiție"
               data-id="{$def->id}">
              șterge
            </a>
          </li>
        {/if}
      {/if}

      {if $showDropup}
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            acțiuni
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            {if $showFlagTypo}
              {if $skinVariables.typo && !$cfg.global.mirror}
                <li>
                  <a href="#"
                     data-definition-id="{$def->id}"
                     data-toggle="modal"
                     data-target="#typoModal">
                    <i class="glyphicon glyphicon-flag"></i>
                    semnalează o greșeală
                  </a>
                </li>
              {/if}
            {/if}

            {if $showBookmark}
              {if User::getActive()}
                {if $row->bookmark}
                  <li class="disabled">
                    <a href="#">
                      <i class="glyphicon glyphicon-heart"></i>
                      adăugat la favorite
                    </a>
                  </li>
                {else}
                  <li>
                    <a class="bookmarkAddButton"
                       href="{$wwwRoot}ajax/bookmarkAdd.php?definitionId={$def->id}">
                      <i class="glyphicon glyphicon-heart"></i>
                      <span>adaugă la favorite</span>
                    </a>
                  </li>
                {/if}
              {/if}
            {/if}

            {if $showRemoveBookmark}
              <li>
                <a class="bookmarkRemoveButton"
                   href="{$wwwRoot}ajax/bookmarkRemove.php?definitionId={$def->id}">
                  <i class="glyphicon glyphicon-remove"></i>
                  <span>șterge de la favorite</span>
                </a>
              </li>
            {/if}

            {if $showPermalink}
              {if $skinVariables.permalink}
                <li>
                  <a href="{$wwwRoot}definitie/{$def->lexicon}/{$def->id}"
                     title="link direct către această definiție">
                    <i class="glyphicon glyphicon-link"></i>
                    permalink
                  </a>
                </li>
              {/if}
            {/if}

            {if $showHistory}
              {if User::can(User::PRIV_EDIT) && !$cfg.global.mirror}
                <li>
                  <a href="{$wwwRoot}istoria-definitiei?id={$def->id}">
                    <i class="glyphicon glyphicon-time"></i>
                    istoria definiției
                  </a>
                </li>
              {/if}
            {/if}

          </ul>
        </li>
      {/if}

    </ul>
  </div>

  {if $row->comment}
    <div class="panel panel-default panel-comment">
      <div class="panel-body">
        <i class="glyphicon glyphicon-comment"></i>
        {$row->comment->htmlContents} -
        <a href="{$wwwRoot}utilizator/{$row->commentAuthor->nick|escape:"url"}"
           >{$row->commentAuthor->nick|escape}</a>
      </div>
    </div>
  {/if}

  {if $showTypos}
    {if count($row->typos)}
      <ul>
        {foreach $row->typos as $typo}
          <li id="typo{$typo->id}">
            
            <span class="text-warning">
              {$typo->problem|escape}
            </span>
            
            <a href="#"
               title="Ignoră această raportare"
               onclick="return ignoreTypo('typo{$typo->id}', {$typo->id});">
              ignoră
            </a>
            
          </li>
        {/foreach}
      </ul>
    {/if}
  {/if}
</div>
