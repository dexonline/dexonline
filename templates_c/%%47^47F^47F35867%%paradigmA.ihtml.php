<?php /* Smarty version 2.6.18, created on 2007-11-12 09:08:39
         compiled from common/bits/paradigmA.ihtml */ ?>
<?php $this->assign('baseInflId', @INFL_A_OFFSET); ?>

<table class="lexem">
  <tr>
    <td colspan="2" rowspan="2">
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
    <td colspan="2" class="inflection">masculin</td>
    <td colspan="2" class="inflection">feminin</td>
  </tr>
  <tr>
    <td class="inflection">nearticulat</td>
    <td class="inflection">articulat</td>
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
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+8)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+12)); ?>
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
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+10)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+14)); ?>
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
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+9)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+13)); ?>
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
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+11)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+15)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
</table>