<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:53
         compiled from common/statusDropDown.ihtml */ ?>
<select name="<?php echo $this->_tpl_vars['name']; ?>
">
  <?php $_from = $this->_tpl_vars['statuses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['submitValue'] => $this->_tpl_vars['displayName']):
?>
    <option value="<?php echo $this->_tpl_vars['submitValue']; ?>
"
            <?php if ($this->_tpl_vars['submitValue'] == $this->_tpl_vars['selectedStatus']): ?>selected="selected"<?php endif; ?>>
      <?php echo $this->_tpl_vars['displayName']; ?>

    </option>
  <?php endforeach; endif; unset($_from); ?>
</select>