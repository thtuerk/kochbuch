<?php

/* This file is part of PHP Kochbuch
   Copyright (C) 2013-2016 Thomas Tuerk <thomas@tuerk-brechen.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
*/


require_once('functions.php');

function print_view($links, $actions, $content_fun, $user) {
  global $GIT_NAME, $GIT_MASTER_LINK, $USE_EXTERNAL_GIT_VIEWER;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
  <link rel="stylesheet" type="text/css" href="styles.css">
  <title>Kochbuch</title>
</head>

<body>
<a name="top"></a>
<h1><img src="title.png" alt="Kochbuch"></h1>

<table border="0" cellpadding="3" cellspacing="0">
<tbody><tr>
  <td valign="top">
<table border="0" cellpadding="0" cellspacing="0">
  <tbody><tr class="border" align="top">
    <td>
      <table border="0" cellpadding="3" cellspacing="2">
        <tbody><tr style="font-size:small;">
          <td style="background-color:#c0c0d5" class="menu">Navigation</td>
        </tr>
        <tr>
          <td class="menu">
            <div style="white-space:nowrap">
            </div>
            <table class="menu" border="0" cellpadding="0" cellspacing="1">
            <tbody>
<?php
foreach ($actions as $i => $value) {
  ?>
<tr>
  <td><img src="dot.png" alt="*" align="bottom" border="0"></td>
  <td><a href="<?php echo $value["l"]?>"><?php echo $value["name"]?></a></td>
</tr>
<?php
}
 if (count($actions) > 0) print "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
?>
<?php
foreach ($links as $i => $value) {
  ?>
<tr>
  <td><img src="dot.png" alt="*" align="bottom" border="0"></td>
  <td><a href="<?php echo $value["l"]?>"><?php echo $value["name"]?></a></td>
</tr>
<?php
} ?>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
  <td><img src="dot.png" alt="*" align="bottom" border="0"></td>
  <td><a target="_blank" href="index.php?update">Aktualisieren</a></td>
</tr>
<?php

if ($USE_EXTERNAL_GIT_VIEWER) {?>
<tr>
  <td><img src="dot.png" alt="*" align="bottom" border="0"></td>
  <td><a target="_blank" href="<?php echo $GIT_MASTER_LINK ?>"><?php echo $GIT_NAME; ?></a></td>
</tr>
<?php }
?>
<tr>
  <td><img src="dot.png" alt="*" align="bottom" border="0"></td>
  <td><a target="_blank" href="mobile.php?<?php echo $_SERVER["QUERY_STRING"]; ?>">Mobilversion</a></td>
</tr>
<tr>
  <td><img src="dot.png" alt="*" align="bottom" border="0"></td>
  <td><a target="_blank" href="http://de.wikipedia.org/wiki/Markdown">Markdown Hilfe</a></td>
</tr>
<tr>
  <td><img src="dot.png" alt="*" align="bottom" border="0"></td>
  <td><a href="index.php?logout">Abmelden</a></br></td>
</tr>
              <tr>
                <td colspan="2" align=center><?php echo (htmlentities(getAuthorForUser($user))); ?> </td>
              </tr>
            </tbody></table>
          </td>
        </tr>
      </tbody></table>
    </td>
  </tr>
</tbody></table>
  </td>
  <td>&nbsp;&nbsp;</td>
  <td valign="top">
    <?php $content_fun () ?>
  </td>
</tr>
</tbody></table>


</body></html>

<?php } ?>