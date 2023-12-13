<?php

/**
 * Created by PhpStorm.
 * User: jonathanlittle
 * Date: 28/07/2016
 * Time: 09:41
 */
class VimeoUpload
{

	private $vimeo;

	/** @var Lesson $lesson */
	private $lesson;

	public function __construct($id, $secret, $access_token, Lesson $lesson)
	{
		$this->vimeo = new Vimeo\Vimeo($id, $secret, $access_token);
		$this->lesson = $lesson;
	}

	/**
	 * Upload the file to Vimeo
	 */
	public function upload()
	{
		if (! file_exists($this->lesson->MediaFile()->getFullPath())) throw new Exception('File to upload does not exist: ' . $this->lesson->MediaFile()->getFullPath());

		//  Send this to the API library.
		$uri = $this->vimeo->upload($this->lesson->MediaFile()->getFullPath());

		//  Now that we know where it is in the API, let's get the info about it so we can find the link.
		$video_data = $this->update(['name' => $this->lesson->Name], $uri);

		if ($video_data['status'] !== 200) throw new Exception('Unable to upload video');

		return $video_data;
	}

	public function update($fields, $uri)
	{
		$this->vimeo->request($uri, $fields, 'PATCH');
		return $this->vimeo->request($uri);
	}

	public function getFiles()
	{

		if (! empty($this->lesson->VimeoFile())) {
			// $url = '/videos/' . $this->lesson->VimeoFile()->VimeoID . ':' . $this->lesson->VimeoFile()->SecondaryID;
			$url = '/videos/' . $this->lesson->VimeoFile()->VimeoID;
			$video_data = $this->vimeo->request($url, null, 'GET');
		}

		if (empty($video_data) || $video_data['status'] !== 200) throw new Exception('Unable to get video');
		return $video_data['body']['files'];
	}

}



class VimeoUploadAlt
{

	private $vimeo;

	/** @var Lesson $lesson */
	private $lesson;

	public function __construct($id, $secret, $access_token, Lesson $lesson)
	{
		$this->vimeo = new Vimeo\Vimeo($id, $secret, $access_token);
		$this->lesson = $lesson;
	}

	/**
	 * Upload the file to Vimeo
	 */
	public function upload()
	{
		if (! file_exists($this->lesson->AltMediaFile()->getFullPath())) throw new Exception('File to upload does not exist: ' . $this->lesson->AltMediaFile()->getFullPath());

		//  Send this to the API library.
		$uri = $this->vimeo->upload($this->lesson->AltMediaFile()->getFullPath());

		//  Now that we know where it is in the API, let's get the info about it so we can find the link.
		$video_data = $this->update(['name' => $this->lesson->Name], $uri);

		if ($video_data['status'] !== 200) throw new Exception('Unable to upload video');

		return $video_data;
	}

	public function update($fields, $uri)
	{
		$this->vimeo->request($uri, $fields, 'PATCH');
		return $this->vimeo->request($uri);
	}

	public function getFiles()
	{

		if (! empty($this->lesson->AltVimeoFile())) {
			// $url = '/videos/' . $this->lesson->AltVimeoFile()->VimeoID . ':' . $this->lesson->AltVimeoFile()->SecondaryID;
			$url = '/videos/' . $this->lesson->AltVimeoFile()->VimeoID;
			$video_data = $this->vimeo->request($url, null, 'GET');
		}

		if (empty($video_data) || $video_data['status'] !== 200) throw new Exception('Unable to get video');
		return $video_data['body']['files'];
	}

}