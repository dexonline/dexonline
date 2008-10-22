<?php /* Smarty version 2.6.18, created on 2007-10-11 08:12:29
         compiled from common/contrib.ihtml */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="styles/simple.css" rel="stylesheet" type="text/css"/>
    <title>Contribuie la DEX!</title>
    <script type="text/javascript" src="js/dex.js"></script>
  </head>

<body onload="contribBodyLoad()">
  <div class="contribSection">
    <div class="contribLinks">
      <a href="javascript:formatwindow()">Instrucţiuni</a>
      &nbsp; | &nbsp;
      <a href="index.php">Pagina principală</a>
    </div>
    Trimiteţi o definiţie &nbsp;&nbsp;
  </div>

  <form name="frm" method="post" action="contrib.php">
    <table>
      <?php if ($this->_tpl_vars['errorMessage']): ?>
        <tr>
          <td colspan="2">
            <span class="errorMessage"><?php echo $this->_tpl_vars['errorMessage']; ?>
</span>
          </td>
        </tr>
      <?php endif; ?>
      <?php if ($this->_tpl_vars['submissionSuccessful']): ?>
        <tr>
          <td colspan="2">
            <span class="confirmationMessage">
              Definiţia a fost trimisă! Un moderator o va examina în
              scurt timp. Vă mulţumim!
            </span>
          </td>
        </tr>
      <?php endif; ?>
      <tr>
        <td>Utilizator:</td>
        <td><b><?php echo $this->_tpl_vars['nick']; ?>
</b></td>
      </tr>
      <?php if (! $this->_tpl_vars['is_connected']): ?>
        <tr>
          <td>&nbsp;</td>
          <td>
            Definiţia dumneavoastră va fi trimisă, dar nu veţi primi credit
            (laude, glorie) pentru ea. Vă recomandăm să vă
            <a href="login.php?target=contrib.php">conectaţi</a>
            sau, dacă nu aveţi un cont pe <i>DEX online</i>, să vă
            <a href="signup.php">înscrieţi</a>.
          </td>
        </tr>
      <?php endif; ?>

      <tr>
        <td style="white-space: nowrap">
          Cuvântul (cu diacritice):
        </td>
        <td>
          <input name="wordName" value="<?php echo $this->_tpl_vars['wordName']; ?>
" size="24"/>
        </td>
      </tr>

      <tr>
        <td><a href="faq.php#surse">Sursa:</a></td>
        <td>
          <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/sourceDropDown.ihtml", 'smarty_include_vars' => array('sources' => $this->_tpl_vars['contribSources'],'src_selected' => $this->_tpl_vars['sourceId'],'skipAnySource' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>
      </tr>
    </table>

    Definiţia:<br/>
      <textarea name="def" rows="10" cols="80"
                onkeypress="contribKeyPressed()"><?php echo $this->_tpl_vars['def']; ?>
</textarea><br/>
      <input type="submit" name="send" value="Trimite"/>
      <input type="reset" name="clear" value="Şterge"
             onclick="return confirm('Confirmaţi ştergerea definiţiei?')"/>
  </form>

  <div class="contribSection">
    Rezultat
  </div>

  <div id="previewDiv" class="previewDiv">
    <?php if ($this->_tpl_vars['previewDivContent']): ?>
      <?php echo $this->_tpl_vars['previewDivContent']; ?>

    <?php else: ?>
      Aici puteţi vedea rezultatul (se actualizează automat la fiecare
      5 secunde).
    <?php endif; ?>
  </div>

  <br/>

  <div class="contribSection">
    Exemplu
  </div>

  <table class="contribExample">
    <tr>
      <td>
        <tt>
          @HAIDUC'IE,@ $haiducii,$ s.f. @1.@ |Lupt~a|| armat~a a unor cete de
          |haiduci (@1@)|haiduc| ^impotriva |asupritorilor|-|, frecvent~a la
          sf^ar,situl |evului mediu|ev| ^in ,t~arile rom^ane,sti ,si ^in
          Peninsula Balcanic~a. @2.@ Via,t~a sau ^indeletnicire de haiduc
          (@1@). @3.@ Purtare, deprindere de haiduc (@1@). - @Haiduc@ +
          suf. $-ie.$
        </tt>
      </td>

      <td>
        <b>HAIDUC&#xcd;E,</b> <i>haiducii,</i> s.f. <b>1.</b> <a class="ref"
        href="/search.php?cuv=lupta">Luptă</a> armată a unor cete
        de <a class="ref" href="/search.php?cuv=haiduc">haiduci (<b>1</b>)</a>
        împotriva <a class="ref"
        href="/search.php?cuv=asupritor">asupritorilor</a>, frecventă
        la sfârşitul <a class="ref" href="/search.php?cuv=ev">evului
        mediu</a> în ţările româneşti şi
        în Peninsula Balcanică. <b>2.</b> Viaţă sau
        îndeletnicire de haiduc (<b>1</b>). <b>3.</b> Purtare, deprindere
        de haiduc (<b>1</b>). &#x2013; <b>Haiduc</b> + suf. <i>-ie.</i>
      </td>
    </tr>
  </table>

  <br/>

  <div class="contribSection">
    Atenţie!
  </div>

  Vă rugăm insistent să trimiteţi numai definiţii din sursele
  acceptate. <b>Nu putem accepta definiţii provenite din opinia
  dumneavoastră personală</b>. Aceasta ar duce la scăderea
  credibilităţii <i>DEX online</i> sau ar putea omite sensuri
  secundare ale cuvântului. Vă rugăm să respectaţi pe cât posibil
  instrucţiunile de formatare, în special simbolurile diacritice. Dacă
  efortul necesar corectării definiţiei pe care ne-o trimiteţi este
  mai mare decât tastarea ei de la zero, bunăvoinţa dumneavoastră este
  irosită. Definiţiile care nu se conformează acestor rugăminţi vor fi
  respinse. Întrucât numărul de voluntari a crescut considerabil în
  ultima vreme, regretăm că nu putem garanta un răspuns individual.

</body>
</html>