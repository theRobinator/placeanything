<?php
/*
 * This file is part of PlaceAnything.
 *
 *  PlaceAnything is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  PlaceAnything is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with PlaceAnything.  If not, see <http://www.gnu.org/licenses/>.
 */


// Run this file to recreate the image index. Doing so will clear your cache, so be careful.

// Make sure this is being run from the command line
if (PHP_SAPI !== 'cli') {
    die('This script must be run from the command line.');
}

require_once('settings.php');

// Connect to MySQL
mysql_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD) or die('Could not connect to MySQL!');
mysql_select_db(MYSQL_DATABASE) or die('Could not use database "' . MYSQL_DATABASE . '"');


// Get rid of everything first
mysql_query('DROP TABLE IF EXISTS images');
foreach (glob(CACHE_FOLDER . '/*') as $filename) {
    unlink($filename);
}

// Create the table
mysql_query('CREATE TABLE images (filename VARCHAR(64) PRIMARY KEY, width INT NOT NULL, height INT NOT NULL, original TINYINT DEFAULT 0)');

// Now go through the image files and create queries
$values = array();
foreach (glob(IMAGE_FOLDER . '/*') as $filename) {
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if (!in_array($extension, $SUPPORTED_IMAGE_TYPES)) {
        echo '"' . $extension . '" is not a supported type, so "' . $filename . '" was not included.' . "\n";
    }
    $imagesize = getimagesize($filename);
    $values[] = "('" . $filename . "', " . $imagesize[0] . ', ' . $imagesize[1] . ', 1)';
}
mysql_query('INSERT INTO images VALUES ' . implode(', ', $values));
?>
