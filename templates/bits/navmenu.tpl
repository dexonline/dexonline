{* Control the flex order because the Donate button comes before the menus in <=sm, *}
{* but after them in >= md. *}
<nav class="navbar navbar-expand-md navbar-light mb-3">
  <div class="container">
    {if $pageType != 'home'}
      <a
        class="navbar-brand order-0"
        href="{Config::URL_PREFIX}"
        title="{cap}{t}home page{/t}{/cap}">
        <img
          alt="logo dexonline"
          src="{Config::URL_PREFIX}img/svg/logo-nav.svg"
          width="173">
      </a>
    {/if}

    {* use order-1 for this one *}
    {Plugin::notify('navbar')}

    {* this migrates to end (5) below md *}
    <div class="collapse navbar-collapse order-5 order-md-2" id="navMenu">
      <ul class="navbar-nav">

        <li class="nav-item dropdown">

          <a
            class="nav-link dropdown-toggle"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            {cap}{t}about{/t}{/cap}
          </a>

          <ul class="dropdown-menu">
            <li>
              <a
                href="https://wiki.dexonline.ro/wiki/Informa%C8%9Bii"
                class="dropdown-item"
                target="_blank">
                {cap}{t}information{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('simple/contact')}" class="dropdown-item">
                {cap}{t}contact us{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="https://blog.dexonline.ro" class="dropdown-item">
                {cap}{t}blog{/t}{/cap}
              </a>
            </li>
          </ul>

        </li>

        <li class="nav-item dropdown">

          <a
            class="nav-link dropdown-toggle"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            {cap}{t}get involved{/t}{/cap}
          </a>

          <ul class="dropdown-menu">
            <li>
              <a
                href="https://wiki.dexonline.ro/wiki/Cum_pute%C8%9Bi_ajuta"
                class="dropdown-item">
                {cap}{t}ways to help{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('user/top')}" class="dropdown-item">
                {cap}{t}volunteer ranking{/t}{/cap}
              </a>
            </li>
          </ul>

        </li>

        <li class="nav-item dropdown">

          <a
            class="nav-link dropdown-toggle"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            {cap}{t}resources{/t}{/cap}
          </a>

          <ul class="dropdown-menu">
            <li>
              <a
                href="https://wiki.dexonline.ro/wiki/Abrevieri"
                class="dropdown-item"
                target="_blank">
                {cap}{t}abbreviations table{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('article/list')}" class="dropdown-item">
                {cap}{t}linguistic articles{/t}{/cap}
              </a>
            </li>
            <li>
              <a
                href="{Router::link('article/view')}/Ghid_de_exprimare_corect%C4%83"
                class="dropdown-item">
                {cap}{t}grammar guide{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('wotd/view')}" class="dropdown-item">
                {cap}{t}word of the day{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('wotm/view')}" class="dropdown-item">
                {cap}{t}word of the month{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('lexeme/random')}" class="dropdown-item">
                {cap}{t}random words{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('games/scrabble')}" class="dropdown-item">
                {cap}{t}Scrabble{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('simple/tools')}" class="dropdown-item">
                {cap}{t}tools{/t}{/cap}
              </a>
            </li>
            <li>
              <a href="{Router::link('simple/links')}" class="dropdown-item">
                {cap}{t}external links{/t}{/cap}
              </a>
            </li>
          </ul>

        </li>
      </ul>

      <ul class="navbar-nav ms-auto">

        {* language selector *}
        <li class="nav-item dropdown">
          <a
            class="nav-link"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            {include "bits/icon.tpl" i=language}
          </a>

          <ul class="dropdown-menu">
            {foreach Config::LOCALES as $id => $name}
              <li>
                <a
                  href="{Router::link('helpers/changeLocale')}?id={$id}"
                  class="dropdown-item">
                  <span {if $id != LocaleUtil::getCurrent()}class="invisible"{/if}>
                    {include "bits/icon.tpl" i=done}
                  </span>
                  {$name}
                </a>
              </li>
            {/foreach}
          </ul>
        </li>

        {* user menu *}
        <li class="nav-item dropdown">

          <a
            class="nav-link dropdown-toggle"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
            {include "bits/avatar.tpl" user=User::getActive()}
            {capture "anon"}{t}Anonymous{/t}{/capture}
            {User::getActive()|escape|default:$smarty.capture.anon}
          </a>

          <ul class="dropdown-menu">
            {if User::can(User::PRIV_ANY)}
              <li>
                <a href="{Router::link('aggregate/dashboard')}" class="dropdown-item">
                  {include "bits/icon.tpl" i=shield}
                  {cap}{t}moderator page{/t}{/cap}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="dropdown-item"
                  data-bs-toggle="modal"
                  data-bs-target="#modal-hotkeys">
                  {include "bits/icon.tpl" i=keyboard}
                  {cap}{t}hotkeys{/t}{/cap}
                </a>
              </li>
            {/if}
            {if isset($recentLinks)}
              <li>
                <a
                  href="#"
                  class="dropdown-item"
                  id="recentPagesLink"
                  data-bs-toggle="modal"
                  data-bs-target="#modal-recent">
                  {include "bits/icon.tpl" i=history}
                  {cap}{t}recently viewed pages{/t}{/cap}
                </a>
              </li>
            {/if}
            <li>
              <a href="{Router::link('user/preferences')}" class="dropdown-item">
                {include "bits/icon.tpl" i=settings}
                {cap}{t}preferences{/t}{/cap}
              </a>
            </li>
            {if User::getActive()}
              <li>
                <a
                  href="{Router::link('user/view')}/{User::getActive()}"
                  class="dropdown-item">
                  {include "bits/icon.tpl" i=person}
                  {cap}{t}profile{/t}{/cap}
                </a>
              </li>
              <li>
                <a href="{Router::link('definition/favorites')}" class="dropdown-item">
                  {include "bits/icon.tpl" i=favorite}
                  {cap}{t}favorite words{/t}{/cap}
                </a>
              </li>
              <li>
                <a href="{Router::link('auth/logout')}" class="dropdown-item">
                  {include "bits/icon.tpl" i=logout}
                  {cap}{t}log out{/t}{/cap}
                </a>
              </li>
            {else}
              <li>
                <a href="{Router::link('auth/login')}" class="dropdown-item">
                  {include "bits/icon.tpl" i=login}
                  {cap}{t}log in{/t}{/cap}
                </a>
              </li>
            {/if}
          </ul>
        </li>
      </ul>

    </div>

    <a
      class="btn btn-info ms-auto order-3"
      href="{Router::link('donation/donate')}">
      {include "bits/icon.tpl" i=credit_card}
      {cap}{t}donate{/t}{/cap}
    </a>

    <button
      class="navbar-toggler order-4"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navMenu"
      aria-controls="navMenu"
      aria-expanded="false"
      aria-label="{t}navigation{/t}">
      <span class="navbar-toggler-icon"></span>
    </button>

  </div>
</nav>

{if User::can(User::PRIV_ANY)}
  <div class="modal fade" id="modal-hotkeys" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Hotkeys (scurtături)</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <h4>Globale</h4>

          <ul>
            <li><b>Alt-R</b> = reafișează (unde este cazul)</li>
            <li><b>Alt-S</b> = salvează</li>
            <li><b>Alt-Q</b> = tabel de glife</li>
            <li><b>Alt-V</b> = pagini vizitate recent</li>
            <li>
              <a href="#" class="hotkey-link" data-mode="structure">Alt-T</a> =
              intră/ieși din modul structurist
            </li>
            <li>
              <a href="#" class="hotkey-link" data-mode="wotd">Alt-W</a> =
              intră/ieși din modul WotD
            </li>
            <li>
              <a href="#" class="hotkey-link" data-mode="granularity">Alt-Shift-W</a> =
              istoricul definiției la nivel de cuvânt/literă
            </li>
          </ul>

          <h4>Salt la pagină</h4>

          <ul>
            <li>
              <a href="{Router::link('aggregate/dashboard')}">Alt-A</a>
              = pagina moderatorului
            </li>
            <li>
              <a href="{Router::link('definition/edit')}">Alt-N</a>
              = adaugă o definiție
            </li>
            <li>
              <a href="{Router::link('wotd/table')}">Alt-C</a>
              = cuvintele zilei
            </li>
            <li>
              <a href="{Router::link('artist/assign')}">Alt-X</a>
              = asignare autori
            </li>
            <li>
              <a href="{Router::link('wotd/images')}">Alt-Z</a>
              = imaginile cuvintelor zilei
            </li>
          </ul>

          <h4>Pagina moderatorului</h4>

          <ul>
            <li><b>Alt-D</b> = navigare rapidă - definiții</li>
            <li><b>Alt-I</b> = navigare rapidă - intrări</li>
            <li><b>Alt-L</b> = navigare rapidă - lexeme</li>
          </ul>

          <h4>Etichetarea imaginilor</h4>

          <ul>
            <li><b>Alt-P</b> = previzualizarea etichetelor</li>
          </ul>

        </div>
      </div>
    </div>
  </div>
{/if}
