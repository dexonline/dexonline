<?php

function int_create($size) {
  $buf = str_repeat(chr(0), 4 * $size + 4);
  // use first 4 bytes to store size
  int_put($buf, -1, $size);
  return $buf;
}

function int_get(&$intArray, $pos) {
  $pos = 4 * $pos + 4;
  return (ord($intArray[$pos]) << 24) | (ord($intArray[$pos + 1]) << 16) |
    (ord($intArray[$pos + 2]) << 8) | ord($intArray[$pos + 3]);
}

function int_put(&$intArray, $pos, $value) {
  $pos = 4 * $pos + 4;
  $intArray[$pos + 3] = chr($value & 0xFF);
  $value >>= 8;
  $intArray[$pos + 2] = chr($value & 0xFF);
  $value >>= 8;
  $intArray[$pos + 1] = chr($value & 0xFF);
  $value >>= 8;
  $intArray[$pos] = chr($value);
}

function int_size(&$intArray) {
  return int_get($intArray, -1);
}

?>
