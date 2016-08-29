{extends file="layout.tpl"}

{block name=title}Contribuie cu definiții{/block}

{block name=banner}{/block}
{block name=search}{/block}

{block name=content}
  {assign var="def" value=$def|default:null}
  {assign var="previewDivContent" value=$previewDivContent|default:null}

  <h2>Trimiteți o definiție</h2>

  {if !$sUser}
    <div class="alert alert-warning alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      Dacă doriți să primiți credit pentru definițiile trimise, vă recomandăm să vă <a href="{$wwwRoot}auth/login">autentificați</a>.
    </div>
  {/if}

  <form id="frmContrib" name="frmContrib" method="post" action="contribuie">


    <div class="form-group">
      <label for="lexemIds">Cuvântul definit</label>
      <select id="lexemIds" name="lexemIds[]" style="width: 100%" multiple>
        {foreach $lexemIds as $l}
          <option value="{$l}" selected></option>
        {/foreach}
      </select>
    </div>

    <div class="form-group">
      <label for="sourceDropDown">Sursa</label>
      {include file="bits/sourceDropDown.tpl" sources=$contribSources src_selected=$sourceId skipAnySource=1}
      <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
      <a href="surse">lista de surse acceptate</a>
      <span class="glyphicon glyphicon-book" aria-hidden="true"></span>
      <a href="http://wiki.dexonline.ro/wiki/Ghidul_voluntarului" target="_blank">instrucțiuni de formatare</a>
    </div>

    <div class="form-group">
      <label for="defTextarea">Definiția</label>
      <textarea class="form-control" id="defTextarea" name="def" rows="15" cols="90">{$def|escape}</textarea>
    </div>

    <input type="submit" name="send" value="Trimite" class="btn btn-default" />
    <input type="reset" name="clear" value="Șterge" onclick="return confirm('Confirmați ștergerea definiției?')" class="btn btn-warning"/>
  </form>

  <h3>Rezultat</h3>

  <div id="previewDiv" class="contribPreview">
    {if $previewDivContent}
      {$previewDivContent}
    {else}
      Aici puteți vedea rezultatul (se actualizează automat la fiecare 5 secunde).
    {/if}
  </div>

  <br/>

  <h3>Exemplu</h3>

  <table class="table table-bordered">
    <tr>
      <th>Tastați...</th>
      <th>Pentru a obține...</th>
    </tr>
    <tr>
      <td>
        <tt>
          @HAIDUC'IE,@ $haiducii,$ #s. f.# @1.@ Lupt~a armat~a a unor cete de haiduci (@1@) ^impotriva asupritorilor, frecvent~a la
          sf^ar,situl evului mediu ^in ,t~arile rom^ane,sti ,si ^in Peninsula Balcanic~a. @2.@ Via,t~a sau ^indeletnicire de haiduc
          (@1@). @3.@ Purtare, deprindere de haiduc (@1@). - @Haiduc@ + #suf.# $-ie.$
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
