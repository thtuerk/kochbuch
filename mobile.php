<?php 

  require_once('config.php');
  require_once('functions.php');
  require_once('view-mobile.php');
  require_once('edit-mobile.php');
  require_once('auth.php');

  $mode = "cathegory";
  if (isset ($_GET['mode'])) $mode = $_GET['mode'];
  if (isset ($_POST['mode'])) $mode = $_POST['mode'];

  if ($mode == "out" || $mode == "outcat") {
     $wikiUser = "";
  } else {
     $wikiUser = login();
  }

  $links = array (
    array ("l" => "mobile.php", "name" => "Alle Rezepte")
  );
  $links[] = array ("l" => "mobile.php?mode=latest_additions", "name" => "Neue Rezepte");
  $links[] = array ("l" => "mobile.php?mode=view_cats", "name" => "Kategorien");

  $actions = array ();

  function add_action ($link, $name) {
     global $actions;
     $actions[] = array ("l" => $link, "name" => $name);
  }

  function add_link ($link, $name) {
     global $links;
     $links[] = array ("l" => $link, "name" => $name);
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
    } else {
       $is_top_cat = false;
       $cat = $_GET['cat'];
       $cat_url = "&cat=" . urlencode ($cat);
       $all_recipes = get_all_recipes($cat);

       $cat_array = explode("/", $cat);
       array_pop ($cat_array);
       $super_cat_0 = implode ("/", $cat_array);
       if (empty ($super_cat_0)) {
       } else {
         add_link ("mobile.php?mode=cathegory&cat=" . (urlencode ($super_cat_0)), "Kategorie " . utf8_htmlentities($super_cat_0));
       }
    }

    $content= "<h2>".utf8_htmlentities($cat) . " (" . count ($all_recipes) . ")</h2>";

    $export_table = "<ul data-role=\"listview\">";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=pdf5x2" . $cat_url . "\">pdf (A5 auf A4)</a></li>\n";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=pdf4" . $cat_url . "\">pdf (A4)</a></li>\n";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=pdf5" . $cat_url . "\">pdf (A5)</a></li>\n";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=epub" . $cat_url . "\">epub</a></li>\n";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=docx" . $cat_url . "\">docx</a></li>\n";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=odt" . $cat_url . "\">odt</a></li>\n";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=tex" . $cat_url . "\">tex</a></li>\n";
    $export_table .= "<li><a data-ajax=\"false\" href=\"index.php?mode=outcat&filetype=txt" . $cat_url . "\">txt</a></li>\n";
    $export_table .= "</ul>";


    $subcats_html = "<ul data-role=\"listview\" data-filter-placeholder=\"suche Kategorie ...\" data-inset=\"true\" data-filter=\"true\">";
    $subcats = get_direct_subcats(($is_top_cat) ? "" : $cat);
    foreach ($subcats as $subcat => $count) {
       $sub_cat_0 = substr($subcat, $is_top_cat ? 0 : strlen($cat)+1);
       $html = "<a href=\"mobile.php?mode=cathegory&cat=" . (urlencode ($subcat)) . "\">" .
            utf8_htmlentities($sub_cat_0) . " <span class=\"ui-li-count\">$count</span></a>";
       $subcats_html .= "<li>$html</li>";
    }
    $subcats_html .= "</ul>";
 
    $content .= "<div data-role=\"collapsible\"><h4>Export</h4>$export_table</div>";

    if (count($subcats) > 0) {
      $content .= "<div data-role=\"collapsible\"><h4>Unterkategorien</h4>$subcats_html</div>";
    }

    $content .= "<div data-role=\"collapsible\" data-collapsed=\"false\"><h4>Rezepte</h4>";
    $content .= "<ul data-role=\"listview\" data-filter=\"true\" data-filter-placeholder=\"suche Rezept ...\" data-inset=\"true\" data-autodividers=\"true\">";
    foreach ($all_recipes as $i => $value) {
      $content .= "\n<li><a href = \"mobile.php?mode=view&file=". urlencode($value['filename']) . "\">". $value['title'] . "</a></li>";
    }
    $content .= "\n</ul></div>\n\n";

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
      $link = "<a href = \"mobile.php?mode=view&file=". urlencode($file) . "\">". $title . "</a>";
      $content .= "<td>$link</td><td>&nbsp;</td>";


      $author = $rev["author_name"];
      $content .= "<td>$author</td>";
      $content .= "<tr>\n";

    }
    $content .= "</table>";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);    
  } else if ($mode == "view_cats" || $mode == "nocat") {
    $content = "<h2>Kategorien</h2>\n\n";

    $rezepte_anzahl = count (get_all_recipes(null));
    $rezepte_anzahl_ohne = count (get_recipes_no_cat());
    $rezepte_anzahl_mit = $rezepte_anzahl - $rezepte_anzahl_ohne;
    $content .= "<ul><li><a href=\"mobile.php?mode=cathegory\">Alle Rezepte</a> ($rezepte_anzahl)</li>";
    $content .= format_cat_array_as_tree("", get_all_cats());
    $content .= "</ul>";

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);    
  } else if ($mode == "edit") {
    $file = $_GET['file'];
    $content = function() { global $file; edit_markdown_file ("", $file, $file, ""); };

    print_view ($links, $actions, $content, $wikiUser);
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
      $save_message .= "<a href=\"mobile.php?mode=view&file=". urlencode ($file) . "\">".
         utf8_htmlentities(get_recipe_title($file)) . "</a> anzeigen";
      print_view ($links, $actions, function () {global $save_message; echo $save_message;}, $wikiUser, true);
    } else if ($action == "Abbrechen") {
      header("Location: mobile.php?mode=view&file=". urlencode ($file));
    } else {
      $content_fun = function() { global $file, $file_new, $data, $warning; edit_markdown_file ($warning, $file, $file_new, $data); };
      print_view ($links, $actions, $content_fun, $wikiUser);
    }
  } else if ($mode == "view") {
    $file = $_GET['file'];
    $file_full = add_rezept_dir($file);
    add_action ("mobile.php?mode=edit&file=" . urlencode ($file), "Rezept bearbeiten");
    add_action (buildRecipeMailToLink("", $file), "eMail");
    if (isset($IOTP_EMAIL)) {
      add_action ("index.php?mode=iotp&file=" . urlencode ($file), "IOTP");
    }
    $content  = recipe_to_html($file_full);

    $extra_files = get_recipe_extra_files($file);
    if (count($extra_files) > 0) {
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
           $image_table .= "<table><tr><td align=center><a data-ajax=\"false\" href=\"$extra_file_full\"><image src=\"". $extra_file_full_no_end . 
              "-klein." . $ending . "\"></a><br/>";
           $image_table .= utf8_htmlentities($extra_file);
           $image_table .= "</td></tr></table>\n";
         }
      }

      if (!(empty ($image_table))) $content .= "<hr>$image_table";
    }

    print_view ($links, $actions, function () {global $content; echo $content;}, $wikiUser);
  } else {
    print "Error: Unknown Mode: $mode";
  }



?>
