{$showBookmark=$showBookmark|default:false}
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

{$def=$row->definition}

<div class="defWrapper">
  <p class="def" title="Clic pentru a naviga la acest cuvânt">
    {$def->htmlRep}
    {foreach $row->tags as $t}
      <label class="label label-info">{$t->value}</label>
    {/foreach}
  </p>

  <small class="defDetails text-muted">
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
        {if $sUser && ($sUser->moderator & $smarty.const.PRIV_EDIT)}
          <li>
            ID: {$def->id}
          </li>
        {/if}
      {/if}

      {if $showEditLink}
        {if $sUser && ($sUser->moderator & $smarty.const.PRIV_EDIT) && !$cfg.global.mirror}
          <li>
            <a href="{$wwwRoot}admin/definitionEdit.php?definitionId={$def->id}">
              editează
            </a>
          </li>
        {/if}
      {/if}

      {if $showWotd}
        {if $sUser && ($sUser->moderator & $smarty.const.PRIV_WOTD) && !$cfg.global.mirror}
          <li>
            {if $def->status == 0}
              {if $row->wotd}
                în lista de WotD {if $row->wotd!==true}({$row->wotd}){/if}
              {else}
                <a href="{$wwwRoot}wotdAdd.php?defId={$def->id}">
                  adaugă WotD
                </a>
              {/if}
            {else}
              definiție ascunsă
            {/if}
          </li>
        {/if}
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
              {if $sUser}
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
              {if $sUser && ($sUser->moderator & $smarty.const.PRIV_EDIT) && !$cfg.global.mirror}
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
  </small>

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
