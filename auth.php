/* This file is part of PHP Kochbuch
   Copyright (C) 2013-2016 Thomas Tuerk <thomas@tuerk-brechen.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
*/

<?php

function auth_fail () {
  global $REALM;
  header('HTTP/1.1 401 Unauthorized');
  header('WWW-Authenticate: Digest realm="'.$REALM.
         '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($REALM).'"');
  die('Keine Berechtigung! <a href="index.php">Anmelden</a>');
}

function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}

function getUserPassword($username) {
  global $AUTHORS;

  return ($AUTHORS[$username][0]);
}

function isValidUser($username) {
  global $AUTHORS;

  return (isset($AUTHORS[$username]));
}

function login() {
  global $REALM;

  if (isset ($_GET['logout'])) {
    unset ($_SERVER['PHP_AUTH_DIGEST']);
  }

  if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    auth_fail();
  }

  // analyze the PHP_AUTH_DIGEST variable
  if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))) auth_fail();
  if (!(isValidUser($data['username']))) auth_fail();


  // generate the valid response
  $A1 = md5($data['username'] . ':' . $REALM . ':' . getUserPassword($data['username']));
  $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
  $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

  if ($data['response'] != $valid_response)
      auth_fail();

  return ($data['username']);
}


?>