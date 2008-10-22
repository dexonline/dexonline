<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:47
         compiled from common/login.ihtml */ ?>
<?php if ($this->_tpl_vars['is_connected']): ?>
  Sunteţi deja conectat!
<?php elseif ($this->_tpl_vars['login'] && $this->_tpl_vars['error_code'] == OK): ?>
  <script language="javascript">location.replace("<?php echo $this->_tpl_vars['target']; ?>
");</script>
<?php elseif ($this->_tpl_vars['forget'] && $this->_tpl_vars['error_code'] == OK): ?>
  Parola a fost resetată. Verificaţi-vă poşta în câteva minute
  pentru a afla noua parolă, apoi <a href="login.php">reconectaţi-vă</a>.
<?php elseif ($this->_tpl_vars['forget'] && $this->_tpl_vars['error_code'] == ERR_NO_MESSAGE): ?>
  Parola a fost schimbată, dar nu v-am putut trimite emailul cu noua
  parolă. Trimiteţi-ne un email pentru a rezolva problema manual.
<?php else: ?>
  Pentru a vă conecta, introduceţi adresa de email sau numele de
  cont şi parola. Dacă v-aţi uitat parola, introduceţi doar adresa
  de email şi apăsaţi butonul "Mi-am uitat parola". O nouă parolă
  va fi generată şi trimisă pe adresa dumneavoastră de email.

  <?php if ($this->_tpl_vars['error_code'] != OK): ?>
    <p><font color=red><b>
    <?php if ($this->_tpl_vars['error_code'] == ERR_NO_NICK): ?>
      Trebuie să introduceţi adresa de email sau numele de cont.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_NO_PASS): ?>
      Trebuie să introduceţi parola.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_BAD_LOGIN): ?>
      Adresa, numele de cont sau parola sunt greşite.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_BAD_EMAIL): ?>
      Adresa <?php echo $this->_tpl_vars['nickOrEmail']; ?>
 nu este înregistrată în <i>DEX online</i>.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_NO_EMAIL): ?>
      Pentru a vă reseta parola, trebuie să vă introduceţi adresa de email.
    <?php endif; ?>
    </b></font>
  <?php endif; ?>

  <p><form name="loginForm" method="post" action="login.php">
  <table><tr>
    <td>Adresa de email sau numele de cont:</td>
    <td><input type="text" name="email" value="<?php echo $this->_tpl_vars['nickOrEmail']; ?>
" size=30></td>
  </tr><tr>
    <td>Parola:</td>
    <td><input type="password" name="password" value="<?php echo $this->_tpl_vars['password']; ?>
"
      size=30></td>
  </tr><tr>
    <td colspan=2 align=center>
      <input type=submit id="login" name="login" value="Conectare">
      <input type=submit name="forget" value="Mi-am uitat parola">
      <input type=hidden name="target" value="<?php echo $this->_tpl_vars['target']; ?>
">
    </td>
  </tr></table>
<?php endif; ?>