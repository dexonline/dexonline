<?php /* Smarty version 2.6.18, created on 2007-10-05 15:00:58
         compiled from common/bits/wlArray.ihtml */ ?>

<?php if (count ( $this->_tpl_vars['wlArray'] ) == 0): ?>
  &mdash;
<?php else: ?>
  <?php $_from = $this->_tpl_vars['wlArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['wl']):
?><?php if ($this->_tpl_vars['i']): ?>, <?php endif; ?><?php echo $this->_tpl_vars['wl']->form; ?>
<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>