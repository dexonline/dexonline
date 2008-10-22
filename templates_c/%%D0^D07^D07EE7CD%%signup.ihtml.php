<?php /* Smarty version 2.6.18, created on 2007-10-11 08:00:12
         compiled from common/signup.ihtml */ ?>
<?php if ($this->_tpl_vars['is_connected']): ?>
  Sunteţi deja înregistrat şi conectat!
<?php elseif ($this->_tpl_vars['send'] && $this->_tpl_vars['error_code'] == OK): ?>
  Înscrierea s-a făcut cu succes! Acum vă puteţi
  <a href="login.php">conecta</a>.
<?php else: ?>
  <p>Dacă nu intenţionaţi să ne trimiteţi
  definiţii, <b>nu este nevoie să vă înscrieţi</b>.
  Puteţi consulta <i>DEX online</i> după pofta inimii şi ca
  utilizator anonim. Ce aveţi de câştigat dacă vă
  înscrieţi:

  <p><ul>
  <li>Definiţiile pe care le trimiteţi se vor adăuga în
  contul dumneavoastră, aducându-vă un loc mai bun în topul
  voluntarilor si multă recunoştinţă din partea
  utilizatorilor :) Altfel, definiţiile sunt trimise cu titlu anonim.

  <li>După ce contribuiţi cu cel puţin 100 de definiţii,
  puteţi cere un cont de moderator. Acesta vă dă dreptul de a
  accepta/respinge definiţii trimise de alţii, de a corecta
  greşeli de tipar ş.a.m.d.
  </ul>

  <p><b>Avem nevoie de adresa dumneavoastră de email</b> pentru a putea
  ţine legătura pe marginea definiţiilor pe care ni le
  trimiteţi. Aveţi opţiunea de a vă face adresa de email
  invizibilă tuturor în afară de administratorul <i>DEX
  online</i>. Nu aveţi nici o grijă, nu vom divulga adresa de email
  nimănui dacă dumneavoastră nu doriţi asta. Şi noi
  primim mesaje nesolicitate (spam) şi ştim ce "plăcere" este.

  <p>Acestea fiind zise...

  <hr/>

    <?php if ($this->_tpl_vars['send']): ?>
    <font color=red><b>
    <?php if ($this->_tpl_vars['error_code'] == ERR_NO_NICK): ?>
      Trebuie să vă alegeţi un nume de cont.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_NICK_LEN): ?>
      Numele de cont trebuie să aibă minim 3 caractere.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_NICK_CHARS): ?>
      Numele de cont poate conţine numai caracterele indicate.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_PASS_LEN): ?>
      Trebuie să vă alegeţi o parolă de minim 4 caractere.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_PASS_MISMATCH): ?>
      Parolele nu coincid.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_NO_EMAIL): ?>
      Trebuie să precizaţi adresa de email.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_BAD_EMAIL): ?>
      Adresa de email nu este validă.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_NICK_TAKEN): ?>
      Acest nume de cont este deja folosit.
    <?php elseif ($this->_tpl_vars['error_code'] == ERR_EMAIL_TAKEN): ?>
      Această adresă de email este deja folosită.
    <?php else: ?>        A fost o problemă la înregistrare. Vă rugăm
      trimiteţi-ne un email pentru a ne ajuta să depanăm
      problema.
    <?php endif; ?>
    </b></font>
  <?php endif; ?>
    
  <p><form name="frm" method="post" action="signup.php">

  <table cellspacing=2>
  <tr>
    <td>Numele de cont:</td>
    <td><input type="text" name="nick" value="<?php echo $this->_tpl_vars['nick']; ?>
" size=16
    maxlength=16/></td>
  </tr></tr>
    <td>&nbsp;</td>
    <td><font size=-1>(3-16 caractere din setul <b>A-Z, a-z,  0-9, &#x2013;</b>
     şi <b>_</b>)</font></td>

  </tr><tr>
    <td>Parola:</td>
    <td><input type="password" name="password" value="<?php echo $this->_tpl_vars['password']; ?>
"
    size=16 maxlength=16/></td>
  </tr><tr>
    <td>&nbsp;</td>
    <td><font size=-1>(4-16 caractere)</font></td>

  </tr><tr>
    <td>Parola (verificare):</td>
    <td><input type="password" name="password2" value="<?php echo $this->_tpl_vars['password2']; ?>
"
    size=16 maxlength=16/></td>
  </tr><tr>
    <td>&nbsp;</td>
    <td><font size=-1>&nbsp;</font></td>

  </tr><tr>
    <td>Numele real (opţional):</td>
    <td><input type="text" name="name" value="<?php echo $this->_tpl_vars['name']; ?>
"
    size=40 maxlength=40/></td>
  </tr><tr>
    <td>&nbsp;</td>
    <td><font size=-1>&nbsp;</font></td>

  </tr><tr>
    <td>Adresa de email:</td>
    <td><input type="text" name="email" value="<?php echo $this->_tpl_vars['email']; ?>
"
    size=40 maxlength=40/></td>
  </tr><tr>
    <td>&nbsp;</td>
    <td><input type="checkbox" name="emailVisible"
    <?php if ($this->_tpl_vars['emailVisible'] == 'on' || ! $this->_tpl_vars['send']): ?>checked="checked"<?php endif; ?>/>
    Adresa mea este vizibilă tuturor utilizatorilor</td>
  </tr><tr>

    <td colspan=2 align=center>
    <input type=submit name="send" value="Creează contul"/>
    </td>
  </tr>
  </table>
<?php endif; ?>