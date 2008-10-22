<?php /* Smarty version 2.6.19, created on 2008-06-11 10:45:51
         compiled from common/bits/paradigmN.ihtml */ ?>
<table class="lexem">
  <tr>
    <td colspan="2">
      <span class="lexemName"
        ><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></span>
      &nbsp; <?php echo $this->_tpl_vars['title']; ?>

      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/locInfo.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="inflection">nearticulat</td>
    <td class="inflection">articulat</td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">nominativ-acuzativ</td>
    <td class="inflection">singular</td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId'])); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+4)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td class="inflection">plural</td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+2)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+6)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">genitiv-dativ</td>
    <td class="inflection">singular</td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+1)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+5)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td class="inflection">plural</td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+3)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+7)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
</table>