<?php

/* This file is part of PHP Kochbuch
   Copyright (C) 2013-2016 Thomas Tuerk <thomas@tuerk-brechen.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
*/

  require_once('config.php');

  function utf8_htmlentities($str) {
    return (htmlspecialchars($str, ENT_QUOTES, "UTF-8"));
  }

  function str_startsWith ($string, $prefix) {
    return (substr($string, 0, strlen ($prefix)) == $prefix);
  }

  function str_endsWith ($string, $suffix) {
    return (substr($string, -(strlen ($suffix))) == $suffix);
  }

  function isSubcat ($subcat, $cat) {
    if (empty($cat)) return true;
    if ($cat == $subcat) return true;
    return (str_startsWith($subcat, $cat . "/"));
  }

  function recipe_to_html ($file) {
    return `pandoc --base-header-level=2 -f markdown --to html < $file`;
  }

  function recipe_to_txt ($file) {
    return `pandoc -f markdown --to plain < $file`;
  }

  function buildRecipeMailToLink($address, $file) {
     $title = get_recipe_title($file);
     $file_full = add_rezept_dir($file);
     $body = recipe_to_txt($file_full);

     $link = "mailto:" . rawurlencode($address) .
             "?subject=" . rawurlencode($title) .
             "&body=" . rawurlencode($body);
     return $link;
  }

  function output_file($name, $path) {
    if(!file_exists($path)) {
        header('HTTP/1.0 404 Not Found');
        exit();
    }

    // Set the content-type header
    header('Content-Type: '.mimeType($name));
    header('Content-Disposition: attachment; filename="'.urlencode($name).'"');

    // Read the file
    readfile($path);
  }

  function mimeType($path) {
    preg_match("|\.([a-z0-9]{2,4})$|i", $path, $fileSuffix);

    switch(strtolower($fileSuffix[1])) {
        case 'jpg' :
        case 'jpeg' :
        case 'jpe' :
            return 'image/jpg';
        case 'png' :
        case 'gif' :
        case 'bmp' :
        case 'tiff' :
            return 'image/'.strtolower($fileSuffix[1]);
        case 'css' :
            return 'text/css';
        case 'xml' :
            return 'application/xml';
        case 'doc' :
        case 'docx' :
            return 'application/msword';
        case 'xls' :
        case 'xlt' :
        case 'xlm' :
        case 'xld' :
        case 'xla' :
        case 'xlc' :
        case 'xlw' :
        case 'xll' :
            return 'application/vnd.ms-excel';
        case 'ppt' :
        case 'pps' :
            return 'application/vnd.ms-powerpoint';
        case 'rtf' :
            return 'application/rtf';
        case 'pdf' :
            return 'application/pdf';
        case 'epub' :
            return 'application/epub+zip';
        case 'html' :
        case 'htm' :
        case 'php' :
            return 'text/html';
        case 'txt' :
            return 'text/plain';
        case 'tex' :
            return 'application/x-tex';
        case 'mpeg' :
        case 'mpg' :
        case 'mpe' :
            return 'video/mpeg';
        case 'mp3' :
            return 'audio/mpeg3';
        case 'wav' :
            return 'audio/wav';
        case 'aiff' :
        case 'aif' :
            return 'audio/aiff';
        case 'avi' :
            return 'video/msvideo';
        case 'wmv' :
            return 'video/x-ms-wmv';
        case 'mov' :
            return 'video/quicktime';
        case 'zip' :
            return 'application/zip';
        case 'tar' :
            return 'application/x-tar';
        case 'swf' :
            return 'application/x-shockwave-flash';
        default :
            return 'unknown/' . trim($fileSuffix[0], '.');

    }
  }

  function is_valid_recipe_filename ($name) {
     return (preg_match('/^[a-zA-Z0-9\-_]+.md$/', $name));
  }

  function add_all_files_in_dir($dir, &$res) {
     $files = scandir($dir);
     foreach($files as $ff) {
        if($ff != '.' && $ff != '..') {
           $dff = $dir . "/" . $ff;
           if (is_dir($dff)) {
             add_all_files_in_dir($dff, $res);
           } else {
             $res[] = $dff;
           }
        }
     }
  }

  function add_rezept_dir ($file) {
    global $DATA_DIR;
    return "$DATA_DIR/".$file;
  }

  function remove_rezept_dir ($file) {
     global $DATA_DIR;
     $rezept_dir = "$DATA_DIR/";
     $len = strlen ($rezept_dir);
     if (substr ($file, 0, $len) == $rezept_dir) {
       return (substr ($file, $len));
     } else {return $file; }
  }

  function get_rezept_dir ($file) {
     $dir = add_rezept_dir(remove_rezept_dir($file)) . ".dir";
     if (!(is_dir ($dir))) {
       mkdir ($dir);
     }
     return $dir;
  }

  function get_kategorien_filename ($file) {
     return (get_rezept_dir($file) . "/kategorien");
  }

  function get_recipe_title ($file) {
     if (! (substr ($file, -3) == ".md")) return false;
     $file = add_rezept_dir(remove_rezept_dir($file));

     $handle = fopen($file, "r");
     if (! $handle) return false;
     $line = fgets($handle);
     fclose($handle);

     $title = ltrim (rtrim ($line));
     if (substr ($title, 0, 1) == "#") {
        $title = ltrim (substr ($title, 1));
     }

     if (empty ($title)) {
       $title = $file;
     }

     return $title;
  }

  function strcmpUmlauts($s1, $s2) {
    $search = array('Ä','Ö','Ü','ß', 'ä', 'ö', 'ü');
    $replace = array('Ae','Oe','Ue','ss', 'ae', 'oe', 'ue');
    return strcasecmp(
           str_ireplace($search, $replace, $s1),
           str_ireplace($search, $replace, $s2)
           );
  }

  function get_all_recipes($cat, $direct=false) {
    global $DATA_DIR;

    $all_files = array();
    add_all_files_in_dir ("$DATA_DIR", $all_files);

    $results = array();
    foreach ($all_files as $file) {
      $file2 = remove_rezept_dir($file);
      $title = get_recipe_title ($file2);

      if ($title) {
        if (!(recipe_in_cat ($file2, $cat, $direct))) continue;
        $results[] = array ('filename' => $file2, 'title' => $title);
      }
    }

    usort($results, function ($a,$b) { return (strcmpUmlauts ($a['title'], $b['title'])) ;} );
    return $results;
  }

  function get_recipe_extra_files($file) {
    $dir = add_rezept_dir($file) . ".dir";
    $all_files = array();
    add_all_files_in_dir ($dir, $all_files);

    $dir_len = strlen($dir) + 1;
    $results = array();
    foreach ($all_files as $file) {
      $file2 = substr ($file, $dir_len);
      if ($file2 == "kategorien") continue;
      $results[] = $file2;
    }

    usort($results, "strcmpUmlauts");
    return $results;
  }

  function get_recipes_no_cat() {
    global $DATA_DIR;

    $all_recipes = get_all_recipes(null);
    $results = array();
    foreach ($all_recipes as $recipe) {
      $file = $recipe["filename"];
      if (count (recipe_cats($file)) == 0) $results[] = $recipe;
    }

    return $results;
  }


  function clean_cat ($cat) {
     $parts = explode ("/", $cat);
     $current_cat = "";
     foreach ($parts as $part) {
       $part2 = ltrim(rtrim($part));
       if (empty ($part2)) continue;
       if (empty ($current_cat)) {
         $current_cat = $part2;
       } else {
         $current_cat .= "/" . $part2;
       }
     }
     return $current_cat;
  }

  function parse_cats_string ($cats_list) {
    $lines = preg_split ('/$\R?^/m', $cats_list);
    $result = array();

    foreach ($lines as $key => $line) {
      $parts = explode ("/", $line);
      $current_cat = "";
      foreach ($parts as $part) {
         $part2 = ltrim(rtrim($part));
         if (empty ($part2)) continue;
         if (empty ($current_cat)) {
            $current_cat = $part2;
         } else {
           $current_cat .= "/" . $part2;
         }
         $result[] = $current_cat;
      }
    }

    $result = array_unique($result);
    usort ($result, "strcmpUmlauts");
    return $result;
  }

  function recipe_cats ($file) {
    $cat_file = get_kategorien_filename ($file);
    $cats_list = file_exists($cat_file) ? file_get_contents($cat_file) : "";
    return (parse_cats_string($cats_list));
  }

  function recipe_set_cats ($file, $newcats) {
    $newcats = clean_cats_list($newcats);
    $cat_file = get_kategorien_filename ($file);
    $newcats = implode("\n", $newcats);
    file_put_contents($cat_file, $newcats);
    git ("stage '$file.dir'");
  }

  function get_all_cats() {
    global $DATA_DIR;

    $all_files = array();
    add_all_files_in_dir ("$DATA_DIR", $all_files);

    $results = array();
    foreach ($all_files as $file) {
      if (!(substr($file, -10) == "kategorien")) continue;

      $cats = parse_cats_string(file_get_contents($file));
      foreach ($cats as $cat) {
        $count = isset($results[$cat]) ? $results[$cat] : 0;
        $results[$cat] = $count + 1;
      }
    }

    uksort ($results, "strcmpUmlauts");
    return $results;
  }

  function clean_cats_list ($cats) {
    arsort($cats);
    $last = "";
    $result = array ();
    foreach ($cats as $cat) {
      $cat = clean_cat($cat);
      if (!(isSubcat ($last, $cat))) $result [] = $cat;
      $last = $cat;
    }
    usort($result, "strcmpUmlauts");
    return $result;
  }

  function clean_cats_array ($cats) {
    krsort($cats);
    $last = "";
    $result = array ();
    foreach ($cats as $cat => $count) {
      $cat = clean_cat($cat);
      if (!(isSubcat ($last, $cat))) $result [$cat] = $count;
      $last = $cat;
    }
    uksort($result, "strcmpUmlauts");
    return $result;
  }

  function remove_subcats ($cats) {
    ksort($cats);
    $last = "";
    $result = array ();
    foreach ($cats as $cat => $count) {
      $cat = clean_cat($cat);
      if ((!(isSubcat ($cat, $last))) || ($last == "")) {
        $result [$cat] = $count;
        $last = $cat;
      }
    }
    uksort($result, "strcmpUmlauts");
    return $result;
  }

  function get_subcats($cat) {
    $cats = get_all_cats ();

    $results = array();
    foreach ($cats as $subcat => $count) {
      if ((isSubcat($subcat, $cat)) && !($subcat == $cat)) {
        $results[$subcat] = $count;
      }
    }
    uksort($results, "strcmpUmlauts");
    return $results;
  }

  function get_direct_subcats($cat) {
    $cats = get_subcats($cat);
    return (remove_subcats($cats));
  }

  function recipe_in_cat ($file, $cat, $direct=false) {
    $cat_file = get_kategorien_filename ($file);
    if (! (file_exists ($cat_file))) return (!(isset($cat)));
    $lines = file($cat_file);

    if (!(isset($cat))) {
      return ((! $direct) || (count($lines) == 0));
    }

    $cat = clean_cat ($cat);
    foreach ($lines as $line) {
      $line2 = clean_cat($line);
      if ($direct) {
        if ($line2 == $cat) return true;
      } else {
        if (isSubcat($line2,$cat)) return true;
      }
    }
    return false;
  }

  function add_cat_to_recipe ($file, $cat) {
    $cats = recipe_cats ($file);
    $cats[] = clean_cat($cat);
    usort($cats, "strcmpUmlauts");
    recipe_set_cats($file, $cats);
  }

  function remove_cat_from_recipe ($file, $del_cat) {
    $cats = recipe_cats ($file);
    $del_cat = clean_cat($del_cat);
    foreach ($cats as $key => $cat) {
       if (isSubcat(clean_cat($cat), $del_cat))
       unset($cats[$key]);
    }
    recipe_set_cats($file, $cats);
  }

  function getAuthorFullForUser($user) {
    global $AUTHORS, $DEFAULT_AUTHOR;

    if (empty ($user)) {
      return "";
    } else if (isset($AUTHORS[$user])) {
      return $AUTHORS[$user][1];
    }
    return $DEFAULT_AUTHOR;
  }

  function dropAuthorEmail($res) {
    $pos = strpos($res, '<');
    if ($pos) {
      $res = substr($res, 0, $pos);
    }
    $res = rtrim ($res);
    return $res;
  }

  function getAuthorEmail($res) {
    $pos = strpos($res, '<');
    if ($pos) {
      $res = substr(rtrim($res), $pos+1, -1);
    }
    $res = ltrim ($res);
    return $res;
  }

  function getAuthorForUser($user) {
    $res = getAuthorFullForUser($user);
    $res = rtrim (dropAuthorEmail($res));
    return $res;
  }

  function git($command, &$output = "") {
    global $GIT, $DATA_DIR;

    $gitDir = dirname(__FILE__) . "/$DATA_DIR/.git";
    $gitWorkTree = dirname(__FILE__) . "/$DATA_DIR";
    $gitCommand = "cd $DATA_DIR; $GIT $command";
    $output = array();
    exec($gitCommand . " 2>&1", $output, $result);
    if ($result != 0) {
      // FIXME: HTMLify these strings
      print "<h1>Error</h1>\n<pre>\n";
      print "$" . $gitCommand . "\n";
      print join("\n", $output) . "\n";
      //print "Error code: " . $result . "\n";
      print "</pre>";
      return 0;
    }
    return 1;
  }

  function git_commit($message, &$output = "") {
     global $wikiUser, $USE_EXTERNAL_GIT;
     $author = addslashes(getAuthorFullForUser($wikiUser));
     $command = "commit --message='$message' --author='$author'";
     git($command, $output);
     if ($USE_EXTERNAL_GIT) {
        git ("push -v");
     }
  }

  function git_pull() {
    global $USE_EXTERNAL_GIT;
    if ($USE_EXTERNAL_GIT) {
      git ("pull");
      git ("clean -f");
    }
  }

  function git_stage($file) {
    if (file_exists (add_rezept_dir ($file))) {
       git ("stage '$file'");
    }
  }

  function git_rm($file) {
    if (file_exists (add_rezept_dir ($file))) {
      if (is_dir (add_rezept_dir ($file))) {
        git ("rm -r '$file'");
      } else {
        git ("rm '$file'");
      }
    }
  }

  function git_mv($file_old, $file_new) {
    if (file_exists (add_rezept_dir ($file_old))) {
      git ("mv '$file_old' '$file_new'");
    }
  }

  function git_last_change($file) {
    global $USE_EXTERNAL_GIT;
    $output = array();
    git ("log --format=\"%H%n%an%n%ae%n%ad\" '$file'", $output);
    $i = 0;
    $result = "";
    while($i < count($output)) {
      $author_name = dropAuthorEmail($output[$i + 1]);
      $author_email = getAuthorEmail($output[$i + 2]);
      $author = "<a href=\"mailto:$author_email\">" . htmlentities($author_name) . "</a>";

      $date_raw = $output[$i+3];
      $date = strftime("%e. %b %Y   %H:%M", strtotime($date_raw));

      $commit_rev = $output[$i];
      if ($USE_EXTERNAL_GIT) {
        $lnk = git_revision_link($commit_rev);
      } else {
        $lnk = "";
      }
      if ($lnk == "") {
        $rev = "(" . substr($commit_rev, 0, 7) . ")";
      } else {
        $rev = "(<a target=\"_gitrev\" href=\"$lnk\">" . substr($commit_rev, 0, 7) . "</a>)";
      }
      $result .= "$author am $date $rev<br/>";

      $i += 4;
    }

    return $result;
  }

  function get_new_recipes() {
    $result = array();
    git ("log --format=\"%H%n%an%n%ae%n%ad\" --name-status", $output);
    $i = 0;
    while($i < count($output)) {
      $rev = array();
      $rev["author_name"] = dropAuthorEmail($output[$i + 1]);
      $rev["author_email"] = getAuthorEmail($output[$i + 2]);
      $date_raw = $output[$i+3];
      $rev["date"] = strtotime($date_raw);
      $rev["no"] = $output[$i];
      $i += 5;

      while ($i < count($output) && (str_startsWith ($output[$i], "M") || str_startsWith ($output[$i], "A") || str_startsWith ($output[$i], "D"))) {
        $file_full = $output[$i];
        $i++;

        if (!(str_startsWith ($file_full, "A"))) continue;
        if (!(str_endsWith ($file_full, ".md"))) continue;
	$file = substr ($file_full, 2);
        if (isset ($result[$file])) continue;
        if (!file_exists(add_rezept_dir(remove_rezept_dir($file)))) continue;
	$result[$file] = $rev;
      }
    }
    return $result;
  }

  function mk_cat_link ($cat, $count) {
     $arr = explode("/", $cat);
     $last = array_pop($arr);
     return "<a href=\"?mode=cathegory&cat=" . (urlencode ($cat)) . "\">" .
          utf8_htmlentities($last) . "</a> (" . $count . ")";
  };

  function format_cat_array_as_tree ($cat, &$all_cats) {
    $content = "";
    while (true) {
      if (!($current_cat = key($all_cats))) break;
      if (isSubcat($current_cat, $cat)) {
        $content .= "<li>" . mk_cat_link ($current_cat, current($all_cats)) . "</li>\n";
        next($all_cats);
        $content .= format_cat_array_as_tree($current_cat, $all_cats);
      } else {
        break;
      }
    }
    if (empty($content)) {
      return "";
    } else {
      return ("<ul>\n" . $content . "</ul>\n\n");
    }
  };

  function is_image_file ($file) {
     $file_array = explode(".", $file);
     $ending = array_pop ($file_array);
     if ($ending == "jpg") return true;
     if ($ending == "jpeg") return true;
     if ($ending == "JPG") return true;
     if ($ending == "JPEG") return true;
     return false;
  }

  function create_small_image($file_no_end, $ending) {
    $org_file = "$file_no_end.$ending";
    $small_file = "$file_no_end-klein.$ending";
    if (file_exists ($small_file)) {
       if (filemtime($small_file) > filemtime($org_file)) return;
    }
    `convert $org_file -scale 300 $small_file`;
  }

  function format_cat($file_full, $tmpfile, $cat_filename, $filetype) {
    global $TMP_DIR;
    if ($filetype == "pdf4") {
       $out_filename = "$cat_filename.pdf";
       exec ("pandoc --toc --template a4.tmpl -f markdown -o $tmpfile.tex < $file_full");
       exec("cd $TMP_DIR; pdflatex $tmpfile.tex > /dev/null 2>&1");
       exec("cd $TMP_DIR; pdflatex $tmpfile.tex > /dev/null 2>&1");
       exec("cd $TMP_DIR; pdflatex $tmpfile.tex > /dev/null 2>&1");
       output_file ($out_filename, "$tmpfile.pdf");
    } else if ($filetype == "pdf5") {
       $out_filename = "$cat_filename.pdf";
       exec ("pandoc --toc --template a5.tmpl -f markdown -o $tmpfile.tex < $file_full");
       exec("cd $TMP_DIR; pdflatex $tmpfile.tex > /dev/null 2>&1");
       exec("cd $TMP_DIR; pdflatex $tmpfile.tex > /dev/null 2>&1");
       exec("cd $TMP_DIR; pdflatex $tmpfile.tex > /dev/null 2>&1");
       output_file ($out_filename, "$tmpfile.pdf");
    } else if ($filetype == "pdf5x2") {
       $out_filename = "$cat_filename.pdf";
       exec("pandoc --toc --template a5.tmpl -f markdown -o $tmpfile.tex < $file_full");
       exec("cd $TMP_DIR; latex $tmpfile.tex > /dev/null 2>&1");
       exec("cd $TMP_DIR; latex $tmpfile.tex > /dev/null 2>&1");
       exec("cd $TMP_DIR; latex $tmpfile.tex > /dev/null 2>&1");
       exec("dvips -t a5 -o $tmpfile-1.ps $tmpfile.dvi > /dev/null 2>&1");
       exec("psnup -Pa5 -pa4 -n 2 $tmpfile-1.ps $tmpfile.ps > /dev/null 2>&1");
       exec("gs -q -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=$tmpfile.pdf $tmpfile.ps > /dev/null 2>&1");
       output_file ($out_filename, "$tmpfile.pdf");
    } else {
       $out_filename = $cat_filename . "-" . strftime("%Y-%m-%d---%H-%M-%S") . "." . $filetype;
       exec("pandoc -f markdown -o $tmpfile.$filetype < $file_full");
       output_file ($out_filename, "$tmpfile.$filetype");
    }
  }


  function check_filename ($file, $old) {
      $warning = "";
      if (is_valid_recipe_filename ($file)) {
        if ((!($file == $old)) && (file_exists (add_rezept_dir($file)))) {
          $warning .= "<div class=\"warning\">Datei '$file' existiert bereits</div>";
        }
      } else {
        $warning .= "<div class=\"warning\">ungültiger Dateiname '$file'</div>";
      }
      return $warning;
  }

  function save_file ($file, $file_new, $data, $newcats) {
      if (! ($file == $file_new)) {
         $file_old = $file;
         $file = $file_new;
         git_mv($file_old, $file);
         git_mv("$file_old.dir", "$file.dir");
      }
      file_put_contents(add_rezept_dir($file), str_replace("\r", "", $data));
      recipe_set_cats($file, parse_cats_string($newcats));
      git_stage($file);
      git_stage("$file.dir");
      git_commit($_POST['commit']);
  }

?>