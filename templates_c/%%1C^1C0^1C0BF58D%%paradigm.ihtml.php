<?php /* Smarty version 2.6.18, created on 2007-10-05 15:00:58
         compiled from common/bits/paradigm.ihtml */ ?>

<?php if (! $this->_tpl_vars['modelType']): ?>
  <?php $this->assign('modelType', $this->_tpl_vars['lexem']->modelType); ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['modelType'] == 'T'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmT.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'I'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmI.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'A'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmA.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'],'title' => 'adjectiv')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'MF'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmA.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'],'title' => 'substantiv')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'M'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmN.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'],'baseInflId' => (@INFL_M_OFFSET),'title' => 'substantiv masculin')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'F'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmN.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'],'baseInflId' => (@INFL_F_OFFSET),'title' => 'substantiv feminin')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'N'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmN.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'],'baseInflId' => (@INFL_N_OFFSET),'title' => 'substantiv neutru')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'P'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmP.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'V'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmV.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'],'title' => 'verb')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['modelType'] == 'VT'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigmV.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'],'title' => 'verb tranzitiv')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
  <div class="lexem">
    Nu pot afişa flexiunea lexemului
    <span class="lexemName"><?php echo $this->_tpl_vars['lexem']->unaccented; ?>
</span>.
  </div>
<?php endif; ?>