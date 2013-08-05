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


// The path to the folder that stores your images.
const IMAGE_FOLDER = 'images';
// The path to the folder that stores the cache of requested images.
const CACHE_FOLDER = 'cache';


// Your MySQL connection info
const MYSQL_SERVER = 'localhost';
const MYSQL_USER = 'root';
const MYSQL_PASSWORD = '';
const MYSQL_DATABASE = 'placeanything';


// You don't need to worry about editing anything below this line.


// The image content types (No, adding things here won't magically add support)
$SUPPORTED_IMAGE_TYPES = array('jpg', 'gif', 'png');
$CONTENT_TYPES = array(
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'png' => 'image/png'
);
?>
