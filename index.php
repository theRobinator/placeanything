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


require_once('settings.php');

// Get the parameters from either the path or the query string
if (isset($_GET['w']) && isset($_GET['h'])) {
    $requestedWidth = $_GET['w'];
    $requestedHeight = $_GET['h'];
} else {
    $myPath = dirname($_SERVER['PHP_SELF']);
    $requestUri = strtok($_SERVER["REQUEST_URI"],'?');  // Without the query string if it's there
    $pathArgs = explode('/', substr($requestUri, strlen($myPath) + 1));
    $requestedWidth = $pathArgs[0];
    $requestedHeight = $pathArgs[1];
}

if (!intval($requestedWidth) || !intval($requestedHeight)) {
    die('Call me with /width/height or by passing the w and h GET parameters.');
}

// Connect to MySQL
mysql_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD) or die('Could not connect to MySQL!');
mysql_select_db(MYSQL_DATABASE) or die('Could not use database "' . MYSQL_DATABASE . '"');


// Get the closest images out of the database based on width and height
$possibleImages = array();
$exactMatch = null;
$result = mysql_query('SELECT *, ABS(' . $requestedWidth . ' - width) AS delta FROM images ORDER BY delta LIMIT 10');
while ($row = mysql_fetch_array($result)) {
    if ($row['original'] == 1) {
        // We should only resize the original source images to create new ones
        $possibleImages[] = $row;
    }
    if ($row['width'] == $requestedWidth && $row['height'] == $requestedHeight) {
        $exactMatch = $row;
        break;
    }
}
if ($exactMatch === null) {
    $result = mysql_query('SELECT *, ABS(' . $requestedHeight . ' - height) AS delta FROM images WHERE original=1 ORDER BY delta LIMIT 3');
    while ($row = mysql_fetch_array($result)) {
        $possibleImages[] = $row;
    }
}

if ($exactMatch === null) {
    // Cache the area of the requested image for error checking
    $targetArea = $requestedWidth * $requestedHeight;

    // Without an exact match in the DB, we'll need to create one. To determine which image to scale,
    // we'll minimize the number of pixels that have to be added or subtracted during the scale/crop
    // process.
    $minError = 1000000;
    $imageToScale = null;
    foreach($possibleImages as $imageRow) {
        $width = $imageRow['width'];
        $height = $imageRow['height'];
        // Determine the number of new pixels that would be created by scaling
        $error = abs($targetArea - $width * $height);

        // Determine how many pixels would be deleted by cropping after scaling
        // First, get the dimensions that the image would scale into
        $ratio = (1.0 * $requestedWidth) / $width;
        if ($height * $ratio < $requestedHeight) {
            $ratio = (1.0 * $requestedHeight) / $height;
        }
        $scaledWidth = intval($width * $ratio);
        $scaledHeight = intval($height * $ratio);
        // The scaled image is now larger, so we subtract the target area to get the extra pixels
        $error += abs($scaledWidth * $scaledHeight - $targetArea);

        if ($error < $minError) {
            $minError = $error;
            $imageToScale = $imageRow;
        }
    }

    // Open up the old image so we can scale it
    $oldFilepath = $imageToScale['filename'];
    $extension = pathinfo($oldFilepath, PATHINFO_EXTENSION);
    if ($extension == 'jpg') {
        $oldImage = imagecreatefromjpeg($oldFilepath);
    } elseif ($extension == 'gif') {
        $oldImage = imagecreatefromgif($oldFilepath);
    } else {
        $oldImage = imagecreatefrompng($oldFilepath);
    }

    // Copy the old image into a new one, scaling as we go
    $newImage = imagecreatetruecolor($requestedWidth, $requestedHeight);
    $ratio = (1.0 * $requestedWidth) / $imageToScale['width'];
    if ($imageToScale['height'] * $ratio < $requestedHeight) {
        $ratio = (1.0 * $requestedHeight) / $imageToScale['height'];
    }

    // Get coordinates for passing into imagecopyresampled
    $startX = 0;
    $startY = 0;
    $srcWidth = $imageToScale['width'];
    $srcHeight = $imageToScale['height'];
    $scaledWidth = intval($srcWidth * $ratio);
    $scaledHeight = intval($srcHeight * $ratio);

    if ($scaledWidth > $requestedWidth) {
        // Skip copying part of the source, effectively cropping it
        $unscaledWidth = intval($requestedWidth / $ratio);
        $startX = intval(($imageToScale['width'] - $unscaledWidth) / 2);
        $srcWidth = $unscaledWidth;
    } elseif ($scaledHeight > $requestedHeight) {
        $unscaledHeight = intval($requestedHeight / $ratio);
        $startY = intval(($imageToScale['height'] - $unscaledHeight) / 2);
        $srcHeight = $unscaledHeight;
    }

    imagecopyresampled($newImage, $oldImage, 0, 0, $startX, $startY, $requestedWidth, $requestedHeight, $srcWidth, $srcHeight);

    // Write the new image to the DB and the cache folder in case the same size is requested again
    $filename = CACHE_FOLDER . '/' . $requestedWidth . 'x' . $requestedHeight . '.jpg';
    imagejpeg($newImage,  $filename);
    mysql_query('INSERT INTO images VALUES ("' . $filename . '", ' . $requestedWidth . ', ' . $requestedHeight . ', 0)');

    $matchedImage = array(
        'filename' => $filename,
        'width' => $requestedWidth,
        'height' => $requestedHeight
    );

} else {
    // An exact match was found, we're good
    $matchedImage = $exactMatch;
}


// Send the image data along with the correct Content-Type
header('Content-Type', $CONTENT_TYPES[pathinfo($matchedImage['filename'], PATHINFO_EXTENSION)]);
echo file_get_contents($matchedImage['filename']);
?>
