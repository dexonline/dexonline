<?php /* Smarty version 2.6.18, created on 2008-01-23 13:06:33
         compiled from common/sourceDropDown.ihtml */ ?>
<select name="source" id="sourceDropDown">
  <?php if (! $this->_tpl_vars['skipAnySource']): ?>
    <option value="">Toate sursele</option>
  <?php endif; ?>
  <?php $_from = $this->_tpl_vars['sources']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['source']):
?>
    <option value="<?php echo $this->_tpl_vars['source']->id; ?>
"
      <?php if ($this->_tpl_vars['src_selected'] == $this->_tpl_vars['source']->id): ?>selected="selected"<?php endif; ?>
      ><?php echo $this->_tpl_vars['source']->shortName; ?>
</option>
  <?php endforeach; endif; unset($_from); ?>
</select>