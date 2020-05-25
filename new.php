<?php

/* This file is part of PHP Kochbuch
   Copyright (C) 2013-2016 Thomas Tuerk <thomas@tuerk-brechen.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
*/

function new_markdown_file($warning) {
  $newcats = isset($_GET['cat']) ? $_GET['cat'] : "";
  if (isset ($_POST['newcats'])) { $newcats = $_POST['newcats']; };

  $content = (isset ($_POST['data'])) ? $_POST['data'] : "# Neues Rezept\n\n";
  $newfile = (isset ($_POST['newfile'])) ? $_POST['newfile'] : "Neues_rezept.md";

  if (!(empty ($warning))) {
     echo "$warning <hr/>\n\n";
  }
?>
<h2>Neues Rezept</h2>
<form method="post" action="index.php">
  <p><textarea name="data" cols="120" rows="20" style="width: 100%"><?php
     echo $content; ?> </textarea><table><tr>
  <td valign=top>Kategorien:</td><td><textarea name="newcats" cols="100" row="3"><?php echo $newcats; ?></textarea></td></tr>
  <td>Dateiname:</td><td><input name="newfile" type="text" size="80" value="<?php echo $newfile; ?>"></td></tr>
  </table>

  <p>
     <input type="hidden" name="mode" value="new">
     <input type="submit" value="Speichern" name="action" />
     <input type="submit" value="Vorschau" name="action"/>
  </p>
</form>

<?php
  $tmpfname = tempnam($TMP_DIR, "dummy_recipe.md");
  $handle = fopen($tmpfname, "w");
  fwrite($handle, $content);
  fclose($handle);
  $content = recipe_to_html ($tmpfname);
  unlink($tmpfname);

  echo "<hr/><br/>$content";
}
?>