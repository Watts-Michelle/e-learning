<?php

class Playlist_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = true;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'withoutID',
		'withID',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'withoutID',
		'$ID//$lessonID' => 'withID',
	);
	
	public function withoutID(SS_HTTPRequest $request)
	{
		if ($request->isGET()) {
			return $this->get($request);
		}

		if ($request->isPOST()) {
			return $this->createPlaylist($request);
		}

		return $this->handleError(404);
	}

	public function withID(SS_HTTPRequest $request)
	{
		$id = $request->param('ID');
		$lessonID = $request->param('lessonID');

		if (! $lessonID) {
			if ($request->isGET()) {
				return $this->getByID($id);
			}

			if ($request->isPOST()) {
				return $this->updatePlaylist($id);
			}

			if ($request->isPUT()) {
				return $this->addTrack($id);
			}

			if ($request->isDELETE()) {
				return $this->deletePlaylist($id);
			}

			return $this->handleError(404);
		}

		return $this->deleteTrack($id, $lessonID);
	}

	private function get()
	{

		$playlists = $this->appUser->Playlists();

		$return = ['playlist' => []];

		if (! empty($playlists)) {
			foreach ($playlists as $playlist) {

				/** @var Playlist $playlist */
				$return['playlist'][] = $playlist->getBasic();
			}
		}

		return (new JsonApi)->formatReturn($return);
	}

	private function createPlaylist(SS_HTTPRequest $request)
	{
		$body = new PlaylistForm($this->requestBody);
		$data = $body->process();

		$playlist = new Playlist;
		$playlist->Name = $data['Name'];
		$playlist->StudentID = $this->appUser->ID;
		$playlist->write();

		if (! empty($data['Lessons'])) {
			$lessons = Lesson::get()->filter(['UUID' => $data['Lessons']]);

			if (! empty($lessons)) {
				foreach ($lessons as $lesson) {
					$playlist->Lessons()->add($lesson);
				}
			}
		}

		$playlist->write();

		return (new JsonApi)->formatReturn(['playlist' => $playlist->getBasic()]);
	}


	private function getByID($id) {

		/** @var Playlist $playlist */
		$playlist = $this->appUser->Playlists()->filter(['UUID' => $id])->first();

		if (empty($playlist)) {
			return $this->handleError(404);
		}

		return (new JsonApi)->formatReturn(['playlist' => $playlist->getBasic()]);
	}

	private function updatePlaylist($id)
	{
		$playlist = $this->appUser->Playlists()->filter(['UUID' => $id])->first();

		if (! $playlist) return $this->handleError(404);

		$body = new PlaylistForm($this->requestBody);
		$data = $body->process();

		if (! empty($data['Name'])) {
			$playlist->Name = $data['Name'];
			$playlist->write();
		}

		if (isset($data['Lessons'])) {

			$lessons = Lesson::get()->filter(['UUID' => $data['Lessons']]);

			if ($lessons->count() > 0 || count($data['Lessons']) == 0) {
				$playlist->Lessons()->removeAll();

				if (!empty($lessons)) {
					foreach ($lessons as $lesson) {
						$playlist->Lessons()->add($lesson);
					}
				}
			}
		}

		return (new JsonApi)->formatReturn(['playlist' => $playlist->getBasic()]);
	}

	private function addTrack($id)
	{
		/** @var Playlist $playlist */
		$playlist = $this->appUser->Playlists()->filter(['UUID' => $id])->first();

		if (! $playlist) return $this->handleError(404);

		if (! empty($this->requestBody['lesson'])) {
			$lessons = Lesson::get()->filter(['UUID' => $this->requestBody['lesson']]);

			if (! empty($lessons)) {
				foreach ($lessons as $lesson) {
					$playlist->Lessons()->add($lesson);
				}
			}
		}

		return (new JsonApi)->formatReturn([]);
	}

	private function deletePlaylist($id)
	{
		/** @var Playlist $playlist */
		$playlist = $this->appUser->Playlists()->filter(['UUID' => $id])->first();

		if ($playlist) {
			$playlist->Lessons()->removeAll();
			$playlist->delete();
		}

		return (new JsonApi)->formatReturn([]);
	}

	private function deleteTrack($id, $lessonID)
	{
		$playlist = $this->appUser->Playlists()->filter(['UUID' => $id])->first();
		$lesson = Lesson::get()->filter(['UUID' => $lessonID])->first();

		if ($playlist && $lesson) {
			$playlist->Lessons()->remove($lesson);
		}

		return (new JsonApi)->formatReturn([]);
	}
}