{assign var="def" value=$def|default:null}
{assign var="previewDivContent" value=$previewDivContent|default:null}

<p class="paragraphTitle">Trimiteți o definiție</p>

{if !$sUser}
  <div class="warning">
    Dacă doriți să primiți credit pentru definițiile trimise, vă recomandăm să vă <a href="{$wwwRoot}auth/login">autentificați</a>.
  </div>
{/if}

<form id="frmContrib" name="frmContrib" method="post" action="contribuie">
  <table id="defUserEdit">

    <tr>
      <td><p class="labelContribute">Cuvântul definit:</p></td>
      <td>
        <input id="lexemIds" name="lexemIds" value="{','|implode:$lexemIds}" type="text"/>
      </td>
    </tr>

    <tr>
      <td><p class="labelContribute">Sursa:</p></td>
      <td>
        {include file="sourceDropDown.tpl" sources=$contribSources src_selected=$sourceId skipAnySource=1}
        <a href="surse">lista de surse acceptate</a>
        <div id="formattingLink"><a href="http://wiki.dexonline.ro/wiki/Ghidul_voluntarului" target="_blank">instrucțiuni de formatare</a></div>
      </td>
    </tr>

    <tr>
      <td><p class="labelContribute">Definiția:</p></td>
      <td><textarea id="defTextarea" name="def" rows="15" cols="90" onkeypress="contribKeyPressed()">{$def|escape}</textarea></td>
    </tr>

    <tr>
      <td colspan="2">
        <input type="submit" name="send" value="Trimite"/>
        <input type="reset" name="clear" value="Șterge" onclick="return confirm('Confirmați ștergerea definiției?')"/>
      </td>
    </tr>
  </table>
</form>

<p class="paragraphTitle">Rezultat</p>

<div id="previewDiv" class="contribPreview">
  {if $previewDivContent}
    {$previewDivContent}
  {else}
    Aici puteți vedea rezultatul (se actualizează automat la fiecare 5 secunde).
  {/if}
</div>

<br/>

<p class="paragraphTitle">Exemplu</p>

<table class="contribExample">
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

<script type="text/javascript">$(contribBodyLoad);</script>
<script type="text/javascript">$(contribInit);</script>
