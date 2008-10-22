<?php /* Smarty version 2.6.18, created on 2007-10-11 05:38:06
         compiled from admin/index.ihtml */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="../styles/flex.css" rel="stylesheet" type="text/css"/>
    <link href="../styles/lexemPicker.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="../js/dex.js"></script>
    <script type="text/javascript" src="../js/flex.js"></script>
    <script type="text/javascript" src="../js/lexemPicker.js"></script>
    <title>DEX | Pagina moderatorului</title>
  </head>

  <body>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/header.ihtml", 'smarty_include_vars' => array('title' => "DEX online - Pagina moderatorului")));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/recentlyVisited.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    Aici găsiţi, deocamdată, un sumar al problemelor din baza de date
    care necesită atenţie şi câteva mecanisme de căutare a lexemelor
    şi definiţiilor.<br/><br/>

    <?php if ($this->_tpl_vars['numUnassociatedLexems']): ?>
      <a href="viewUnassociatedLexems.php"
       >Lexeme neasociate cu nici o definiţie: <?php echo $this->_tpl_vars['numUnassociatedLexems']; ?>
</a>
    <?php else: ?>
      Lexeme neasociate cu nici o definiţie: 0
    <?php endif; ?>
    <br/>

    <?php if ($this->_tpl_vars['numUnassociatedDefinitions']): ?>
      <a href="viewUnassociatedDefinitions.php"
       >Definiţii neasociate cu nici un lexem:
       <?php echo $this->_tpl_vars['numUnassociatedDefinitions']; ?>
</a>
    <?php else: ?>
      Definiţii neasociate cu nici un lexem: 0
    <?php endif; ?>
    <br/>

    <?php if ($this->_tpl_vars['numDefinitionsWithTypos']): ?>
      <a href="viewTypos.php">Definiţii cu greşeli de tipar:
      <?php echo $this->_tpl_vars['numDefinitionsWithTypos']; ?>
</a>
    <?php else: ?>
      Definiţii cu greşeli de tipar: 0
    <?php endif; ?>
    <br/>

    <?php if ($this->_tpl_vars['numTemporaryDefinitions']): ?>
      <a href="viewPendingDefinitions.php"
        >Definiţii nemoderate: <?php echo $this->_tpl_vars['numTemporaryDefinitions']; ?>
</a>
    <?php else: ?>
      Definiţii nemoderate: 0
    <?php endif; ?>
    <br/>

    <?php if ($this->_tpl_vars['is_flex_moderator']): ?>  
      <?php if ($this->_tpl_vars['numTemporaryLexems']): ?>
        <a href="../flex/viewTemporaryLexems.php"
          >Lexeme fără paradigme: <?php echo $this->_tpl_vars['numTemporaryLexems']; ?>
</a>
      <?php else: ?>
        Lexeme fără paradigme: 0
      <?php endif; ?>
      <br/>

      <?php if ($this->_tpl_vars['numLexemsWithComments']): ?>
        <a href="../flex/viewLexemsWithComments.php"
          >Lexeme cu comentarii: <?php echo $this->_tpl_vars['numLexemsWithComments']; ?>
</a>
      <?php else: ?>
        Lexeme cu comentarii: 0
      <?php endif; ?>
      <br/>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['numLexemsWithoutAccents']): ?>
      <a href="../flex/viewLexemsWithoutAccents.php"
        >Lexeme fără accent: <?php echo $this->_tpl_vars['numLexemsWithoutAccents']; ?>
</a>
    <?php else: ?>
      Lexeme fără accent: 0
    <?php endif; ?>
    <br/>

    <br/>
    <form action="lexemLookup.php" method="get">
      <table>
        <tr>
          <td>Caută lexem:</td>
          <td>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/lexemPicker.ihtml", 'smarty_include_vars' => array('fieldName' => 'lexemName')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <input type="submit" name="searchLexem" value="Caută"/>
          </td>
        </tr>
      </table>
    </form>
    <br/>

    <?php if ($this->_tpl_vars['is_flex_moderator']): ?>
      <form action="../flex/dispatchModelAction.php" method="get">
        Modelul:
        <select name="modelType"
                onchange="return updateModelList(this, 'modelListId')">
          <?php $_from = $this->_tpl_vars['modelTypes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mt']):
?>
            <option value="<?php echo $this->_tpl_vars['mt']->value; ?>
"><?php echo $this->_tpl_vars['mt']->value; ?>
</option>
          <?php endforeach; endif; unset($_from); ?>
        </select>
        <select name="modelNumber" id="modelListId">
          <?php $_from = $this->_tpl_vars['models']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?>
            <option value="<?php echo $this->_tpl_vars['m']->number; ?>
