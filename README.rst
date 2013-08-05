#############
PlaceAnything
#############

PlaceAnything is an image placeholder script that generates images based on a
given width and height. This is useful mainly for web designers who need images
of exact sizes but don't want to go to the trouble of creating them.

You may have already seen placekitten.com or placedog.com; this script does the
same thing, except that instead of kittens, it uses a folder on your computer.
This means that you can make your very own placekitten, placebunny, or
placearmadillo, using whatever images you want.


Requirements
============

- PHP (Tested on 5.3.15)
- php-gd for working with images (This was already installed on my mac)
- Apache
- MySQL


Setup
=====

Once you've dropped PlaceAnything in a place that your Apache can serve it,
open up settings.php. Change the values of the MySQL consts so that they will
work with your system.

After you've updated the values, **create the database in MySQL**.


Usage
=====

When everything is in place, run the reindex.php script. This will fill in the
database with all of the images in your source folder. You'll need to re-run
that script whenever you want to change images.

After you've run the script, hit up the script using either /{width}/{height} or
?w={width}&h={height}.


Just in Case
============

Sometimes you will run into a 404 error when trying to use the /{width}/{height}
notation. This means that somewhere in your Apache configuration there is a
line that says "AllowOverride None". Find that line (usually in
/etc/apache2/sites-enabled or /etc/apache2/httpd.conf) and change it to say
"AllowOverride FileInfo" or "AllowOverride All".
