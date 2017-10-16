{extends "layout-admin.tpl"}

{block "title"}Pagina moderatorului{/block}

{block "content"}
  <h3>Pagina moderatorului</h3>

  {include "bits/phpConstants.tpl"}

  {if User::can(User::PRIV_EDIT)}
    <div class="panel panel-default">
      <div class="panel-heading">
        Rapoarte

        <form class="pull-right">

          <small class="text-muted">
            calculate acum {($timeAgo/60)|string_format:"%d"} minute, {$timeAgo%60} secunde
          </small>

          <button type="submit" name="recountButton" class="btn btn-info btn-xs">
            <i class="glyphicon glyphicon-repeat"></i>
            recalculează acum
          </button>

        </form>

      </div>

      <table class="table table-condensed table-hover">
        {foreach $reports as $r}
          {if $r.count && User::can($r.privilege)}
            <tr>
              <td>{$r.text}</td>
              <td><a href="{$wwwRoot}{$r.url}">{$r.count}</a></td>
            </tr>
          {/if}
        {/foreach}
      </table>
    </div>
  {/if}

  {if User::can(User::PRIV_EDIT)}
    <div class="panel panel-default quickNav">
      <div class="panel-heading">
        Navigare rapidă
      </div>

      <div class="panel-body">
        <div class="col-lg-4 col-md-6 col-sm-6">
          <form action="definitionEdit.php">
            <select id="definitionId" name="definitionId"></select>
          </form>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6">
          <form action="{$wwwRoot}editEntry.php">
            <select id="entryId" name="id"></select>
          </form>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6">
          <form action="lexemEdit.php">
            <select id="lexemId" name="lexemId"></select>
          </form>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6">
          <form action="{$wwwRoot}editTree.php">
            <select id="treeId" name="id"></select>
          </form>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6">
          <form action="{$wwwRoot}eticheta.php">
            <select id="labelId" name="id"></select>
          </form>
        </div>
      </div>
    </div>
  {/if}

  {if User::can(User::PRIV_EDIT)}
    <div class="row">
      <div class="col-sm-6 col-md-6 col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            Caută definiții
          </div>

          <div class="panel-body">
            <form class="form-horizontal" action="definitionLookup.php" method="post">
              <div class="form-group">
                <label class="col-sm-3 control-label">lexem</label>
                <div class="col-sm-9">
                  <input id="definitionName" class="form-control" type="text" name="name" value="*">
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">starea</label>
                <div class="col-sm-9">
                  {include "bits/statusDropDown.tpl"
                  name="status"
                  selectedStatus=Definition::ST_ACTIVE}
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">trimise de</label>
                <div class="col-sm-9">
                  <input class="form-control" type="text" name="nick">
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">sursa</label>
                <div class="col-sm-9">
                  {include "bits/sourceDropDown.tpl" name="sourceId"}
                </div>
              </div>

              {assign var="nextYear" value=$currentYear+1}
              <div class="form-group">
                <label class="col-sm-3 control-label">între</label>
                <div class="col-sm-9 form-inline">
                  {include "bits/numericDropDown.tpl" name="yr1" start=2001 end=$nextYear}
                  {include "bits/numericDropDown.tpl" name="mo1" start=1 end=13}
                  {include "bits/numericDropDown.tpl" name="da1" start=1 end=32}
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">și</label>
                <div class="col-sm-9 form-inline">
                  {include "bits/numericDropDown.tpl" name="yr2" start=2001 end=$nextYear
                  selected=$currentYear}
                  {include "bits/numericDropDown.tpl" name="mo2" start=1 end=13 selected=12}
                  {include "bits/numericDropDown.tpl" name="da2" start=1 end=32 selected=31}
                </div>
              </div>

              <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                  <button type="submit" class="btn btn-primary" name="searchButton">
                    <i class="glyphicon glyphicon-search"></i>
                    caută
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-md-6 col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            Caută lexeme
          </div>

          <div class="panel-body">
            <form class="form-horizontal" action="lexemSearch.php">
              <div class="form-group">
                <label class="col-sm-3 control-label">forma</label>
                <div class="col-sm-9">
                  <input id="lexemForm"
                         class="form-control"
                         type="text"
                         name="form"
                         placeholder="acceptă expresii regulate">
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">sursa</label>
                <div class="col-sm-9">
                  {include "bits/sourceDropDown.tpl"}
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">în LOC</label>
                <div class="col-sm-9">
                  <select class="form-control" name="loc">
                    <option value="2">indiferent</option>
                    <option value="1">da</option>
                    <option value="0">nu</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">paradigmă</label>
                <div class="col-sm-9">
                  <select class="form-control" name="paradigm">
                    <option value="2">indiferent</option>
                    <option value="1">da</option>
                    <option value="0">nu</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">structurare</label>
                <div class="col-sm-9">
                  {include "bits/structStatus.tpl" canEdit=true anyOption=true}
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">structurist</label>
                <div class="col-sm-9">
                  <select name="structuristId" class="form-control">
                    <option value="{Entry::STRUCTURIST_ID_ANY}">oricare</option>
                    <option value="{Entry::STRUCTURIST_ID_NONE}">niciunul</option>
                    {foreach $structurists as $s}
                      <option value="{$s->id}">
                        {$s->nick} ({$s->name})
                      </option>
                    {/foreach}
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">trimise de</label>
                <div class="col-sm-9">
                  <input class="form-control" type="text" name="nick">
                </div>
              </div>

              <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                  <button type="submit" class="btn btn-primary" name="searchButton">
                    <i class="glyphicon glyphicon-search"></i>
                    caută
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  {/if}

  {if User::can(User::PRIV_EDIT)}
    <div class="panel panel-default">
      <div class="panel-heading">
        Modele de flexiune
      </div>
      
      <div class="panel-body">

        <form class="form-inline" action="dispatchModelAction.php">
          <div class="form-group">
            <span data-model-dropdown>
              <input type="hidden" name="locVersion" value="6.0" data-loc-version>
              <select class="form-control" name="modelType" data-model-type data-canonical="1">
              </select>
              <select class="form-control" name="modelNumber" data-model-number>
              </select>
            </span>

            <div class="btn-group">
              <button type="submit" class="btn btn-default" name="showLexems">
                arată toate lexemele
              </button>
              <button type="submit" class="btn btn-default" name="editModel">
                <i class="glyphicon glyphicon-pencil"></i>
                editează
              </button>
              <button type="submit" class="btn btn-default" name="cloneModel">
                <i class="glyphicon glyphicon-duplicate"></i>
                clonează
              </button>
              <button type="submit" class="btn btn-danger" name="deleteModel">
                <i class="glyphicon glyphicon-trash"></i>
                șterge
              </button>
            </div>
          </div>
        </form>

        <div class="voffset2"></div>

        <p>
          <a href="../admin/mergeLexems.php">unificare plural-singular</a>

          <span class="text-muted">
            pentru familiile de plante și animale și pentru alte lexeme care apar
            cu restricția „P” într-o sursă, dar fără restricții în altă sursă.
          </span>
        </p>

        <p>
          <a href="../admin/bulkLabelSelectSuffix.php">etichetare în masă a lexemelor</a>

          <span class="text-muted">
            pe baza sufixului
          </span>
        </p>
      </div>
    </div>
  {/if}

  {if User::can(User::PRIV_ADMIN)}
    <div class="panel panel-default">
      <div class="panel-heading">
        Înlocuiește în definiții
      </div>
      
      <div class="panel-body">
        <form class="form-horizontal" action="bulkReplace.php">
          <div class="form-group">
            <label class="control-label col-xs-1">caută</label>
            <div class="col-xs-5">
              <input class="form-control" type="text" name="search">
            </div>

            <label class="control-label col-xs-1">motor</label>
            <div class="col-xs-5">
              {include "bits/diffEngineDropDown.tpl" name="engine" canEdit=true}
            </div>
            
          </div>

          <div class="form-group">
            <label class="control-label col-xs-1">substituie cu</label>
            <div class="col-xs-5">
              <input class="form-control" type="text" name="replace">
            </div>
            <label class="control-label col-xs-1">detaliere</label>
            <div class="col-xs-5" id="granularity">
              {include "bits/diffGranularityRadio.tpl" name="granularity" canEdit=true selected=1}
            </div>
             <div class="col-xs-5" id="message" hidden="true">
              <p class="help-block">în cazul LDiff schimbarea detalierii se face apăsând Alt+Shift+W</p>
            </div>

          </div>

          <div class="form-group">
            <label class="control-label col-xs-1">sursa</label>
            <div class="col-xs-5">
              {include "bits/sourceDropDown.tpl" name="sourceId"}
            </div>

            <label class="control-label col-xs-1">rezultate</label>
            <div class="col-sm-2" id="maxaffected">
                <div class="input-group spinner">
                  <input type="numeric" name="maxaffected" readonly class="form-control" value="1000" min="100" max="1000" step="100">
                  <div class="input-group-btn-vertical">
                    <button class="btn btn-default" type="button"><i class="glyphicon glyphicon-chevron-up"></i></button>
                    <button class="btn btn-default" type="button"><i class="glyphicon glyphicon-chevron-down"></i></button>
                  </div>
                </div>
                <p class="help-block">Min 100 - Max 1000.</p>
            </div>
          </div>

    
          <div class="form-group">
            <div class="col-xs-5 col-xs-offset-1">
              <button type="submit" class="btn btn-primary" name="previewButton">
                previzualizează
              </button>
            </div>
          </div>
        </form>

        <p class="text-muted">
          Folosiți cu precauție această unealtă. Ea înlocuiește primul text cu al
          doilea în toate definițiile, făcând diferența între litere mari și mici
          (case-sensitive) și fără expresii regulate (textul este căutat ca
          atare). Vor fi modificate maximum 1.000 de definiții. Veți putea vedea
          lista de modificări propuse și să o acceptați.
        </p>
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">
        Legături
      </div>
      
      <table class="table table-condensed">
        <tr>
          <td><a href="{$wwwRoot}moderatori">moderatori</a></td>
          <td><a href="{$wwwRoot}surse">surse</a></td>
        </tr>
        <tr>
          <td><a href="{$wwwRoot}etichete">etichete</a></td>
          <td><a href="{$wwwRoot}tipuri-modele">tipuri de model</a></td>
        </tr>
        <tr>
          <td><a href="{$wwwRoot}flexiuni">flexiuni</a></td>
          <td><a href="{$wwwRoot}admin/ocrInput.php">adaugă definiții OCR</a></td>
        </tr>
        <tr>
          <td><a href="{$wwwRoot}admin/contribTotals">contorizare contribuții</a></td>
          <td></td>
        </tr>
      </table>
    </div>
  {/if}

  {if User::can(User::PRIV_EDIT + User::PRIV_DONATION)}
    <div class="panel panel-default">
      <div class="panel-heading">
        Unelte diverse
      </div>
      
      <div class="panel-body">
        <ul>
          {if User::can(User::PRIV_EDIT)}
            <li>
              <a href="../admin/deTool.php">reasociere D. Enciclopedic</a>
              <span class="text-muted">
                o interfață mai rapidă pentru asocierea de lexeme și modificarea modelelor
                acestora
              </span>
            </li>

            <li>
              <a href="../admin/placeAccents.php">plasarea asistată a accentelor</a>
              <span class="text-muted">
                pentru lexeme alese la întâmplare
              </span>
            </li>

            <li>
              <a href="{$wwwRoot}acuratete">verificarea acurateței editorilor</a>
            </li>
          {/if}

          {if User::can(User::PRIV_DONATION)}
            <li>
              <a href="{$wwwRoot}proceseaza-donatii">procesează donații</a>
            </li>
          {/if}
        </ul>
      </div>
    </div>
  {/if}

  {if User::can(User::PRIV_STRUCT)}
    <div class="panel panel-default">
      <div class="panel-heading">
        Structurare
      </div>
      
      <div class="panel-body">
        <ul>
          <li>
            <a href="structChooseEntry.php">Intrări ușor de structurat</a>
            <span class="text-muted">
              100 de cuvinte din DEX cu definiții cât mai scurte
            </span>
          </li>

          <li>
            <a href="lexemSearch.php?structStatus={Entry::STRUCT_STATUS_IN_PROGRESS}&amp;structuristId={User::getActiveId()}">
              Lexemele mele în curs de structurare
            </a>
          </li>

          <li>
            <a href="lexemSearch.php?structStatus={Entry::STRUCT_STATUS_IN_PROGRESS}&amp;structuristId={Entry::STRUCTURIST_ID_NONE}">
              Lexeme orfane
            </a>
            <span class="text-muted">
              în curs de structurare, fără structurist asignat
            </span>
          </li>
        </ul>
      </div>
    </div>
  {/if}

  {if User::can(User::PRIV_VISUAL)}
    <div class="panel panel-default">
      <div class="panel-heading">
        Dicționarul vizual
      </div>
      
      <div class="panel-body">
        <a href="{$wwwRoot}admin/visual.php">dicționarul vizual</a>
      </div>
    </div>
  {/if}

  {if User::can(User::PRIV_WOTD)}
    <div class="panel panel-default">
      <div class="panel-heading">
        Cuvântul + imaginea zilei
      </div>
      
      <div class="panel-body">
        <ul>
          <li><a href="wotdTable.php">cuvântul zilei</a></li>
          <li><a href="wotdImages.php">imaginea zilei</a></li>
          <li><a href="../autori-imagini.php">autori</a></li>
          <li><a href="../alocare-autori.php">alocarea autorilor</a></li>
        </ul>
      </div>
    </div>
  {/if}

  <script>
   $(adminIndexInit);
  </script>
{/block}
