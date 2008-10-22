<?php /* Smarty version 2.6.18, created on 2007-11-12 09:08:26
         compiled from polar/pageLayout.ihtml */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="<?php echo $this->_tpl_vars['cssRoot']; ?>
/polar.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $this->_tpl_vars['cssRoot']; ?>
/paradigm.css" rel="stylesheet" type="text/css"/>
    <title><?php echo $this->_tpl_vars['page_title']; ?>
</title>
    <script type="text/javascript" src="js/dex.js"></script>
  </head>

  <body id="polarSkinBody"
        <?php if ($this->_tpl_vars['show_search_box'] && $this->_tpl_vars['focus_search_box']): ?>
          onload="document.frm.cuv.focus()"
        <?php endif; ?>>

    <table class="header">
      <tr>
        <td class="searchBoxContainer">
          <div class="linkBar">
            <a href="faq.php">Informaţii</a> |
            <a href="contrib.php">Contribuie</a> |
            <a href="corect.php" id="guideLink">Ghid de exprimare</a> |
            <a href="tools.php">Unelte</a> |
            <a href="top.php">Top</a> |            
            <a href="contact.php">Contact</a>
          </div>
      
          <form action="search.php" name="frm" class="searchForm">
            <input type="text" name="cuv" value="<?php echo $this->_tpl_vars['cuv']; ?>
" size="20"
              maxlength="50"/>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/sourceDropDown.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <input type="submit" value="Caută"/>
            <br/>
            <span class="definitionBodyCheckbox">
              <input type="checkbox" id="defBody"
                     name="text" value="1"
                     <?php if ($this->_tpl_vars['text']): ?>checked="checked"<?php endif; ?>
              /><label for="defBody">Caută în tot textul definiţiilor</label>
              (<a href="faq.php#fulltext">explicaţie</a>)
            </span>
          </form>
        </td>
        <td class="title">
          <span class="pageTitle">DEX online</span><br/>
          Dicţionare ale limbii române<br/>
          <span class="formPlug">Peste <?php echo $this->_tpl_vars['words_rough']; ?>
 de definiţii</span>
        </td>
        <td class="logo">
          <a href="<?php echo $this->_tpl_vars['wwwRoot']; ?>
/" class="noBorder">
            <img src="<?php echo $this->_tpl_vars['imgRoot']; ?>
/polar/dexonline.png" alt="DEX online logo"
                 class="logo"/></a>
        </td>
      </tr>
    </table>
      
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['contentTemplateName'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    <table class="footer">
      <tr>
        <td>
          Starea curentă: <b><?php echo $this->_tpl_vars['words_total']; ?>
</b> de definiţii, din care
          <b><?php echo $this->_tpl_vars['words_last_month']; ?>
</b> învăţate în ultima lună.<br/>

          <?php if (! $this->_tpl_vars['is_mirror']): ?>
            Utilizator: <b id="user.nick"><?php echo $this->_tpl_vars['nick']; ?>
</b>
            <?php if ($this->_tpl_vars['is_connected'] && $this->_tpl_vars['is_moderator']): ?>
              | <a href="admin">Pagina moderatorului</a>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['is_connected']): ?>
              | <a href="account.php">Contul meu</a>
              | <a href="logout.php" id="logoutLink">Deconectare</a>
            <?php else: ?>
              | <a href="login.php" id="loginLink">Conectare</a>
              | <a href="signup.php">Înregistrare</a>
            <?php endif; ?>
            <br/>
          <?php endif; ?>

          Copyright (C) 2004-2007 DEX online. Copierea definiţiilor
          este permisă sub <a
          href="<?php echo $this->_tpl_vars['wwwRoot']; ?>
/faq.php#licenta">licenţa GPL</a>, cu
          condiţia păstrării acestei note.

        </td>
        <td class="narrow">
          <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/trafic_ro.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>
        <?php if ($this->_tpl_vars['hostedBy']): ?>
          <td class="narrow">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/hosting/".($this->_tpl_vars['hostedBy']).".ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        <?php endif; ?>
      </tr>
    </table>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/debugInfo.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  </body>
</html>