<?php
class FilemakerProImporter {
	
	private $pluginVersion;
	private $imageDirectory;
	private $tabFile;
	
	private $artworkID;
	private $artistID;
	private $artistFirstName;
	private $artistLastName;
	private $artworkDescription;
	private $artworkYear;
	private $artworkMaterials;
	private $artworkDimensions;
	private $artworkExhibition;
	private $artworkImageName;
	private $isImportedArtwork;
	
	private $imagePath;
	private $imageExtension;
	
	// post meta keys
	private $artistFirstNameMetaKey;
	private $artistLastNameMetaKey;
	private $artworkDescriptionMetaKey;
	private $artworkYearMetaKey;
	private $artworkMaterialsMetaKey;
	private $artworkDimensionsMetaKey;
	private $artworkExhibitionMetaKey;
	private $isImportedArtworkMetaKey;
	
	private $numRecordsProcessed;
	
	function FilemakerProImporter() {
		// get all values from config file...not great OOP...
		include( plugin_dir_path( __FILE__ ) . 'config.php');
		
		$upload_dir = wp_upload_dir();
		
		$this->dataTable = FMP_DATA_TABLE_NAME;
		$this->postTable = FMP_POST_TABLE_NAME;
		$this->pluginVersion = FMPI_VERSION;
		// this is path to use in db
		$this->imageDirectory = $upload_dir['baseurl']."/".FMP_UPLOAD_DIR."/";
		$this->imagePath = $upload_dir['basedir']."/".FMP_UPLOAD_DIR."/";
		$this->tabFile = $upload_dir['basedir']."/".FMP_UPLOAD_DIR."/".FMP_DATA_FILENAME;
		
		//error_log("image dir: ".$this->imageDirectory);
		//error_log("tab: ".$this->tabFile);
		
		$this->artworkID = FMP_ARTWORK_ID;
		$this->artistID = FMP_ARTIST_ID;
		$this->artistFirstName = FMP_ARTIST_FIRST_NAME;
		$this->artistLastName = FMP_ARTIST_LAST_NAME;
		$this->artworkDescription = FMP_ARTWORK_DESCRIPTION;
		$this->artworkYear = FMP_ARTWORK_YEAR;
		$this->artworkMaterials = FMP_ARTWORK_MATERIALS;
		$this->artworkDimensions = FMP_ARTWORK_DIMENSIONS;
		$this->artworkExhibition = FMP_ARTWORK_EXHIBITION;
		$this->artworkImageName = FMP_ARTWORK_IMAGE_NAME;
		$this->isImportedArtwork = FMP_IS_IMPORTED_ARTWORK;
		
		//$this->imagePath = FMP_IMAGE_PATH;
		$this->imageExtension = FMP_IMAGE_EXTENSION;
		
		// post meta keys
		$this->artistFirstNameMetaKey = FMP_ARTIST_FIRST_NAME_META;
		$this->artistLastNameMetaKey = FMP_ARTIST_LAST_NAME_META;
		$this->artworkDescriptionMetaKey = FMP_ARTWORK_DESCRIPTION_META;
		$this->artworkYearMetaKey = FMP_ARTWORK_YEAR_META;
		$this->artworkMaterialsMetaKey = FMP_ARTWORK_MATERIALS_META;
		$this->artworkDimensionsMetaKey = FMP_ARTWORK_DIMENSIONS_META;
		$this->artworkExhibitionMetaKey = FMP_ARTWORK_EXHIBITION_META;
		
		$this->isImportedArtworkMetaKey = FMP_IS_IMPORTED_ARTWORK_META;
		
		$this->numRecordsProcessed = 0;
		
	}

	// creates directory for the tab file and images
	public function createDirectory() {
		global $wpdb;
		
		if (!is_dir($this->imagePath)) {
			if(!(wp_mkdir_p($this->imagePath))) {
				error_log("FMP: error creating the subdirectory");
			}	
		} 
		
		add_option( "fmp_db_version", $this->pluginVersion );
		
		return true;
	}
	
