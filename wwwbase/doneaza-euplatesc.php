<?php 

/**
 * We received this code from euplatesc.ro. Do not alter unless you really know what you're doing.
 **/

require_once("../phplib/Core.php");
$mid = Config::get('euplatesc.euPlatescMid');
$key = Config::get('euplatesc.euPlatescKey');
require_once("../phplib/third-party/euplatesc.php");

?>

<div align="center">
  <form ACTION="https://secure.euplatesc.ro/tdsprocess/tranzactd.php" METHOD="POST" name="gateway" target="_self">

    <!-- begin billing details -->
    <input name="fname" type="hidden" value="<?php echo $dataBill['fname'];?>">
    <input name="lname" type="hidden" value="<?php echo $dataBill['lname'];?>">
    <input name="country" type="hidden" value="<?php echo $dataBill['country'];?>">
    <input name="company" type="hidden" value="<?php echo $dataBill['company'];?>">
    <input name="city" type="hidden" value="<?php echo $dataBill['city'];?>">
    <input name="add" type="hidden" value="<?php echo $dataBill['add'];?>">
    <input name="email" type="hidden" value="<?php echo $dataBill['email'];?>">
    <input name="phone" type="hidden" value="<?php echo $dataBill['phone'];?>">
    <input name="fax" type="hidden" value="<?php echo $dataBill['fax'];?>">
    <!-- end billing details -->

    <!-- daca detaliile de shipping difera -->
    <input type="hidden" NAME="amount" VALUE="<?php echo  $dataAll['amount'] ?>" SIZE="12" MAXLENGTH="12">
    <input TYPE="hidden" NAME="curr" VALUE="<?php echo  $dataAll['curr'] ?>" SIZE="5" MAXLENGTH="3">
    <input type="hidden" NAME="invoice_id" VALUE="<?php echo  $dataAll['invoice_id'] ?>" SIZE="32" MAXLENGTH="32">
    <input type="hidden" NAME="order_desc" VALUE="<?php echo  $dataAll['order_desc'] ?>" SIZE="32" MAXLENGTH="50">
    <input TYPE="hidden" NAME="merch_id" SIZE="15" VALUE="<?php echo  $dataAll['merch_id'] ?>">
    <input TYPE="hidden" NAME="timestamp" SIZE="15" VALUE="<?php echo  $dataAll['timestamp'] ?>">
    <input TYPE="hidden" NAME="nonce" SIZE="35" VALUE="<?php echo  $dataAll['nonce'] ?>">
    <input TYPE="hidden" NAME="fp_hash" SIZE="40" VALUE="<?php echo  $dataAll['fp_hash'] ?>">
  </form>                                                                 

  <p class="tx_red_mic">Transferring to EuPlatesc.ro gateway</p>
  <p><img src="https://www.euplatesc.ro/plati-online/tdsprocess/images/progress.gif" alt="" title="" onload="javascript:document.gateway.submit()"></p>
  <p><a href="#" onclick="javascript:document.gateway.submit()">Go Now!</a></p>
</div>
