<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:53
         compiled from admin/lexemPicker.ihtml */ ?>
<input type="text" name="<?php echo $this->_tpl_vars['fieldName']; ?>
" value="<?php echo $this->_tpl_vars['displayValue']; ?>
" size="40"
       onkeydown="return lexemPickerKeyEvent(this, event);"
       onblur="return lexemPickerBlur(this);"
       onchange="return lexemPickerChange(this);"
       autocomplete="off"/>
<input type="hidden" name="<?php echo $this->_tpl_vars['submitName']; ?>
" value="<?php echo $this->_tpl_vars['submitValue']; ?>
"/>
<?php if ($this->_tpl_vars['editLinkId']): ?>
  <a href="<?php echo $this->_tpl_vars['wwwRoot']; ?>
/admin/lexemEdit.php?lexemId=<?php echo $this->_tpl_vars['editLinkId']; ?>
">editează</a> |
<?php endif; ?>
<?php if ($this->_tpl_vars['dissociateLink']): ?>
  <a href="#" onclick="return deleteLexemRow(this)">disociază</a>
<?php endif; ?>
<table class="picker"></table>