	// make sure the data file is where its supposed to be
	public function dataFileExists() {
		if(!file_exists($this->tabFile)) {
			error_log("FilemakerProImporter: Data file not found: ".$this->tabFile);
			return false;
		} else {	
			return true;
		}
	}
	
	// FilemakerPro exported data must be in tab delimited, UTF-8 format and match predefined fields.
	// Please note that this function is called from fmp_plugin_options(), which ensures that the tables exist
	public function importFmpData() {
		
		// keep track of any images that are in tab file but not found in the subdirectory
		$missingImageFiles = "";
		
		// keep track of how many records were successfully processsed
		$processedRecords = 0;
		
		// first read in the tab file
		$contents = file_get_contents($this->tabFile);
		
		// now explode file into lines
		$lines = explode("\x0D",$contents);
		
		// iterate through each line of tab file
		for($i=0;$i<count($lines)-1;$i++) {
			// split data into array
			$data_array = explode("\t",$lines[$i]);
			
			// if image is already in the database, skip it
			$imagePost = get_page_by_title(html_entity_decode($data_array[$this->artworkImageName]),OBJECT,'attachment');
			
			if($imagePost !== NULL) {
			
				$imageName = get_the_title( $imagePost->ID );
				
				if($imageName == $data_array[$this->artworkImageName]) {
					continue;
				}
			}
			
			// make sure the actual image file exists
			$imageFile = $this->imagePath.$data_array[$this->artworkImageName].$this->imageExtension;
			if(!file_exists($imageFile)) {
				$missingImageFiles .= "<br>".$imageFile."<br>";
				continue;
			} else {
				// add image and meta data to the db
				$success = $this->processImage($imageFile,$data_array);
				++$this->numRecordsProcessed;
			}
			
		}
		
		if(strlen($missingImageFiles) > 0) $missingImageFiles = "<br>Missing images: ".$missingImageFiles;
		
		return $missingImageFiles;
	}
	
	/**
	 * Add the image to the posts table as a post
	 * Add the meta data for this image to the postmeta table
	**/
	private function processImage($filename, $data_array) {
		$wp_filetype = wp_check_filetype(basename($filename), null );
		
		// values to be added to the posts table
		$attachment = array(
			'guid' => $this->imageDirectory . basename( $filename ), 
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename );
		
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		 
		/* generate metadata for an image attachment. Also creates a thumbnail and 
		 * other intermediate sizes of the image attachment based on the sizes defined 
		 * in Dashboard->Settings->Media
		 */
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		 
		// Update metadata for an attachment. 
		$res = wp_update_attachment_metadata( $attach_id, $attach_data );
		 
		// now add some custom meta for this image
		 
		// ARTIST - First Name
		update_post_meta ($attach_id, $this->artistFirstNameMetaKey, $data_array[$this->artistFirstName]);
		 
		 // ARTIST - Last Name
		update_post_meta ($attach_id, $this->artistLastNameMetaKey, $data_array[$this->artistLastName]);
		 
		// ARTIST - Artwork Description
		update_post_meta ($attach_id, $this->artworkDescriptionMetaKey, $data_array[$this->artworkDescription]);
		 
		// ARTIST - Artwork Year
		update_post_meta ($attach_id, $this->artworkYearMetaKey, $data_array[$this->artworkYear]);
		 
		// ARTIST - Artwork Materials
		update_post_meta ($attach_id, $this->artworkMaterialsMetaKey, $data_array[$this->artworkMaterials]);
		 
		// ARTIST - Artwork Dimensions
		update_post_meta ($attach_id, $this->artworkDimensionsMetaKey, $data_array[$this->artworkDimensions]);
		 
		// ARTIST - Artwork Exhibition
		update_post_meta ($attach_id, $this->artworkExhibitionMetaKey, $data_array[$this->artworkExhibition]);
		 
		// so we can get all posts that are images and are imported artwork...what is someone adds manually?
		update_post_meta ($attach_id, $this->isImportedArtworkMetaKey, $this->isImportedArtwork);
		
		return true;
	}
	
	public function getNumRecordsProcessed() {
		return $this->numRecordsProcessed;	
	}
}
?>