<nav class="navbar navbar-default">
  <div class="container">
    <div class="navbar-header navbar-left pull-left">
      {if !$onHomePage}
        <a class="navbar-brand" href="{$wwwRoot}" title="Prima pagină">
          <img id="logo-wide"
               alt="logo dexonline"
               src="{$wwwRoot}img/svg/logo-nav-wide.svg"
               width="173">
          <img id="logo-narrow"
               alt="logo dexonline"
               src="{$wwwRoot}img/svg/logo-nav-narrow.svg"
               width='44'>
        </a>
      {/if}
    </div>

    <div class="navbar-header navbar-right pull-right">
      <ul class="nav navbar-nav pull-left">
        <li>
          <p class="navbar-btn">
            <a class="btn btn-info" href="{$wwwRoot}doneaza">
              <i class="glyphicon glyphicon-credit-card"></i>
              Donează
            </a>
          </p>
        </li>
      </ul>

      <button type="button"
              class="navbar-toggle collapsed hamburger-menu"
              data-toggle="collapse"
              data-target="#navMenu"
              aria-expanded="false">
        <span class="sr-only">Navigare</span>
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
            Despre noi
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii"
                 target="_blank">
                Informații
              </a>
            </li>
            <li><a href="{$wwwRoot}contact">Contact</a></li>
            <li><a href="http://dexonline.blogspot.ro">Blogul nostru</a></li>
          </ul>

        </li>

        <li class="dropdown">

          <a href="#"
             class="dropdown-toggle"
             data-toggle="dropdown"
             role="button"
             aria-haspopup="true"
             aria-expanded="false">
            Implică-te
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li><a href="{$wwwRoot}contribuie">Contribuie cu definiții</a></li>
            <li><a href="{$wwwRoot}top">Topul voluntarilor</a></li>
            <li>
              <a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii_pentru_programatori">
                Informații pentru programatori
              </a>
            </li>
          </ul>

        </li>

        <li class="dropdown">

          <a href="#"
             class="dropdown-toggle"
             data-toggle="dropdown"
             role="button"
             aria-haspopup="true"
             aria-expanded="false">
            Articole și resurse
            <span class="caret"></span>
          </a>

          <ul class="dropdown-menu">
            <li>
              <a href="http://wiki.dexonline.ro/wiki/Abrevieri" target="_blank">
                Abrevieri folosite
              </a>
            </li>
            <li><a href="{$wwwRoot}articole">Articole lingvistice</a></li>
            <li><a href="{$wwwRoot}articol/Ghid_de_exprimare_corect%C4%83">Ghid de exprimare</a></li>
            <li><a href="{$wwwRoot}cuvantul-zilei">Cuvântul zilei</a></li>
            <li><a href="{$wwwRoot}cuvantul-lunii">Cuvântul lunii</a></li>
            <li><a href="{$wwwRoot}cuvinte-aleatoare">Cuvinte aleatoare</a></li>
            <li><a href="{$wwwRoot}scrabble">Scrabble</a></li>
            <li><a href="{$wwwRoot}unelte">Unelte</a></li>
            <li><a href="{$wwwRoot}legaturi">Legături externe</a></li>
          </ul>

        </li>
      </ul>

      {if !$cfg.global.mirror}
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"
               role="button" aria-haspopup="true" aria-expanded="false">
              {if User::getActive()->hasAvatar|default:false}
                {include "bits/avatar.tpl" user=User::getActive()}
              {else}
                <i class="glyphicon glyphicon-user"></i>
              {/if}
              {User::getActive()|escape|default:'Anonim'}
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              {if User::can(User::PRIV_ANY)}
                <li>
                  <a href="{$wwwRoot}admin">
                    <i class="glyphicon glyphicon-king"></i>
                    Pagina moderatorului
                  </a>
                </li>
              {/if}
              {if User::can(User::PRIV_EDIT)}
                <li>
                  <a href="#" data-toggle="modal" data-target="#hotkeysModal">
                    <i class="glyphicon glyphicon-hand-up"></i>
                    Hotkeys
                  </a>
                </li>
              {/if}
              {if isset($recentLinks)}
                <li>
                  <a href="#" id="recentPagesLink" data-toggle="modal" data-target="#recentModal">
                    <i class="glyphicon glyphicon-pushpin"></i>
                    Pagini vizitate recent
                  </a>
                </li>
              {/if}
              <li>
                <a href="{$wwwRoot}preferinte">
                  <i class="glyphicon glyphicon-cog"></i>
                  Preferințe
                </a>
              </li>
              {if User::getActive()}
                <li>
                  <a href="{$wwwRoot}utilizator/{User::getActive()}">
                    <i class="glyphicon glyphicon-user"></i>
                    Profilul meu
                  </a>
                </li>
                <li>
                  <a href="{$wwwRoot}cuvinte-favorite">
                    <i class="glyphicon glyphicon-heart"></i>
                    Cuvinte favorite
                  </a>
                </li>
                <li>
                  <a href="{$wwwRoot}auth/logout">
                    <i class="glyphicon glyphicon-log-out"></i>
                    Închide sesiunea
                  </a>
                </li>
              {else}
                <li>
                  <a href="{$wwwRoot}auth/login">
                    <i class="glyphicon glyphicon-log-in"></i>
                    Autentificare cu OpenID
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

{if User::can(User::PRIV_EDIT)}
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
            <li><b>Alt-A</b> = pagina moderatorului</li>
            <li><b>Alt-R</b> = reafișează (unde este cazul)</li>
            <li><b>Alt-S</b> = salvează</li>
            <li><b>Alt-V</b> = pagini vizitate recent</li>
            <li><b>Alt-W</b> = intră/ieși din modul WotD</li>
            <li><b>Alt-Shift-W</b> = istoricul definiției la nivel de cuvânt/literă</li>
          </ul>

          <h4>Pagina moderatorului</h4>
          
          <ul>
            <li><b>Alt-D</b> = navigare rapidă - definiții</li>
            <li><b>Alt-I</b> = navigare rapidă - intrări</li>
            <li><b>Alt-L</b> = navigare rapidă - lexeme</li>
            <li><b>Alt-Shift-D</b> = căutare definiții</li>
            <li><b>Alt-Shift-L</b> = căutare lexeme</li>
          </ul>

          <h4>Pagina moderatorului</h4>
            <li><b>Alt-Z</b> = navigare rapidă - imaginile cuvintelor zilei</li>
            <li><b>Alt-X</b> = navigare rapidă - asignare autori</li>
            <li><b>Alt-C</b> = navigare rapidă - cuvintele zilei</li>

          <h4>Etichetarea imaginilor</h4>
          
          <ul>
            <li><b>Alt-P</b> = previzualizarea etichetelor</li>
          </ul>

        </div>
      </div>
    </div>
  </div>
{/if}
