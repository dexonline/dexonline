{$ulClass=$ulClass|default:''}
{$showBookmark=$showBookmark|default:false}
{$showCourtesyLink=$showCourtesyLink|default:false}
{$showDate=$showDate|default:false}
{$showDeleteLink=$showDeleteLink|default:false}
{$showDropup=$showDropup|default:true}
{$showEditLink=$showEditLink|default:true}
{$showFlagTypo=$showFlagTypo|default:false}
{$showHistory=$showHistory|default:false}
{$showId=$showId|default:true}
{$showPageLink=$showPageLink|default:true}
{$showPageModal=$showPageModal|default:true}
{$showPermalink=$showPermalink|default:true}
{$showRemoveBookmark=$showRemoveBookmark|default:false}
{$showSource=$showSource|default:true}
{$showStatus=$showStatus|default:false}
{$showUser=$showUser|default:true}
{$showWotd=$showWotd|default:false}

{$def=$row->definition}

<ul class="list-inline mb-0 {$ulClass}">
  {if $row->source->hidden || $def->status == Definition::ST_HIDDEN}
    <li class="list-inline-item">
      <span class="badge bg-secondary">
        {include "bits/icon.tpl" i=visibility_off}
        {if $row->source->hidden}
          sursă ascunsă
        {else}
          definiție ascunsă
        {/if}
      </span>
    </li>
  {/if}

  {if $showSource}
    <li class="list-inline-item">
      {t}source{/t}:
      <a class="ref"
        href="{Router::link('source/view')}/{$row->source->urlName}"
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
      <li class="list-inline-item">
        {t}provided by{/t}
        <a class="ref" href="{Router::link('helpers/goto')}/{$row->source->courtesyLink}">
          {$row->source->courtesyText}
        </a>
      </li>
    {/if}
  {/if}

  {if $showStatus}
    <li class="list-inline-item">
      {t}status{/t}: {$def->getStatusName()}
    </li>
  {/if}

  {if $showUser}
    {if $row->user->id}
      <li class="list-inline-item">
        {t}added by{/t}
        {strip}
        <a href="{Router::link('user/view')}/{$row->user->nick|escape:"url"}">
          {$row->user->nick|escape}
        </a>
        {if $showDate}
          , {LocaleUtil::date($def->createDate)}
        {/if}
        {/strip}
      </li>
    {/if}
  {/if}

  {if $showId}
    {if User::can(User::PRIV_EDIT)}
      <li class="list-inline-item">
        ID: {$def->id}
      </li>
    {/if}
  {/if}

  {if $showEditLink}
    {if User::can(User::PRIV_EDIT)}
      <li class="list-inline-item">
        <a href="{Router::link('definition/edit')}/{$def->id}">
          editează
        </a>
      </li>
    {/if}
  {/if}

  {if $showWotd}
    <li class="list-inline-item">
      {include "bits/icon.tpl" i=today}
      {if $def->status == Definition::ST_HIDDEN || $row->source->hidden}
        definiție ascunsă
      {elseif $row->wotdType == SearchResult::WOTD_NOT_IN_LIST}
        <a href="{Router::link('wotd/add')}?defId={$def->id}">
          adaugă WotD
        </a>
      {elseif $row->wotdType == SearchResult::WOTD_IN_LIST}
        în lista de WotD {if $row->wotdDate}({$row->wotdDate}){/if}
      {else} {* a related definition is in WotD *}
        <span class="text-warning">
          o definiție similară este în WotD
          {if $row->wotdDate}
            {strip}
            (
            <a href="{Router::link('wotd/view')}/{$row->wotdDate|replace:'-':'/'}">
              {$row->wotdDate}
            </a>
            )
            {/strip}
          {/if}
        </span>
      {/if}
    </li>
  {/if}

  {if $showDeleteLink}
    {if $def->status == Definition::ST_PENDING}
      <li class="list-inline-item">
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
    <li class="list-inline-item dropup">
      <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
        {t}actions{/t}
      </a>

      <ul class="dropdown-menu">
        {if $showFlagTypo}
          {if Config::SKIN_TYPO}
            <li>
              <a href="#"
                class="dropdown-item"
                data-definition-id="{$def->id}"
                data-bs-toggle="modal"
                data-bs-target="#typoModal">
                {include "bits/icon.tpl" i=flag}
                {t}report a typo{/t}
              </a>
            </li>
          {/if}
        {/if}

        {if $showBookmark}
          {if User::getActive()}
            {if $row->bookmark}
              <li>
                <a href="#" class="dropdown-item disabled">
                  {include "bits/icon.tpl" i=favorite}
                  {t}added to favorites{/t}
                </a>
              </li>
            {else}
              <li>
                <a class="dropdown-item bookmarkAddButton"
                  href="{Config::URL_PREFIX}ajax/bookmarkAdd.php?definitionId={$def->id}">
                  {include "bits/icon.tpl" i=favorite}
                  <span
                    data-pending-text="{t}please wait...{/t}"
                    data-added-text="{t}added to favorites{/t}">
                    {t}add to favorites{/t}
                  </span>
                </a>
              </li>
            {/if}
          {/if}
        {/if}

        {if $showRemoveBookmark}
          <li>
            <a class="dropdown-item bookmarkRemoveButton"
              href="{Config::URL_PREFIX}ajax/bookmarkRemove.php?definitionId={$def->id}">
              {include "bits/icon.tpl" i=clear}
              <span data-pending-text="{t}please wait...{/t}">
                {t}remove from favorites{/t}
              </span>
            </a>
          </li>
        {/if}

        {if $showPermalink}
          {if Config::SKIN_PERMALINK}
            <li>
              <a href="{Config::URL_PREFIX}definitie/{$def->lexicon}/{$def->id}"
                class="dropdown-item"
                title="link direct către această definiție">
                {include "bits/icon.tpl" i=link}
                {t}permalink{/t}
              </a>
            </li>
          {/if}
        {/if}

        {if $showPageLink &&
          $row->source->hasPageImages &&
          (User::can(User::PRIV_ORIGINAL) ||
            TraineeSource::TraineeCanEditSource(User::getActiveId(), $def->sourceId))}
          <li>
            <a href="#"
              class="dropdown-item"
              title="arată pagina originală cu această definiție"
              data-bs-toggle="modal"
              data-bs-target="#pageModal"
              data-sourceId="{$def->sourceId}"
              data-word="{$def->lexicon|escape}"
              data-volume="{$def->volume|escape}"
              data-page="{$def->page|escape}">
              {include "bits/icon.tpl" i=description}
              arată originalul
            </a>
          </li>
        {/if}

        {if $showHistory}
          {if User::can(User::PRIV_EDIT)}
            <li>
              <a
                href="{Router::link('definition/history')}?id={$def->id}"
                class="dropdown-item">
                {include "bits/icon.tpl" i=hourglass_empty}
                istoria definiției
              </a>
            </li>
          {/if}
        {/if}

      </ul>
    </li>

    {$numDep=count($row->dependants)}
    {if $numDep}
      <li class="list-inline-item">
        <button
          class="btn btn-outline-secondary btn-sm"
          data-bs-toggle="collapse"
          data-bs-target="#identical-{$row->definition->id}">
          {include "bits/icon.tpl" i=repeat}
          {t
            count=$numDep
            1=$numDep
            plural="+%1 identical definitions"}
          +1 identical definition
          {/t}
        </button>
      </li>
    {/if}
  {/if}

</ul>

{if $showFlagTypo}
  {include "bits/typoForm.tpl"}
{/if}

{*
   Sometimes we need to include the modal separately. For example, nested forms are not
   allowed, so if we are inside a form we cannot include the modal.
  *}
{if $showPageLink &&
  $showPageModal &&
  $showDropup &&
  $row->source->hasPageImages &&
  (User::can(User::PRIV_ORIGINAL) || TraineeSource::TraineeCanEditSource(User::getActiveId(), $def->sourceId))}
  {include "bits/pageModal.tpl"}
{/if}
