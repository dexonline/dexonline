<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:45
         compiled from common/errorMessage.ihtml */ ?>
<?php if ($this->_tpl_vars['errorMessage']): ?>
  <table class="errorMessage">
    <?php if (is_array ( $this->_tpl_vars['errorMessage'] )): ?>
      <?php $_from = $this->_tpl_vars['errorMessage']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['em']):
?>
        <tr><td><?php echo $this->_tpl_vars['em']; ?>
</td></tr>
      <?php endforeach; endif; unset($_from); ?>
    <?php else: ?>
      <tr><td><?php echo $this->_tpl_vars['errorMessage']; ?>
</td></tr>
    <?php endif; ?>
  </table>
<?php endif; ?>