<?php 
class VimeoFile extends DataObject {

    /** @var array  Define the required fields for the VimeoFile table */
    protected static $db = array(
    	'VimeoID' => 'Varchar(20)',
		'SecondaryID' => 'Varchar(20)'
	);
    
    protected static $has_one = array(
    	'Lesson' => 'Lesson'
	);
    
    protected static $has_many = array(
    	'VimeoFileURL' => 'VimeoFileURL'
	);
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array();

	public function getLink()
	{
		return 'https://player.vimeo.com/video/' . $this->VimeoID . '?background=1&mute=0';
	}

	public function setIDsFromURI($uri)
	{
		// Old method of fetching a Primary and Seconday ID does not seem to be applicable anymore - MJS 12/05/2017
//		preg_match('/(?<=\:)(.*)/', $uri, $secondaryID);
//		preg_match('/(?<=\/videos\/)(.*)(?=\:)/', $uri, $vimeoID);
		preg_match('/\/videos\/(.*)/', $uri, $vimeoID);
//		preg_match('/(?<=\:)(.*)/', $uri, $secondaryID);

		if (isset($vimeoID[1])) {
			$this->VimeoID = $vimeoID[1];
		}

//		if (isset($secondaryID[0])) {
//			$this->SecondaryID = $secondaryID[0];
//		}

		return true;
	}

	public function getLinks()
	{
		if (! $this->VimeoFileURL()->count()) return false;

		$config = SiteConfig::current_site_config();

		$files = $this->VimeoFileURL()->filter(['Height:GreaterThanOrEqual' => $config->MinimumProvidedVideoHeight]);

		$returnArray = [];

		foreach ($files as $file) {
			$returnArray[] = [
				'size' => $file->Height,
				'link' => $file->Link
			];
		}

		return $returnArray;
	}
}

class AltVimeoFile extends VimeoFile {

}