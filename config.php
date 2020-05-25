<?php

/* This file is part of PHP Kochbuch
   Copyright (C) 2013-2016 Thomas Tuerk <thomas@tuerk-brechen.de>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
*/

  date_default_timezone_set('Europe/Berlin');
  setlocale(LC_TIME, "de_DE.utf8");

  // Location of the Git binary
  $GIT = "/usr/bin/git";

  $REALM = "Kochbuch";

  //temp dir, this needs to be writeable by the webserver
  $TMP_DIR = "/tmp";

  // Dir that contains the data files (i.e. the Git repository). This directory
  // must be writable by the web server.
  $DATA_DIR = "data";

  // The default author of the git commits.
  $DEFAULT_AUTHOR = 'unbekannt';

  // EMail Adress of IOTP printer, null indicates no IOTP printer
  //$IOTP_EMAIL = "???@???.de";

  $USE_EXTERNAL_GIT = false;
  $USE_EXTERNAL_GIT_VIEWER=false;

  // Link to Webview of Git Repository of Recipes, e.g. a gitlist instance
  function git_revision_link($rev) {
     return "https://git.tuerk-brechen.de/Tuerk/rezepte/commit/$rev";
  }
  function git_history_link($file) {
     return "https://git.tuerk-brechen.de/Tuerk/rezepte/commits/branch/master/$file";
  }

  $GIT_NAME = "Gitea";
  $GIT_MASTER_LINK = "https://git.tuerk-brechen.de/Tuerk/rezepte/commits/branch/master";

  // Set the mappings from HTTP username to Git commit author.
  $AUTHORS = array(
    "admin" => array("admin", "Admin <thomas@tuerk-brechen.de>")
  );
?>
