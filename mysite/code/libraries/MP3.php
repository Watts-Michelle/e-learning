<?php

class MP3
{

	/** @var File  */
	private $file;

	public function __construct(File $file)
	{
		$this->checkExists($file);
		$this->checkType($file);
		$this->file = $file;
	}

	private function checkExists(File $file)
	{
		if (! file_exists($file->getFullPath())) {
			throw new Exception('File not found: ' . $file->Filename);
		}

		return true;
	}

	private function checkType(File $file) {
		$info = new SplFileInfo($file->getFullPath());

		if ($info->getExtension() != 'mp3') {
			throw new Exception('File not an mp3: ' . $file->Filename);
		}

		return true;
	}

	public function getDuration()
	{
		$abs = new Zend_Media_Mpeg_Abs($this->file->getFullPath(), ['readmode' => 'lazy']);
		return $length = round($abs->getLength());
	}

	public function getEstimatedDuration()
	{
		$abs = new Zend_Media_Mpeg_Abs($this->file->getFullPath());
		return $length = round($abs->getLengthEstimate());
	}

	public function resample($background = true) {
		// Resample to 48 kbps for low quality connections
		$input = $this->file->getFullPath();
		$output = preg_replace('/\.mp3$/', '-48kbps.mp3', $input);

		// Use lame to do the resampling and throw it into the background so it
		// doesn't block
		system("/usr/local/bin/lame --mp3input -b 48 \"$input\" \"$output\" " . (($background) ? '&':''));
	}
}