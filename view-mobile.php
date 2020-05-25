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
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="jquery-1.10.2.min.js"></script>
<script src="jquery.mobile-1.4.2.min.js"></script>
<link rel="stylesheet" href="jquery.mobile-1.4.2.min.css">
<style>
.ui-li-static.ui-collapsible > .ui-collapsible-heading {
    margin: 0;
}
.ui-li-static.ui-collapsible {
    padding: 0;
}
.ui-li-static.ui-collapsible > .ui-collapsible-heading > .ui-btn {
    border-top-width: 0;
}
.ui-li-static.ui-collapsible > .ui-collapsible-heading.ui-collapsible-heading-collapsed > .ui-btn,
.ui-li-static.ui-collapsible > .ui-collapsible-content {
    border-bottom-width: 0;
}
</style>
</head>
<body>

<div data-role="page">
  <div data-role="header">
    <h1>Kochbuch</h1>
  </div>

  <div data-role="main" class="ui-content">
    <ul data-role="listview" data-inset="true" data-shadow="true">
      <?php foreach ($actions as $i => $value) { ?> 
            <li><a href="<?php echo $value["l"]?>"><?php echo $value["name"]?></a></li>
      <?php } ?>
      <li style="width:auto" data-role="collapsible" data-iconpos="right" data-inset="false">
	<h2>Navigation</h2>
        <ul data-role="listview" data-theme="b">
          <?php foreach ($links as $i => $value) { ?> 
            <li><a href="<?php echo $value["l"]?>"><?php echo $value["name"]?></a></li>
          <?php } ?>
        </ul>
      </li>
      <li data-role="collapsible" data-iconpos="right" data-inset="false">
        <h2>Kochbuch</h2>
        <ul data-role="listview" data-theme="b">
          <li><a href="mobile.php?update">Aktualisieren</a></li>
          <li><a data-ajax=\"false\" target="_blank" href="index.php?<?php echo $_SERVER["QUERY_STRING"]; ?>">Desktopversion</a></li>
          <li><a href="mobile.php?logout">Abmelden</a></li>
        </ul>
      </li>
    </ul><br/>
    <?php $content_fun () ?>
  </div>

  <div data-role="footer">
    <h1><?php echo (htmlentities(getAuthorForUser($user))); ?></h1>
  </div>
</div> 

</body>
</html>

<?php } ?>