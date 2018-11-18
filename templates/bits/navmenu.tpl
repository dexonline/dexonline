<nav class="navbar navbar-default">
  <div class="container">
    <div class="navbar-header navbar-left pull-left">
      {if $pageType != 'home'}
        <div class="logo-wrapper">
          <a class="navbar-brand" href="{$wwwRoot}" title="{'home page'|_|cap}">
            <img id="logo-nav"
              alt="logo dexonline"
              src="{$wwwRoot}img/svg/logo-nav.svg">
          </a>
        </div>
      {/if}
    </div>

    <div class="navbar-header navbar-right pull-right">
      <ul class="nav navbar-nav pull-left">
        <li>
          <p class="navbar-btn">
            <a class="btn btn-info" href="{$wwwRoot}doneaza">
              <i class="glyphicon glyphicon-credit-card"></i>
              <span>{'donate'|_}</span>
            </a>
          </p>
        </li>
      </ul>

      <button type="button"
        class="navbar-toggle collapsed hamburger-menu"
        data-toggle="collapse"
        data-target="#navMenu"
        aria-expanded="false">
        <span class="sr-only">{'navigation'|_}</span>
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
            {'about'|_}
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="https://wiki.dexonline.ro/wiki/Informa%C8%9Bii"
                target="_blank">
                {'information'|_}
              </a>
            </li>
            <li><a href="{$wwwRoot}contact">{'contact us'|_}</a></li>
            <li><a href="https://dexonline.blogspot.ro">{'blog'|_}</a></li>
          </ul>

        </li>

        <li class="dropdown">

          <a href="#"
            class="dropdown-toggle"
            data-toggle="dropdown"
            role="button"
            aria-haspopup="true"
            aria-expanded="false">
            {'get involved'|_}
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="https://wiki.dexonline.ro/wiki/Cum_pute%C8%9Bi_ajuta">{'ways to help'|_}</a>
            </li>
            <li><a href="{$wwwRoot}top">{'volunteer ranking'|_}</a></li>
          </ul>

        </li>

        <li class="dropdown">

          <a href="#"
            class="dropdown-toggle"
            data-toggle="dropdown"
            role="button"
            aria-haspopup="true"
            aria-expanded="false">
            {'resources'|_}
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="https://wiki.dexonline.ro/wiki/Abrevieri" target="_blank">
                {'abbreviations table'|_}
              </a>
            </li>
            <li><a href="{$wwwRoot}articole">{'linguistic articles'|_}</a></li>
            <li><a href="{$wwwRoot}articol/Ghid_de_exprimare_corect%C4%83">{'grammar guide'|_}</a></li>
            <li><a href="{$wwwRoot}cuvantul-zilei">{'word of the day'|_}</a></li>
            <li><a href="{$wwwRoot}cuvantul-lunii">{'word of the month'|_}</a></li>
            <li><a href="{$wwwRoot}cuvinte-aleatoare">{'random words'|_}</a></li>
            <li><a href="{$wwwRoot}scrabble">{'Scrabble'|_}</a></li>
            <li><a href="{$wwwRoot}unelte">{'tools'|_}</a></li>
            <li><a href="{$wwwRoot}legaturi">{'external links'|_}</a></li>
          </ul>

        </li>
      </ul>

      {if !$cfg.global.mirror}
        <ul class="nav navbar-nav navbar-right">

          {* language selector *}
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"
              role="button" aria-haspopup="true" aria-expanded="false">
              <i class="glyphicon glyphicon-globe"></i>
              <span class="caret"></span>
            </a>

            <ul class="dropdown-menu">
              {foreach Locale::getAll() as $id => $name}
                <li>
                  <a href="{$wwwRoot}changeLocale?id={$id}">
                    <i class="glyphicon glyphicon-ok {if $id != Locale::getCurrent()}invisible{/if}">
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
              {User::getActive()|escape|default:{'Anonymous'|_}}
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              {if User::can(User::PRIV_ANY)}
                <li>
                  <a href="{$wwwRoot}admin">
                    <i class="glyphicon glyphicon-king"></i>
                    <span>{'moderator page'|_}</span>
                  </a>
                </li>
              {/if}
              {if User::can(User::PRIV_ANY)}
                <li>
                  <a href="#" data-toggle="modal" data-target="#hotkeysModal">
                    <i class="glyphicon glyphicon-hand-up"></i>
                    <span>{'hotkeys'|_}</span>
                  </a>
                </li>
              {/if}
              {if isset($recentLinks)}
                <li>
                  <a href="#" id="recentPagesLink" data-toggle="modal" data-target="#recentModal">
                    <i class="glyphicon glyphicon-pushpin"></i>
                    <span>{'recently viewed pages'|_}</span>
                  </a>
                </li>
              {/if}
              <li>
                <a href="{$wwwRoot}preferinte">
                  <i class="glyphicon glyphicon-cog"></i>
                  <span>{'preferences'|_}</span>
                </a>
              </li>
              {if User::getActive()}
                <li>
                  <a href="{$wwwRoot}utilizator/{User::getActive()}">
                    <i class="glyphicon glyphicon-user"></i>
                    <span>{'profile'|_}</span>
                  </a>
                </li>
                <li>
                  <a href="{$wwwRoot}cuvinte-favorite">
                    <i class="glyphicon glyphicon-heart"></i>
                    <span>{'favorite words'|_}</span>
                  </a>
                </li>
                <li>
                  <a href="{$wwwRoot}auth/logout">
                    <i class="glyphicon glyphicon-log-out"></i>
                    <span>{'log out'|_}</span>
                  </a>
                </li>
              {else}
                <li>
                  <a href="{$wwwRoot}auth/login">
                    <i class="glyphicon glyphicon-log-in"></i>
                    <span>{'log in'|_}</span>
                  </a>
                </li>
              {/if}
            </ul>
          </li>
        </ul>
      {/if}

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
            <li><a href="{$wwwRoot}admin/">Alt-A</a> = pagina moderatorului</li>
            <li><a href="{$wwwRoot}admin/definitionEdit.php">Alt-N</a> = adaugă o definiție</li>
            <li><a href="{$wwwRoot}admin/wotdTable.php">Alt-C</a> = cuvintele zilei</li>
            <li><a href="{$wwwRoot}alocare-autori.php">Alt-X</a> = asignare autori</li>
            <li><a href="{$wwwRoot}admin/wotdImages.php">Alt-Z</a> = imaginile cuvintelor zilei</li>
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
