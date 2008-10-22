<?php /* Smarty version 2.6.18, created on 2007-10-05 14:59:39
         compiled from common/bits/lexemName.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'common/bits/lexemName.ihtml', 1, false),)), $this); ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['lexem']->unaccented)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
<?php if ($this->_tpl_vars['lexem']->description): ?> (<?php echo $this->_tpl_vars['lexem']->description; ?>
)<?php endif; ?><?php if (! $this->_tpl_vars['lexem']->unaccented && ! $this->_tpl_vars['lexem']->description): ?>[ID = <?php echo $this->_tpl_vars['lexem']->id; ?>
]<?php endif; ?>