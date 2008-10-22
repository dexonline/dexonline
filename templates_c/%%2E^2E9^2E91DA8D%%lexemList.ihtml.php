<?php /* Smarty version 2.6.18, created on 2007-10-05 14:59:39
         compiled from admin/lexemList.ihtml */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="../styles/flex.css" rel="stylesheet" type="text/css"/>
    <title>DEX | <?php echo $this->_tpl_vars['sectionTitle']; ?>
</title>
  </head>

  <body>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/header.ihtml", 'smarty_include_vars' => array('title' => ($this->_tpl_vars['sectionTitle']),'items' => ($this->_tpl_vars['lexems']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/recentlyVisited.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    <?php $_from = $this->_tpl_vars['lexems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row_id'] => $this->_tpl_vars['l']):
?>
      <a href="<?php echo $this->_tpl_vars['wwwRoot']; ?>
/admin/lexemEdit.php?lexemId=<?php echo $this->_tpl_vars['l']->id; ?>
"
        ><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['l'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        (<?php echo $this->_tpl_vars['l']->modelType; ?>
<?php echo $this->_tpl_vars['l']->modelNumber; ?>
)</a>
    <?php endforeach; endif; unset($_from); ?>    

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/debugInfo.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  </body>

</html>