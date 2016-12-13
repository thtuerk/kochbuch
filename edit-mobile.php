/* This file is part of PHP Kochbuch
   Copyright (C) 2013-2016 Thomas Tuerk <thomas@tuerk-brechen.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
*/

<?php

function edit_markdown_file($warning, $file, $newfile, $preview) {
  $title = get_recipe_title($file);

  $show_preview = ! (empty ($preview));
  if ($show_preview) {
     $content = $preview;
     $commit_message = $_POST['commit'];
     $newcats = $_POST['newcats'];
  } else {
     $content = file_get_contents (add_rezept_dir($file));
     $newcats = file_get_contents (get_kategorien_filename($file));
     $commit_message = $file . " bearbeitet";
  }

  if (!(empty($warning))) {
    print "$warning\n<hr/>\n";
  }
?>
<h2><?php echo($title); ?></h2>
<form data-ajax="false" method="post" action="mobile.php">
  <p><textarea name="data"><?php
     echo $content; ?> </textarea>
  <div class="ui-field-contain">
     <label for="newcats">Kategorien</label>
     <textarea name="newcats" cols="55" row="3"><?php echo $newcats; ?></textarea>
  </div>
  <div class="ui-field-contain">
     <label for="newfile">Dateiname</label>
     <input name="newfile" type="text" size="50" value="<?php echo $newfile; ?>">
  </div>
  <div class="ui-field-contain">
     <label for="newfile">Kommentar</label>
     <input name="commit" type="text" size="50" value="<?php echo (htmlentities($commit_message)); ?>">
  </div> 
  <div data-role="controlgroup" data-type="vertical">
     <input type="hidden" name="mode" value="save">
     <input type="hidden" name="file" value="<?php echo $file; ?>">
     <input type="submit" value="Speichern" name="action" />
     <input type="submit" value="Vorschau" name="action"/>
     <input type="submit" value="Abbrechen" name="action"/>
  </div>
</form>

<?php
if ($show_preview) {
  $tmpfname = tempnam("/tmp", "dummy_recipe.md");
  $handle = file_put_contents($tmpfname, $content);
  $content = recipe_to_html($tmpfname);
  unlink($tmpfname);

  echo "<hr/><br/>$content";
}

}
?>