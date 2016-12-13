/* This file is part of PHP Kochbuch
   Copyright (C) 2013-2016 Thomas Tuerk <thomas@tuerk-brechen.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
*/

<?php 

  require_once('config.php');
  require_once('functions.php');
  require_once('view.php');
  require_once('edit.php');
  require_once('new.php');
  require_once('auth.php');
  require_once('/usr/share/php/libphp-phpmailer/class.phpmailer.php');

  $mode = "cathegory";
  if (isset ($_GET['mode'])) $mode = $_GET['mode'];
  if (isset ($_POST['mode'])) $mode = $_POST['mode'];

  if ($mode == "out" || $mode == "outcat") {
     $wikiUser = "";
  } else {
     $wikiUser = login();
  }

  $links = array (
    array ("l" => "index.php", "name" => "Alle Rezepte")
  );
  $links[] = array ("l" => "index.php?mode=latest_additions", "name" => "Neue Rezepte");
  $links[] = array ("l" => "index.php?mode=view_cats", "name" => "Kategorien");
  $links[] = array ("l" => "index.php?mode=nocat", "name" => "Rezepte ohne Kategorie");


  $actions = array ();

  function add_action ($link, $name) {
     global $actions;
     $actions[] = array ("l" => $link, "name" => $name);
  }

  function get_kategory_md ($cat_name, $recipes) {
    if (count($recipes) == 0) return "";

    $result = "## $cat_name\n";
    foreach ($recipes as $i => $value) {
       $result .= "- ". $value['title'] . "\n";
    }
    $result .= "\n\n\n";
    return $result;
  }

  if ($mode == "cathegory") {
    if (isset ($_GET['update'])) {
       git_pull ();
    }
    if (!isset ($_GET['cat'])) {
       $all_recipes = get_all_recipes(null);
       $cat = "Alle Rezepte";
       $cat_url = "";
       $is_top_cat = true;
       add_action ("index.php?mode=new", "Neues Rezept");
       $super_cat = "-";
    } else {
       $is_top_cat = false;
       $cat = $_GET['cat'];
       $cat_url = "&cat=" . urlencode ($cat);
       $all_recipes = get_all_recipes($cat);
       add_action ("index.php?mode=edit_cat&cat=" . urlencode ($cat), "Kategorie bearbeiten");
       add_action ("index.php?mode=new&cat=" . urlencode ($cat), "Neues Rezept");

       $cat_array = explode("/", $cat);
       array_pop ($cat_array);
       $super_cat_0 = implode ("/", $cat_array);
       if (empty ($super_cat_0)) {
         $super_cat = "<a href=\"index.php?mode=cathegory\">Alle Rezepte</a>";
       } else {
         $super_cat = "<a href=\"index.php?mode=cathegory&cat=" . (urlencode ($super_cat_0)) . "\">" .
              utf8_htmlentities($super_cat_0) . "</a>";
       }
    }

    $content= "<h2>".utf8_htmlentities($cat) . " (" . count ($all_recipes) . ")</h2>";

    $export_table  = "<tr><th align=left valign=top>Export:</th><td>\n";
    $export_table .= "<ul><li><a href=\"index.php?mode=outcat&filetype=pdf5x2" . $cat_url . "\">pdf (A5 auf A4)</a></li>\n";
    $export_table .= "<li><a href=\"index.php?mode=outcat&filetype=pdf4" . $cat_url . "\">pdf (A4)</a></li>\n";
    $export_table .= "<li><a href=\"index.php?mode=outcat&filetype=pdf5" . $cat_url . "\">pdf (A5)</a></li>\n";
    $export_table .= "<li><a href=\"index.php?mode=outcat&filetype=epub" . $cat_url . "\">epub</a></li>\n";
    $export_table .= "<li><a href=\"index.php?mode=outcat&filetype=docx" . $cat_url . "\">docx</a></li>\n";
    $export_table .= "<li><a href=\"index.php?mode=outcat&filetype=odt" . $cat_url . "\">odt</a></li>\n";
    $export_table .= "<li><a href=\"index.php?mode=outcat&filetype=tex" . $cat_url . "\">tex</a></li>\n";
    $export_table .= "<li><a href=\"index.php?mode=outcat&filetype=txt" . $cat_url . "\">txt</a></li></ul>\n";
    $export_table .= "</td></tr>";

    $subcats_html = "";
    $subcats = get_direct_subcats(($is_top_cat) ? "" : $cat);
    foreach ($subcats as $subcat => $count) {
       $sub_cat_0 = substr($subcat, $is_top_cat ? 0 : strlen($cat)+1);
       $html = "<a href=\"index.php?mode=cathegory&cat=" . (urlencode ($subcat)) . "\">" .
            utf8_htmlentities($sub_cat_0) . "</a> ($count)";
       $subcats_html .= "<li>$html</li>";
    }
 
    $content .= "<table>\n";
    $content .= "<tr><th align=left valign=top>Oberkategorie: </th><td>$super_cat</td></tr>\n";
    $content .= "<tr><th align=left valign=top>Unterkategorien: </th><td><ul>$subcats_html</ul></td></tr>\n";
    $content .= $export_table;
    $content .= "</table>\n\n<br/><hr/><ul>";

    foreach ($all_recipes as $i => $value) {
       $content .= "\n<li><a href = \"index.php?mode=view&file=". urlencode($value['filename']) . "\">". $value['title'] . "</a></li>";
    }
    $content .= "\n</ul>\n\n";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);
  } else if ($mode == "latest_additions") {
    $neue_rezepte = get_new_recipes();
    $rezepte_anzahl = count ($neue_rezepte);
    $content = "<h2>Neue Rezepte ($rezepte_anzahl)</h2>\n\n";
    $content .= "<table>\n";

    foreach ($neue_rezepte as $file => $rev) {
      $content .= "<tr>";
      $date = strftime("%e. %b %Y", $rev["date"]);
      $content .= "<td align=right>$date</td><td>&nbsp;</td>";

      $title = get_recipe_title($file);      
      $link = "<a href = \"index.php?mode=view&file=". urlencode($file) . "\">". $title . "</a>";
      $content .= "<td>$link</td><td>&nbsp;</td>";


      $author = $rev["author_name"];
      $content .= "<td>$author</td>";
      $content .= "<tr>\n";

    }
    $content .= "</table>";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);    
  } else if ($mode == "view_cats") {
    $content = "<h2>Kategorien</h2>\n\n";

    $rezepte_anzahl = count (get_all_recipes(null));
    $rezepte_anzahl_ohne = count (get_recipes_no_cat());
    $rezepte_anzahl_mit = $rezepte_anzahl - $rezepte_anzahl_ohne;
    $content .= "<ul><li><a href=\"index.php?mode=cathegory\">Alle Rezepte</a> ($rezepte_anzahl)</li>";
    $content .= "<li><a href=\"index.php?mode=nocat\">Rezepte ohne Kategorie</a> ($rezepte_anzahl_ohne)</li>";
    $content .= "<li>Rezepte mit Kategorie ($rezepte_anzahl_mit)</li>";
    $cats = get_all_cats();
    $content .= format_cat_array_as_tree("", $cats);
    $content .= "</ul>";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);    
  } else if ($mode == "nocat") {
    $all_recipes = get_recipes_no_cat();

    $content= "<h2>Rezepte ohne Kategorie</h2>\n\n<ul>";

    foreach ($all_recipes as $i => $value) {
       $content .= "\n<li><a href = \"index.php?mode=view&file=". urlencode($value['filename']) . "\">". $value['title'] . "</a></li>";
    }
    $content .= "\n</ul>\n\n";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);

  } else if ($mode == "out") {
    $file = $_GET['file'];
    $filetype = $_GET['filetype'];
    $file_full = add_rezept_dir($file);

    $tmpfile = tempnam("$TMP_DIR", "KOCHSUITE") ;
    if ($filetype == "pdf4") {
       $out_filename = substr($file, 0, -2) . "pdf";

       exec ("pandoc --template a4.tmpl -f markdown -o $tmpfile.pdf < $file_full");
       output_file ($out_filename, "$tmpfile.pdf");
    } else if ($filetype == "pdf5") {
       $out_filename = substr($file, 0, -2) . "pdf";
       exec ("pandoc --template a5.tmpl -f markdown -o $tmpfile.pdf < $file_full");
       output_file ($out_filename, "$tmpfile.pdf");
    } else if ($filetype == "pdf5x2") {
       $out_filename = substr($file, 0, -2) . "pdf";
       exec("pandoc --template a5.tmpl -f markdown -o $tmpfile.tex < $file_full");
       exec("cd tmp; latex $tmpfile.tex > /dev/null 2>&1");       
       exec("dvips -t a5 -o $tmpfile-1.ps $tmpfile.dvi > /dev/null 2>&1");
       exec("psnup -Pa5 -pa4 -n 2 $tmpfile-1.ps $tmpfile.ps > /dev/null 2>&1");
       exec("gs -q -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=$tmpfile.pdf $tmpfile.ps > /dev/null 2>&1");
       output_file ($out_filename, "$tmpfile.pdf");
    } else if ($filetype == "html") {
       $content = `pandoc -s -f markdown --to html -H iotp.css < $file_full`;
       print($content);
    } else {
       $out_filename = substr($file, 0, -2) . $filetype;
       exec("pandoc -f markdown -o $tmpfile.$filetype < $file_full");
       output_file ($out_filename, "$tmpfile.$filetype");
    }
    `rm -f $tmpfile*`;
  } else if ($mode == "iotp") {
    $file = $_GET['file'];

    $file_full = add_rezept_dir($file);
    $out_filename = substr($file, 0, -2) . "html";
    $tmpfile = tempnam("$TMP_DIR", "KOCHSUITE") ;
    `pandoc -s -f markdown -o $tmpfile.html --to html -H iotp.css < $file_full`;
    $title = get_recipe_title($file);
    $body = recipe_to_txt($file_full);
    $mail = new PHPMailer(true);  
    try {  
      $mail->AddAddress($IOTP_EMAIL, 'IOTP');
      $mail->SetFrom($IOTP_EMAIL, 'Kochbuch');
      $mail->Subject = $title;
      $mail->Body=$body;
      $mail->AddAttachment( "$tmpfile.html" , $out_filename );
      $mail->Send();
      echo "Rezept erfolgreich an Internet of Things Printer verschickt";
    } catch (phpmailerException $e) {
      echo $e->errorMessage(); //Pretty error messages from PHPMailer
    } catch (Exception $e) {
      echo $e->getMessage(); //Boring error messages from anything else!
    }
    `rm -f $tmpfile*`;
  } else if ($mode == "outcat") {
    if (!isset ($_GET['cat'])) {
       $all_recipes = get_all_recipes(null, false);
       $all_recipes_direct = get_all_recipes(null, true);
       $cat = "Alle Rezepte";
       $subcats = get_subcats(null);
    } else {
       $cat = $_GET['cat'];
       $all_recipes = get_all_recipes($cat, false);
       $all_recipes_direct = get_all_recipes($cat, true);
       $subcats = get_subcats($cat);
    }
    $filetype = $_GET['filetype'];

    $tmpfile = tempnam("$TMP_DIR", "KOCHSUITE");
    $file_full = "$tmpfile.md";

    $handle = fopen($file_full, 'w');
    fwrite($handle, "% $cat\n%\n%" . strftime("%e. %B %Y") . "\n\n");

    fwrite($handle, "# Kategorien\n\n");
    fwrite($handle, get_kategory_md($cat, $all_recipes_direct));
    foreach ($subcats as $subcat => $count) {
      $recipes = get_all_recipes($subcat, true);
      fwrite($handle, get_kategory_md($subcat, $recipes));      
    }    
    fwrite($handle, "\n\n\n");

    foreach ($all_recipes as $i => $value) {
       $content = file_get_contents (add_rezept_dir($value['filename']));
       fwrite($handle, $content . "\n\n\n\n");
    }
    fclose ($handle);
    $cat_filename = "kochbuch";

    format_cat($file_full, $tmpfile, $cat_filename, $filetype);
    `rm $file_full`; 
  } else if ($mode == "delete") {
    $file = $_GET['file'];
    if (!isset ($_GET['confirm'])) {
      $file_full = add_rezept_dir($file);
      $link = "index.php?mode=delete&confirm&file=". urlencode($file);
      $content = "<a class=\"delete\" href=\"$link\">Löschen bestätigen</a></div><hr>\n";
      $content .= recipe_to_html ($file_full);
      print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);
    } else {
      $content = "<h2 style=\"color:red\">gelöscht</h2>\n\n";
      git_rm ($file);
      git_rm ("$file.dir");
      git_commit($file." gelöscht");
      print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);
    }
  } else if ($mode == "edit_files") {
    $file = $_GET['file'];
    $base_url = "index.php?mode=edit_files&file=" . urlencode ($file);
    $submode = isset ($_GET['submode']) ? $_GET['submode'] : "";

    $content = "";
    if ($submode == "upload_new") {
      if (($_FILES['upfile']['name']) && (!$_FILES['upfile']['error']))	{
        $new_file_name = $file . ".dir/" . $_FILES['upfile']['name'];
	move_uploaded_file($_FILES['upfile']['tmp_name'], add_rezept_dir($new_file_name));
        git_stage($new_file_name);
        git_commit("Datei $new_file_name hochgeladen");
        $upload_result="Datei <i>" . $_FILES['upfile']['name'] ."</i> erfolgreich hochgeladen";
      } else {
        $upload_result = "Fehler " . $_FILES['upfile']['error'];
      }

      $content .= "<h2 style=\"color:red\">$upload_result</h2><hr/>\n\n";
    }
    if (($submode == "delete") && (isset ($_GET['extra_file']))) {
      $full_file_name = $file . ".dir/" . ($_GET['extra_file']);
      git_rm($full_file_name);
      git_commit("Datei $full_file_name gelöscht");
      $upload_result="Datei <i>" . $_GET['extra_file'] ."</i> erfolgreich gelöscht";
      $content .= "<h2 style=\"color:red\">$upload_result</h2><hr/>\n\n";
    }
    
    $content .= "<h2>Dateien \n";
    $content .= "<a target=\"_blank\" href=\"index.php?mode=view&file=". urlencode ($file) . "\">".
           utf8_htmlentities(get_recipe_title($file)) . "</a> ";
    $content .= "</h2>\n\n";
  
    $content .= "<table width=\"100%\"><tr><th align=left>Dateiname</th><th align=right>Größe</th><th>&nbsp;</th></tr>\n";

    $extra_files = get_recipe_extra_files($file);
    foreach ($extra_files as $extra_file) {
       $extra_file_full = add_rezept_dir($file) . ".dir/$extra_file";
       $stats = stat($extra_file_full);
       $content .= "<tr><td><a href=\"$extra_file_full\">$extra_file</a></td><td align=right>". round ($stats[7] / 1000) . " kb</td>";
       $content .= "<td align=right><a href=\"$base_url&submode=delete&extra_file=" . urlencode($extra_file) . "\">";
       $content .= "Löschen</td>\n";
       $content .= "</tr>\n";
    }

    $content .=  "</table><hr/><h3>Neue Datei hochladen</h3><form action=\"$base_url&submode=upload_new\" method=\"post\" enctype=\"multipart/form-data\">\n";
    $content .= "<label for=\"upfile\">Datei:</label>&nbsp;<input type=\"file\" name=\"upfile\" id=\"upfile\"><br/>\n";
    $content .= "<input type=\"submit\" name=\"submit\" value=\"Senden\">\n</form>\n";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);
  } else if ($mode == "view") {
    $file = $_GET['file'];
    $file_full = add_rezept_dir($file);

    $table = "<table><tr><th align=left>Kategorien:</th><td>";
    $table .= implode (", ", array_map (function ($cat) {return 
        "<a href=\"index.php?mode=cathegory&cat=". urlencode($cat) . "\">$cat</a>";}, 
        clean_cats_list(recipe_cats($file))));
    $table .= "</td></tr>\n";
    $table .= "<tr><th align=left valign=top>Geschichte:</th><td>";
    $table .= git_last_change($file);
    $table .= "</td></tr>\n";
    $table .= "<tr><th align=left valign=top>Dateiname:</th><td>$file</td></tr>\n";
    $table .= "<tr><th align=left valign=top>Export:</th><td>\n";
    $table .= "pdf (<a href=\"index.php?mode=out&filetype=pdf5x2&file=" . urlencode ($file) . "\">A5 auf A4</a>, \n";
    $table .= "<a href=\"index.php?mode=out&filetype=pdf4&file=" . urlencode ($file) . "\">A4</a>, \n";
    $table .= "<a href=\"index.php?mode=out&filetype=pdf5&file=" . urlencode ($file) . "\">A5</a>), \n";
    $table .= "<a href=\"index.php?mode=out&filetype=html&file=" . urlencode ($file) . "\">html</a>,\n";
    $table .= "<a href=\"index.php?mode=out&filetype=docx&file=" . urlencode ($file) . "\">docx</a>,\n";
    $table .= "<a href=\"index.php?mode=out&filetype=odt&file=" . urlencode ($file) . "\">odt</a>,\n";
    $table .= "<a href=\"index.php?mode=out&filetype=tex&file=" . urlencode ($file) . "\">tex</a>,\n";
    $table .= "<a href=\"index.php?mode=out&filetype=txt&file=" . urlencode ($file) . "\">txt</a>\n";
    $table .= "</td></tr>\n";
    $table .= "</table>\n\n";

    add_action ("index.php?mode=edit&file=" . urlencode ($file), "Rezept bearbeiten");
    add_action ("index.php?mode=delete&file=" . urlencode ($file), "Rezept löschen");
    add_action ("index.php?mode=edit_files&file=" . urlencode ($file), "Dateien bearbeiten");
    add_action (buildRecipeMailToLink("", $file), "eMail");
    if (isset ($IOTP_EMAIL)) {
      add_action ("index.php?mode=iotp&file=" . urlencode ($file), "IOTP");
    }
    $content  = recipe_to_html($file_full);

    $extra_files = get_recipe_extra_files($file);
    if (count($extra_files) > 0) {
      $files_table = "<table width=\"100%\"><tr><th align=left>Dateiname</th><th align=right>Größe</th><th>&nbsp;</th></tr>\n";
      $image_table = "";
      foreach ($extra_files as $extra_file) {
         $extra_file_full = add_rezept_dir($file) . ".dir/$extra_file";
         $stats = stat($extra_file_full);
         if (is_image_file($extra_file)) {
           $file_array = explode(".", $extra_file_full);
           $ending = array_pop ($file_array);
           $extra_file_full_no_end = implode(".", $file_array);
           if (str_endsWith($extra_file_full_no_end, "-klein")) continue;
           create_small_image($extra_file_full_no_end, $ending);
           $image_table .= "<table><tr><td align=center><a href=\"$extra_file_full\"><image src=\"". $extra_file_full_no_end . 
              "-klein." . $ending . "\"></a><br/>";
           $image_table .= utf8_htmlentities($extra_file);
           $image_table .= "</td></tr></table>\n";
         }
         $files_table .= "<tr><td><a href=\"$extra_file_full\">$extra_file</a></td><td align=right>". round ($stats[7] / 1000) . " kb</td></tr>\n";
      }

      $files_table .= "</table>\n";
      if (!(empty ($image_table))) $image_table .= "<br/>";
      $content .= "<hr>$image_table $files_table<br/>";
    }

    $content .= "<hr>$table<br/><br/>";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);
  } else if ($mode == "edit") {
    $file = $_GET['file'];
    $content = function() { global $file; edit_markdown_file ("", $file, $file, ""); };

    print_view ($links, $actions, $content, $wikiUser);
  } else if ($mode == "new") {
    if (isset ($_POST['action'])) {
      $file = $_POST['newfile'];
      $warning = check_filename($file);
      if ($_POST['action'] == "Speichern" && empty ($warning)) {
        file_put_contents(add_rezept_dir($file), str_replace("\r", "", $_POST['data']));
        recipe_set_cats($file, parse_cats_string($_POST['newcats']));
        git_stage($file);
        git_stage("$file.dir");
        git_commit($file . " erstellt");

        $save_message = "<h2 style=\"color:red\">gespeichert</h2><br>\n";
        $save_message .= "<a href=\"index.php?mode=view&file=". urlencode ($file) . "\">".
           utf8_htmlentities(get_recipe_title($file)) . "</a> anzeigen<br/>\n\n";
        $save_message .= "<a href=\"index.php?mode=edit&file=". urlencode ($file) . "\">".
           utf8_htmlentities(get_recipe_title($file)) . "</a> bearbeiten";
        print_view ($links, $actions, function () {global $save_message; echo $save_message;}, $wikiUser);
      } else {      
        $content_fun = function() { global $warning; new_markdown_file ($warning); };
        print_view ($links, $actions, $content_fun, $wikiUser);
      }
    } else {
      $content_fun = function() { new_markdown_file (""); };
      print_view ($links, $actions, $content_fun, $wikiUser);
    }
  } else if ($mode == "save") {
    $file = $_POST['file'];
    $data = $_POST['data'];
    $action = $_POST['action'];
    $newcats = $_POST['newcats'];
 
    $file_new = $_POST['newfile'];
    $warning = check_filename($file_new, $file);
    
    $save_message = "";
    if (($action == "Speichern") && ($warning == "")) {
      save_file ($file, $file_new, $data, $newcats);

      $save_message = "<h2 style=\"color:red\">gespeichert</h2><br>\n";
      $save_message .= "<a href=\"index.php?mode=view&file=". urlencode ($file) . "\">".
         utf8_htmlentities(get_recipe_title($file)) . "</a> anzeigen";
      print_view ($links, $actions, function () {global $save_message; echo $save_message;}, $wikiUser);
    } else if ($action == "Abbrechen") {
      header("Location: index.php?mode=view&file=". urlencode ($file));
    } else {
      $content_fun = function() { global $file, $file_new, $data, $warning; edit_markdown_file ($warning, $file, $file_new, $data); };
      print_view ($links, $actions, $content_fun, $wikiUser);
    }
  } else if ($mode == "edit_cat") {
    $cat = $_GET['cat'];
    $all_recipes = get_all_recipes(null);
    $content= "<h2>$cat</h2>\n";
    $content .= "<form method=\"post\" action=\"index.php\">\n";
    $content .= "<input type=\"hidden\" name=\"mode\" value=\"save_cat\">\n";
    $content .= "<input type=\"hidden\" name=\"cat\" value=\"$cat\">\n";
    foreach ($all_recipes as $i => $value) {
       $body = "&nbsp;&nbsp;&nbsp;<a target=\"_blank\" href=\"index.php?mode=view&file=". urlencode($value['filename']) . "\">". $value['title'] . "</a>";
       $checked = (recipe_in_cat($value['filename'], $cat)) ? "checked " : "";
       $content .= "\n<input type=\"checkbox\" $checked name=\"cat_recipe[]\" value=\"" . $value['filename'] . "\">$body</input><br/>\n";
    }
    $content .= "\n\n<br/><input type=\"submit\" value=\"Speichern\" name=\"action\" />&nbsp;&nbsp;";
    $content .= "<input type=\"submit\" value=\"Abbrechen\" name=\"action\" />";
    $content .= "\n</form>\n\n";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);
  } else if ($mode == "save_cat") {
    $cat = $_POST['cat'];
    $all_recipes = get_all_recipes(null);

    $file = $_POST['file'];
    $data = $_POST['cat_recipe'];
    $action = $_POST['action'];

    $save_message = "";
    if ($action == "Speichern") {
      $save_message = "<h2 style=\"color:red\">gespeichert</h2>\n\n";

      foreach ($all_recipes as $recipe) {
        $in_cat = recipe_in_cat($recipe['filename'], $cat);
        $new_in_cat = in_array($recipe['filename'], $data);

        if ($in_cat and (! $new_in_cat)) {
            remove_cat_from_recipe($recipe['filename'], $cat);
        }
        if ((!$in_cat) and $new_in_cat) {
            add_cat_to_recipe($recipe['filename'], $cat);
        }
      }
      git_commit("Kategorie $cat bearbeitet");

      print_view ($links, $actions, function () {global $save_message; echo $save_message;}, $wikiUser);
    } 
    if ($action == "Abbrechen") {
      header("Location: index.php?mode=cathegory&cat=". urlencode ($cat));
    } 
  } else {
    print "Error: Unknown Mode: $mode";
  }



?>
