<h1>Filemaker Pro Importer - Wordpress Plugin</h1>
Filemaker Pro Importer is a Wordpress Plugin that imports a Filemaker Pro tab file (exported from Filemaker Pro), and associated images.

<h2>Specfics</h2>
This plugin was developed specifically for an artist website. 

Each line of the tab file is considered a record. In that record there is a field that contains an image name (without the ".jpg", and some associated meta data (artist first name, artist last name, etc). The following is a sample line from the tab file (6th field is the image name "81677_012014174326_753128"):

10.0	Upload	Public	81677	1	81677_011714161119_742203	81677_012014174326_753128	81677_060413181206_963783		1976		Letha	LW	Wilson					active												2014		30 3/8 x 34 3/8 x 2 inches											Dimensions:	2	30 3/8	34 3/8								Framed Dimensions:											Crate Dimensions:													LW_507_14	(LW_507_14)	Gallery		unique c-prints, concrete, emulsion transfer, welded aluminum frame				unique	10000	$10,000						Available

<h2>How It Works</h2>
1. Upload the zip file to your Wordpress plugins directory
2. Extract the zip
3. Activate the plugin (a subdirectory in the uploads directory will be created)
4. From the Dashboard, click Settings->Filemaker Pro Importer
5. Follow the instructions on the settings page.
6. Create a file named "newexport-utf8.tab". Copy/paste the sample line above into that file. Put that file in the newly created /wp-content/uploads/filemaker-pro-uploads directory. 
7. Create/use any image, and name it "81677_012014174326_753128.jpg", and then place that image into the same directory.
8. From the Filemaker Pro Importer Settings page, click "Process"

You get a message on error or success.

Once you have imported the data, take a look at your "wp_posts" and "wp_postmeta" tables. You will see the new image in the posts table, as type "attachment." In the postmeta table, you will see some custom fields that contain the artist info. This meta data is associated with the attachment post in the posts table.

Note that the script also creates various sizes of each imported image. The sizes are created depending on what is configured in Dashboard->Settings->Media.

Next create or edit a post. Click the "Add Media" button. You will see your new image in the Media Library. If you click on that image, you will see the artist first and last name in the "Attachment Details."
 
<h2>Source File Info</h2>
config.php - variables/constants for setting meta keys, record indexes, directory names, etc<br />
filemaker-pro-importer.php - main plugin file that is used to activation/initializationr<br />
FilemakerProImporter.class.php - PHP class file used by plugin file. This file contains all of the data processing functions.<br />
filemaker-pro-importer.zip - contains all plugin files. Use this file to install the plugin on your Wordpress site.
<br />


