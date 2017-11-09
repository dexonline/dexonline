{extends "layout-admin.tpl"}

{block "title"}Contribuie cu definiții{/block}

{block "content"}
  {$def=$def|default:null}
  {$previewDivContent=$previewDivContent|default:null}
  {$activate=$activate|default:false}

  <h3>Trimiteți o definiție</h3>

  {if !User::getActive()}
    <div class="alert alert-warning alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      Dacă doriți să primiți credit pentru definițiile trimise, vă recomandăm să vă <a href="{$wwwRoot}auth/login">autentificați</a>.
    </div>
  {/if}

  <form method="post">

    <div class="row">

      <div class="form-group col-md-6">
        <label for="sourceDropDown">Sursa</label>
        {include "bits/sourceDropDown.tpl" sources=$contribSources skipAnySource=1}

        <a href="surse">
          <i class="glyphicon glyphicon-list-alt" aria-hidden="true"></i>
          lista de surse acceptate
        </a>

        <a href="https://wiki.dexonline.ro/wiki/Ghidul_voluntarului" target="_blank">
          <i class="glyphicon glyphicon-book" aria-hidden="true"></i>
          instrucțiuni de formatare
        </a>
      </div>

      <div class="form-group col-md-6">
        <label for="lexemIds">Cuvântul definit</label>
        <select id="lexemIds" name="lexemIds[]" style="width: 100%" multiple>
          {foreach $lexemIds as $l}
            <option value="{$l}" selected></option>
          {/foreach}
        </select>
        {if User::can(User::PRIV_EDIT)}
          <div class="checkbox">
            <label title="altfel definiția va fi trimisă în starea temporară">
              <input type="checkbox"
                     name="activate"
                     value="1"
                     {if $activate}checked{/if}>
              activează direct definiția
            </label>
          </div>
        {/if}
      </div>

    </div>

    <div class="form-group">
      <label for="defTextarea">Definiția</label>
      <textarea class="form-control" id="defTextarea" name="def" rows="15" cols="90">{$def|escape}</textarea>
    </div>

    <button type="submit" name="send" class="btn btn-success">
      trimite
    </button>

    <button type="reset"
            onclick="return confirm('Confirmați ștergerea definiției?')"
            class="btn btn-danger">
      <i class="glyphicon glyphicon-trash"></i>
      șterge
    </button>

    <div class="btn-group pull-right" id="tinymceButtonWrapper">
      <button id="tinymceToggleButton"
              type="button"
              class="btn btn-default"
              data-other-text="ascunde TinyMCE"
              href="#"
              title="TinyMCE este un editor vizual (cu butoane de bold, italic etc.).">
        arată TinyMCE
      </button>
    </div>

  </form>

  <h3>Rezultat</h3>

  <div id="previewDiv" class="contribPreview">
    {if $previewDivContent}
      {$previewDivContent}
    {else}
      Aici puteți vedea rezultatul (se actualizează automat la fiecare 5 secunde).
    {/if}
  </div>

  <br>

  <h3>Exemplu</h3>

  <table class="table table-bordered">
    <tr>
      <th>Tastați...</th>
      <th>Pentru a obține...</th>
    </tr>
    <tr>
      <td>
        <tt>
          @HAIDUC'IE,@ $haiducii,$ #s. f.# @1.@ Luptă armată a unor cete de haiduci (@1@)
          împotriva asupritorilor, frecventă la sfârșitul evului mediu în țările românești și
          în Peninsula Balcanică. @2.@ Viață sau îndeletnicire de haiduc (@1@). @3.@ Purtare,
          deprindere de haiduc (@1@). - @Haiduc@ + #suf.# $-ie.$
        </tt>
      </td>

      <td>
        <b>HAIDUCÍE,</b> <i>haiducii,</i> <abbr class="abbrev" title="substantiv feminin">s. f.</abbr> <b>1.</b> Luptă armată a unor cete de haiduci (<b>1</b>)
        împotriva asupritorilor, frecventă la sfârșitul Evului Mediu în țările românești și în Peninsula Balcanică. <b>2.</b> Viață sau îndeletnicire de haiduc
        (<b>1</b>). <b>3.</b> Purtare, deprindere de haiduc (<b>1</b>). &#x2013; <b>Haiduc</b> + <abbr class="abbrev" title="sufix">suf.</abbr> <i>-ie.</i>
      </td>
    </tr>
  </table>
{/block}
