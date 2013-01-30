<?php

/**
 * We received this code from euplatesc.ro. Do not alter unless you really know what you're doing.
 **/

function hmacsha1($key, $data) {
  $blocksize = 64;
  $hashfunc  = 'md5';
  if(strlen($key) > $blocksize) {
    $key = pack('H*', $hashfunc($key));
  }
  $key  = str_pad($key, $blocksize, chr(0x00));
  $ipad = str_repeat(chr(0x36), $blocksize);
  $opad = str_repeat(chr(0x5c), $blocksize);
  $hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));
  return bin2hex($hmac);
}

function euplatesc_mac($data, $key = NULL) {
  $str = NULL;
  foreach($data as $d) {
    if($d === NULL || strlen($d) == 0) {
      $str .= '-'; // valorile nule sunt inlocuite cu -
    } else {
      $str .= strlen($d) . $d;
    }
  }

  $key = pack('H*', $key);
  return hmacsha1($key, $str);
}

$dataAll = array(
  'amount'      => $_POST['amount'],                                        // suma de plata
  'curr'        => 'RON',                                                   // moneda de plata
  'invoice_id'  => str_pad(substr(mt_rand(), 0, 7), 7, '0', STR_PAD_LEFT),  // numarul comenzii este generat aleator. inlocuiti cuu seria dumneavoastra
  'order_desc'  => 'Donatie online',                                        // descrierea comenzii

  // va rog sa nu modificati urmatoarele 3 randuri
  'merch_id'    => $mid,                                                    // nu modificati
  'timestamp'   => gmdate("YmdHis"),                                        // nu modificati
  'nonce'       => md5(microtime() . mt_rand()),                            // nu modificati
); 
$dataAll['fp_hash'] = strtoupper(euplatesc_mac($dataAll, $key));

// completati cu valorile dvs
$dataBill = array(
  'fname'   => '', // $_POST['fname'],   // nume
  'lname'   => '', // $_POST['lname'],   // prenume
  'country' => '', // $_POST['country'], // tara
  'company' => '', // $_POST['company'], // firma
  'city'    => '', // $_POST['city'],    // oras
  'add'     => '', // $_POST['add'],     // adresa
  'email'   => $_POST['email'],   // email
  'phone'   => '', // $_POST['phone'],   // telefon
  'fax'     => '', // $_POST['fax'],     // fax
);

?>
