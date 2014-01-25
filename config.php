<?php
define('FMPI_VERSION', '1.0.0');

// name of the subdirectory in "wp-content/uploads" where we will find the tab and jpg files
define('FMP_UPLOAD_DIR',"filemaker-pro-uploads");

// the tab file to process
define('FMP_DATA_FILENAME','newexport-utf8.tab');

// the filemaker pro import only includes the filename, with no path and no file extension
$path = content_url().'/';
define('FMP_IMAGE_PATH',$path);
define('FMP_IMAGE_EXTENSION','.jpg');

// these indices don't match up with the tab export. 
// But if you echo out the array for each line of the file, they match up. No idea why.
define('FMP_ARTWORK_ID',5); //60
define('FMP_ARTIST_ID',7); //82
define('FMP_ARTIST_FIRST_NAME',11); //done
define('FMP_ARTIST_LAST_NAME',13); //done
define('FMP_ARTWORK_DESCRIPTION',96); //30
define('FMP_ARTWORK_YEAR',30); //29
define('FMP_ARTWORK_MATERIALS',19); //17
define('FMP_ARTWORK_DIMENSIONS',32); //31
define('FMP_ARTWORK_EXHIBITION',80);
define('FMP_ARTWORK_IMAGE_NAME',6); //71

define('FMP_IS_IMPORTED_ARTWORK',1);

// use these values to get the post_meta for each image...
define('FMP_ARTIST_FIRST_NAME_META','fmp_artist_first_name');
define('FMP_ARTIST_LAST_NAME_META','fmp_artist_last_name');
define('FMP_ARTWORK_DESCRIPTION_META','fmp_artwork_description');
define('FMP_ARTWORK_YEAR_META','fmp_artwork_year');
define('FMP_ARTWORK_MATERIALS_META','fmp_artwork_materials');
define('FMP_ARTWORK_DIMENSIONS_META','fmp_artwork_dimensions');
define('FMP_ARTWORK_EXHIBITION_META','fmp_artwork_exhibition');

define('FMP_IS_IMPORTED_ARTWORK_META','fmp_is_imported_image');

?>