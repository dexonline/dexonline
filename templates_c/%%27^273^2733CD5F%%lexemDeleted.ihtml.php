<?php /* Smarty version 2.6.18, created on 2007-10-11 08:09:04
         compiled from admin/lexemDeleted.ihtml */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="../styles/flex.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="../js/dex.js"></script>
    <title>Confirmare ştergere lexem</title>
  </head>

  <body>
    Lexemul
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    a fost şters. Puteţi vizita unul dintre omonimele listate mai jos sau
    merge înapoi la <a href="../admin">pagina moderatorului</a>.
    <br/><br/>

    <?php $_from = $this->_tpl_vars['homonyms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['h']):
?>
      <?php if ($this->_tpl_vars['i']): ?>|<?php endif; ?>
      <a href="lexemEdit.php?lexemId=<?php echo $this->_tpl_vars['h']->id; ?>
"
        ><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['h'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></a>
    <?php endforeach; endif; unset($_from); ?>
  </body>
</html>