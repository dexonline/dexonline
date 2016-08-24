{extends file="layout.tpl"}

{block name=title}Pagina moderatorului{/block}

{block name=content}
  <h3>Pagina moderatorului</h3>

  {include file="bits/phpConstants.tpl"}

  {if $sUser->moderator & $smarty.const.PRIV_EDIT}
    <div class="panel panel-default quickNav">
      <div class="panel-heading">
        Navigare rapidă
      </div>

      <div class="panel-body">
        <form action="lexemEdit.php">
          <select id="lexemId" name="lexemId"></select>
        </form>

        <form action="definitionEdit.php">
          <select id="definitionId" name="definitionId"></select>
        </form>
      </div>
    </div>
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_EDIT}
    <div class="panel panel-default">
      <div class="panel-heading">
        Rapoarte
      </div>

      <ul class="list-group">
        {foreach from=$reports item=r}
          {if ($r.count != '0') && ($sUser->moderator & $r.privilege)}
            <li class="list-group-item">
              {$r.text}: <a href="{$wwwRoot}{$r.url}">{$r.count}</a>
            </li>
          {/if}
        {/foreach}
      </ul>
    </div>
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_EDIT}
    <div class="row">
      <div class="col-md-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            Caută definiții
          </div>

          <div class="panel-body">
            <form class="form-horizontal" action="definitionLookup.php" method="post">
              <div class="form-group">
                <label class="col-sm-3 control-label">lexicon</label>
                <div class="col-sm-9">
                  <input class="form-control" type="text" name="name" value="*">
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">starea</label>
                <div class="col-sm-9">
                  {include "bits/statusDropDown.tpl"
                  name="status"
                  selectedStatus=Definition::ST_PENDING}
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
                  {include file="bits/sourceDropDown.tpl" name="sourceId"}
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
                    Caută
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            Caută lexeme
          </div>

          <div class="panel-body">
            <form class="form-horizontal" action="lexemSearch.php">
              <div class="form-group">
                <label class="col-sm-3 control-label">forma</label>
                <div class="col-sm-9">
                  <input class="form-control" type="text" name="form"
                         placeholder="acceptă expresii regulate">
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">sursa</label>
                <div class="col-sm-9">
                  {include file="bits/sourceDropDown.tpl"}
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
                  {include file="bits/structStatus.tpl" canEdit=true anyOption=true}
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">structurist</label>
                <div class="col-sm-9">
                  <select id="structuristId" name="structuristId"></select>
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
                    Caută
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_EDIT}
    <h4>Modele de flexiune</h4>

    <form action="dispatchModelAction.php" method="get">
      <span data-model-dropdown>
        Modelul:
        <input type="hidden" name="locVersion" value="6.0" data-loc-version>
        <select name="modelType" data-model-type data-canonical="1"></select>
        <select name="modelNumber" data-model-number></select>
      </span>
      <input type="submit" name="showLexems" value="Arată toate lexemele">
      <input type="submit" name="editModel" value="Editează">
      <input type="submit" name="cloneModel" value="Clonează">

      <span class="tooltip2" title="În loc să permitem crearea de la zero a unui model nou, care probabil nu ar fi prea utilă, permitem clonarea unui
                                    model deja existent. Noul model va avea aceleași flexiuni, același exponent și (în cazul verbelor) același tip de participiu cu modelul
                                    original. Trebuie să indicați doar un nou număr de model. Opțional, puteți alege lexemele etichetate cu modelul original pe care doriți să le
                                    migrați la modelul-clonă.">&nbsp;</span>

      <input type="submit" name="deleteModel" value="Șterge">

      <span class="tooltip2" title="Când ștergeți un model, toate lexemele etichetate cu acel model vor fi reetichetate cu modelul T1. (Vă va fi
                                    prezentat un ecran de confirmare cu lista acestor lexeme). Probabil este de dorit să reetichetați din timp aceste lexeme cu modelele
                                    corespunzătoare, astfel ca în momentul ștergerii modelul să nu mai aibă niciun lexem.">&nbsp;</span>

    </form>
    <br>

    <a href="../admin/mergeLexems.php">unificare plural-singular</a>

    <div class="flexExplanation">
      Această unificare se pretează în special la familiile de plante
      și animale, dar și la alte lexeme care apar cu restricția „P”
      într-o sursă, dar fără restricții în altă sursă.
    </div>
    <br>

    <a href="../admin/bulkLabelSelectSuffix.php">etichetare în masă a lexemelor</a>

    <div class="flexExplanation">
      Rostul acestei pagini este de a facilita etichetarea în masă a
      lexemelor care există în <i>dexonline</i>, dar nu și în LOC, pe baza
      sufixelor. De exemplu, există sute de lexeme neetichetate
      terminate în „-tate”. Există și 900 de lexeme din LOC terminate
      în „-tate” și absolut toate au modelul F117, deci aproape sigur
      și cele noi vor fi etichetate cu același model. Rolul
      operatorului uman este să identifice excepțiile și să indice
      eventualele restricții de flexionare.
    </div>
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_ADMIN}
    <h4>Unelte pentru administratori</h4>

    <form action="bulkReplace.php" method="get">
      Înlocuiește în definiții: <input type="text" name="search" size="25">
      cu <input type="text" name="replace" size="25">
      în sursa: {include file="bits/sourceDropDown.tpl"}
      <input type="submit" name="previewButton" value="Previzualizează" onclick="return hideSubmitButton(this)">
    </form>
    <div class="flexExplanation">
      Folosiți cu precauție această unealtă. Ea înlocuiește primul text cu al
      doilea în toate definițiile, făcând diferența între litere mari și mici
      (case-sensitive) și fără expresii regulate (textul este căutat ca
      atare). Vor fi modificate maximum 1.000 de definiții. Veți putea vedea
      lista de modificări propuse și să o acceptați. Din păcate, nu avem
      posibilitatea să subliniem exact porțiunile din text modificate.
    </div>
    <br>
    <a href="{$wwwRoot}moderatori">moderatori</a> |
    <a href="{$wwwRoot}surse">surse</a> |
    <a href="{$wwwRoot}etichete">etichete</a> |
    <a href="{$wwwRoot}tipuri-modele">tipuri de model</a> |
    <a href="{$wwwRoot}flexiuni">flexiuni</a> |
    <a href="{$wwwRoot}acuratete">verificarea acurateței editorilor</a> |
    <a href="{$wwwRoot}admin/ocrInput.php">adaugă definiții OCR</a>
    <br>
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_EDIT}
    <h4>Unelte</h4>

    Pentru a reasocia cuvinte din D. Enciclopedic cu lexemele corecte, <a href="../admin/deTool.php">clic aici</a>.

    <div class="flexExplanation">
      Este o pagină care încearcă să faciliteze asocierea de lexeme și
      modificarea modelelor acestora, reducând numărul de clicuri necesare.
    </div>
    <br>

    Pentru a încerca plasarea asistată a accentelor,
    <a href="../admin/placeAccents.php">clic aici</a>.

    <div class="flexExplanation">
      Veți primi o pagină cu 10 lexeme alese la întâmplare (deocamdată
      avem de unde alege...) pentru care puteți indica unde pică accentul.
    </div>
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_STRUCT}
    <h4>Structurare</h4>

    <a href="structChooseEntry.php">Intrări ușor de structurat</a>
    (100 de cuvinte din DEX cu definiții cât mai scurte)
    <br>

    <a href="lexemSearch.php?structStatus={Entry::STRUCT_STATUS_IN_PROGRESS}&amp;structuristId={$sUser->id}">
      Lexemele mele în curs de structurare
    </a>
    <br>

    <a href="lexemSearch.php?structStatus={Entry::STRUCT_STATUS_IN_PROGRESS}&amp;structuristId=-1">
      Lexeme orfane
    </a>
    (în curs de structurare, fără structurist asignat)
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_VISUAL}
    <h4>Dicționarul vizual</h4>

    <a href="{$wwwRoot}admin/visual.php">dicționarul vizual</a>
  {/if}

  {if $sUser->moderator & $smarty.const.PRIV_WOTD}
    <h4>Cuvântul + imaginea zilei</h4>

    <a href="wotdTable.php">cuvântul zilei</a> |
    <a href="wotdImages.php">imaginea zilei</a> |
    <a href="../autori-imagini.php">autori</a> |
    <a href="../alocare-autori.php">alocarea autorilor</a>
  {/if}

  <script>
   $(adminIndexInit);
  </script>
{/block}