"
              <?php if ($this->_tpl_vars['m']->number == $this->_tpl_vars['model']->number): ?>selected="selected"<?php endif; ?>
              ><?php echo $this->_tpl_vars['m']->number; ?>
<?php if (! $this->_tpl_vars['m']->id): ?>*<?php endif; ?> (<?php echo $this->_tpl_vars['m']->exponent; ?>
)
            </option>
          <?php endforeach; endif; unset($_from); ?>
        </select>
        <input type="submit" name="showLexems" value="Arată toate lexemele"/>
        <input type="submit" name="editModel" value="Editează"/>
        <input type="submit" name="cloneModel" value="Clonează"/>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'modelClone')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      </form>
      <br/>
    <?php endif; ?>

    <form action="definitionLookup.php" method="post">
      Caută definiţii:
      <input type="text" name="name" size="10" value="*"/>
      &nbsp; &nbsp; starea:
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/statusDropDown.ihtml", 'smarty_include_vars' => array('name' => 'status','statuses' => $this->_tpl_vars['allStatuses'],'selectedStatus' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      &nbsp; &nbsp; trimise de:
      <input type="text" name="nick" size="10" value=""/>
      &nbsp; &nbsp; sursa:
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/sourceDropDown.ihtml", 'smarty_include_vars' => array('sources' => $this->_tpl_vars['allModeratorSources'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      <br/>
      &nbsp; &nbsp; &nbsp; &nbsp; între
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/numericDropDown.ihtml", 'smarty_include_vars' => array('name' => 'yr1','start' => 2001,'end' => 2008)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/numericDropDown.ihtml", 'smarty_include_vars' => array('name' => 'mo1','start' => 1,'end' => 13)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/numericDropDown.ihtml", 'smarty_include_vars' => array('name' => 'da1','start' => 1,'end' => 32)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      &nbsp; &nbsp; şi
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/numericDropDown.ihtml", 'smarty_include_vars' => array('name' => 'yr2','start' => 2001,'end' => 2008,'selected' => 2007)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/numericDropDown.ihtml", 'smarty_include_vars' => array('name' => 'mo2','start' => 1,'end' => 13,'selected' => 12)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/numericDropDown.ihtml", 'smarty_include_vars' => array('name' => 'da2','start' => 1,'end' => 32,'selected' => 31)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      &nbsp; &nbsp; 
      <input type="submit" name="searchButton" value="Caută"/>
    </form>
    <br/>

    <?php if ($this->_tpl_vars['is_flex_moderator']): ?>  
      Pentru a verifica corectitudinea paradigmelor,
      <a href="../flex/verifyParadigms.php">click aici</a>.
      <div class="flexExplanation">
        Rostul acestei pagini este de a verifica prin inspecţie
        corectitudinea modelelor desfăşurate extrase de scriptul lui
        Radu din LOC3/4. Pagina vă oferă maxim 4 lexeme din fiecare
        model, pe care încearcă să le aleagă cu sufixe cât mai
        diferite. După ce trecem prin toate modelele şi le etichetăm
        drept corecte sau greşite, îi trimitem lui Radu lista de greşeli
        descoperite, pentru ca el să-şi poată îmbunătăţi scriptul de
        extragere a modelelor.
      </div>
      <br/>
  
      Pentru a încerca etichetarea asistată a cuvintelor,
      <a href="../flex/bulkLabelSelectSuffix.php">click aici</a>.
  
      <div class="flexExplanation">
        Rostul acestei pagini este de a facilita etichetarea în masă a
        lexemelor care există în DEX online, dar nu şi în LOC, pe baza
        sufixelor. De exemplu, există sute de lexeme neetichetate
        terminate în „-tate”. Există şi 900 de lexeme din LOC terminate
        în „-tate” şi absolut toate au modelul F117, deci aproape sigur
        şi cele noi vor fi etichetate cu acelaşi model. Rolul
        operatorului uman este să identifice excepţiile şi să indice
        eventualele restricţii de flexionare.
      </div>
      <br/>

      Pentru a încerca plasarea asistată a accentelor,
      <a href="../flex/placeAccents.php">click aici</a>.
  
      <div class="flexExplanation">
        Veţi primi o pagină cu 10 lexeme alese la întâmplare (deocamdată
        avem de unde alege...) pentru care puteţi indica unde pică accentul.
      </div>
      <br/>
    <?php endif; ?>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/debugInfo.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  </body>

</html>