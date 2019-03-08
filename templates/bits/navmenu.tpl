<nav class="navbar navbar-default">
  <div class="container">
    <div class="navbar-header navbar-left pull-left">
      {if $pageType != 'home'}
        <div class="logo-wrapper">
          <a class="navbar-brand" href="{Config::URL_PREFIX}" title="{cap}{t}home page{/t}{/cap}">
            <img id="logo-nav"
              alt="logo dexonline"
              src="{Config::URL_PREFIX}img/svg/logo-nav.svg">
          </a>
        </div>
      {/if}
    </div>

    <div class="navbar-header navbar-right pull-right">
      <ul class="nav navbar-nav pull-left">
        <li>
          <p class="navbar-btn">
            <a class="btn btn-info" href="{Router::link('donation/donate')}">
              <i class="glyphicon glyphicon-credit-card"></i>
              <span>{t}donate{/t}</span>
            </a>
          </p>
        </li>
      </ul>

      <button type="button"
        class="navbar-toggle collapsed hamburger-menu"
        data-toggle="collapse"
        data-target="#navMenu"
        aria-expanded="false">
        <span class="sr-only">{t}navigation{/t}</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="navMenu">
      <!-- Collect the nav links, forms, and other content for toggling -->
      <ul class="nav navbar-nav">

        <li class="dropdown">

          <a href="#"
            class="dropdown-toggle"
            data-toggle="dropdown"
            role="button"
            aria-haspopup="true"
            aria-expanded="false">
            {t}about{/t}
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="https://wiki.dexonline.ro/wiki/Informa%C8%9Bii"
                target="_blank">
                {t}information{/t}
              </a>
            </li>
            <li><a href="{Config::URL_PREFIX}contact">{t}contact us{/t}</a></li>
            <li><a href="https://dexonline.blogspot.ro">{t}blog{/t}</a></li>
          </ul>

        </li>

        <li class="dropdown">

          <a href="#"
            class="dropdown-toggle"
            data-toggle="dropdown"
            role="button"
            aria-haspopup="true"
            aria-expanded="false">
            {t}get involved{/t}
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="https://wiki.dexonline.ro/wiki/Cum_pute%C8%9Bi_ajuta">{t}ways to help{/t}</a>
            </li>
            <li><a href="{Router::link('user/top')}">{t}volunteer ranking{/t}</a></li>
          </ul>

        </li>

        <li class="dropdown">

          <a href="#"
            class="dropdown-toggle"
            data-toggle="dropdown"
            role="button"
            aria-haspopup="true"
            aria-expanded="false">
            {t}resources{/t}
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="https://wiki.dexonline.ro/wiki/Abrevieri" target="_blank">
                {t}abbreviations table{/t}
              </a>
            </li>
            <li><a href="{Router::link('article/list')}">{t}linguistic articles{/t}</a></li>
            <li><a href="{Router::link('article/view')}/Ghid_de_exprimare_corect%C4%83">{t}grammar guide{/t}</a></li>
            <li><a href="{Router::link('wotd/view')}">{t}word of the day{/t}</a></li>
            <li><a href="{Router::link('wotm/view')}">{t}word of the month{/t}</a></li>
            <li><a href="{Config::URL_PREFIX}cuvinte-aleatoare">{t}random words{/t}</a></li>
            <li><a href="{Router::link('games/scrabble')}">{t}Scrabble{/t}</a></li>
            <li><a href="{Config::URL_PREFIX}unelte">{t}tools{/t}</a></li>
            <li><a href="{Config::URL_PREFIX}legaturi">{t}external links{/t}</a></li>
          </ul>

        </li>
      </ul>

      <ul class="nav navbar-nav navbar-right">

        {* language selector *}
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"
            role="button" aria-haspopup="true" aria-expanded="false">
            <i class="glyphicon glyphicon-globe"></i>
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            {foreach Config::LOCALES as $id => $name}
              <li>
                <a href="{Config::URL_PREFIX}changeLocale?id={$id}">
                  <i class="glyphicon glyphicon-ok {if $id != LocaleUtil::getCurrent()}invisible{/if}">
                  </i>
                  {$name}
                </a>
              </li>
            {/foreach}
          </ul>
        </li>

        {* user menu *}
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"
            role="button" aria-haspopup="true" aria-expanded="false">
            {if User::getActive()->hasAvatar|default:false}
              {include "bits/avatar.tpl" user=User::getActive()}
            {else}
              <i class="glyphicon glyphicon-user"></i>
            {/if}
            {capture "anon"}{t}Anonymous{/t}{/capture}
            {User::getActive()|escape|default:$smarty.capture.anon}
            <span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            {if User::can(User::PRIV_ANY)}
              <li>
                <a href="{Config::URL_PREFIX}admin/">
                  <i class="glyphicon glyphicon-king"></i>
                  <span>{t}moderator page{/t}</span>
                </a>
              </li>
            {/if}
            {if User::can(User::PRIV_ANY)}
              <li>
                <a href="#" data-toggle="modal" data-target="#hotkeysModal">
                  <i class="glyphicon glyphicon-hand-up"></i>
                  <span>{t}hotkeys{/t}</span>
                </a>
              </li>
            {/if}
            {if isset($recentLinks)}
              <li>
                <a href="#" id="recentPagesLink" data-toggle="modal" data-target="#recentModal">
                  <i class="glyphicon glyphicon-pushpin"></i>
                  <span>{t}recently viewed pages{/t}</span>
                </a>
              </li>
            {/if}
            <li>
              <a href="{Config::URL_PREFIX}preferinte">
                <i class="glyphicon glyphicon-cog"></i>
                <span>{t}preferences{/t}</span>
              </a>
            </li>
            {if User::getActive()}
              <li>
                <a href="{Router::link('user/view')}/{User::getActive()}">
                  <i class="glyphicon glyphicon-user"></i>
                  <span>{t}profile{/t}</span>
                </a>
              </li>
              <li>
                <a href="{Router::link('definition/favorites')}">
                  <i class="glyphicon glyphicon-heart"></i>
                  <span>{t}favorite words{/t}</span>
                </a>
              </li>
              <li>
                <a href="{Router::link('auth/logout')}">
                  <i class="glyphicon glyphicon-log-out"></i>
                  <span>{t}log out{/t}</span>
                </a>
              </li>
            {else}
              <li>
                <a href="{Router::link('auth/login')}">
                  <i class="glyphicon glyphicon-log-in"></i>
                  <span>{t}log in{/t}</span>
                </a>
              </li>
            {/if}
          </ul>
        </li>
      </ul>

    </div>
  </div>
</nav>

{if User::can(User::PRIV_ANY)}
  <div class="modal fade" id="hotkeysModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Hotkeys (scurtături)</h4>
        </div>
        <div class="modal-body">

          <h4>Globale</h4>

          <ul>
            <li><b>Alt-R</b> = reafișează (unde este cazul)</li>
            <li><b>Alt-S</b> = salvează</li>
            <li><b>Alt-Q</b> = tabel de glife</li>
            <li><b>Alt-V</b> = pagini vizitate recent</li>
            <li>
              <a href="#" class="hotkeyLink" data-mode="structure">Alt-T</a> =
              intră/ieși din modul structurist
            </li>
            <li>
              <a href="#" class="hotkeyLink" data-mode="wotd">Alt-W</a> =
              intră/ieși din modul WotD
            </li>
            <li>
              <a href="#" class="hotkeyLink" data-mode="granularity">Alt-Shift-W</a> =
              istoricul definiției la nivel de cuvânt/literă
            </li>
          </ul>

          <h4>Salt la pagină</h4>

          <ul>
            <li>
              <a href="{Config::URL_PREFIX}admin/">Alt-A</a>
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
