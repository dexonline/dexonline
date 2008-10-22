<?php /* Smarty version 2.6.18, created on 2008-01-23 13:06:33
         compiled from common/randomTip.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'common/randomTip.ihtml', 6, false),)), $this); ?>
<div class="randomTip">
  <b>Sugestie:</b>

  <?php $this->assign('numHints', '7'); ?>
  <?php echo smarty_function_math(array('equation' => "rand(0, ".($this->_tpl_vars['numHints'])." - 1)",'assign' => 'rnd'), $this);?>


   <?php if ($this->_tpl_vars['rnd'] == 0): ?>
     Doriţi să vă instalaţi <i>DEX online</i> pe calculatorul de
     acasă?  Citiţi <a href="faq.php#offline">această secţiune</a> din
     pagina de informaţii.
   <?php elseif ($this->_tpl_vars['rnd'] == 1): ?>
     Puteţi face căutări cu <i>expresii regulate</i>, care sunt mult
     mai puternice decât căutarea unui singur cuvânt. De exemplu,
     căutarea <i>dif*</i> va lista toate cuvintele care încep cu
     <i>dif-</i>. Detalii <a href="faq.php#regexp">aici</a>.
   <?php elseif ($this->_tpl_vars['rnd'] == 2): ?>
     Există mirror-uri (copii) ale <i>DEX online</i> care ar putea fi
     mai rapide pentru dumneavoastră decât site-ul principal. Detalii
     <a href="faq.php#mirrors">aici</a>.
   <?php elseif ($this->_tpl_vars['rnd'] == 3): ?>
     Dacă observaţi o greşeală de tipar într-o definiţie, ne-o puteţi
     semnala! După fiecare definiţie există o legătură în acest scop.
   <?php elseif ($this->_tpl_vars['rnd'] == 4): ?>
     Puteţi schimba aspectul paginii din pagina de <a
     href="tools.php">unelte</a> (unele designuri nu sunt complet
     funcţionale).
   <?php elseif ($this->_tpl_vars['rnd'] == 5): ?>
     Puteţi da click pe orice cuvânt din interiorul unei definiţii
     pentru a naviga la definiţia acelui cuvânt.
   <?php elseif ($this->_tpl_vars['rnd'] == 6): ?>
     Puteţi vedea flexiunea oricărui cuvânt (conjugarea verbelor sau
     declinarea substantivelor şi adjectivelor). După ce căutaţi un
     cuvânt, daţi click pe "Flexiuni".
   <?php endif; ?>
</div>