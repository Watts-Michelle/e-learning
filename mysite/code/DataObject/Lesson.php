<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * Class Lesson
 *
 * Allows the creation of lessons for use in the app, lessons can be either audio or video
 * A video lesson is uploaded to Vimeo using a cron job
 * A secondary cron job then creates links to that video that can be used by the app
 * Until these cron jobs have been run, the video will not appear in the app
 * Audio files will appear automatically
 *
 */
class Lesson extends DataObject {

    /** @var array  Define the required fields for the Lesson table */
    protected static $db = array(
        'UUID' => 'Varchar(50)',
        'Name' => 'Varchar(100)',
        'Content' => 'HTMLText',
		'Free' => 'Boolean',
        'LessonSortOrder' => 'Int',
		'CompletionPoints' => 'Int(0)',
		'Uploaded' => 'Boolean',
		'Type' => "Enum('none, audio, video')",
		'Duration' => 'Int',
		'MediaLink' => 'Varchar(255)',
		'MediaLinkUpdated' => 'SS_Datetime',
		'AltUploaded' => 'Boolean',
		'AltType' => "Enum('none, audio, video')",
		'AltDuration' => 'Int',
		'AltMediaLink' => 'Varchar(255)',
		'AltMediaLinkUpdated' => 'SS_Datetime',
		'EventID' => 'Int'
    );
    
    protected static $has_one = array(
        'SubjectArea' => 'SubjectArea',
        'Image'   => 'Image',
        'MediaFile' => 'File',
		'VimeoFile' => 'VimeoFile',
		'SubtitlesFile' => 'File',
		'AltMediaFile' => 'File',
		'AltVimeoFile' => 'AltVimeoFile',
		'AltSubtitlesFile' => 'File'
    );

	protected static $has_many = array(
		'Points' => 'Points',
		'FavouritingUsers' => 'FavouriteLesson',
		'CompletingUsers' => 'CompletedLesson',
		'CompletingHomeworkPlaylistUsers' => 'CompletedHomeworkPlaylistLesson'
	);

    protected static $belongs_many_many = array(
        'Playlists' => 'Playlist',
		'HomeworkPlaylists' => 'HomeworkPlaylist'
    );

    protected static $indexes = array(
        'UUID' => 'unique("UUID")',
    );

    protected static $summary_fields = array(
        'Name' => 'Title',
        'SubjectArea.Subject.ExamLevel.Name' => 'Exam Level',
		'SubjectArea.Subject.Name' => 'Subject',
		'SubjectArea.Title' => 'Subject Area',
		'CompletionPoints' => 'Points for Completion',
		'IsFree' => 'Free'
    );

	protected static $searchable_fields = array(
		'Name' => array(
			'title' => 'Name'
		),
		'SubjectAreaID' => array(
			'title' => 'Subject Area'
		),
		'SubjectArea.SubjectID' => array(
			'title' => 'Subject'
		),
		'SubjectArea.Subject.ExamLevelID' => array(
			'title' => 'Exam Level'
		)
	);

	public function onBeforeWrite()
	{
		parent::onBeforeWrite();

		if($this->Type != 'none' && $this->Type == $this->AltType) {
			throw new ValidationException('Alternative Media should not be the same type as the Default Media - you have both set to ' . $this->Type);
		}

		if (strpos($this->Name, ':') !== FALSE)
		{
			throw new ValidationException('Please do not use colons in the lesson name.');
		}

		// If this is an update to an existing lesson then we can compare media files with what we already had in the database
		// otherwise, let's compare against a blank lesson which should always result in the actions being taken
		if ($this->ID) {
			$lesson = Lesson::get()->byID($this->ID);
		} else {
			$lesson = new Lesson();
		}

		// Default Media

		if (($this->MediaFileID != $lesson->MediaFileID || $this->Duration == 0) && $this->Type == 'audio') {
			if ($this->MediaFileID) {
				$mp3 = new MP3($this->MediaFile());
				$this->Duration = $mp3->getEstimatedDuration();
				$mp3->resample();
			}
		}

		// if the video changes, we need to upload the new file to Vimeo on the next pass
		if ($this->MediaFileID != $lesson->MediaFileID && $this->Type == 'video') {
			$this->Uploaded = 0;
		}

		// Alternative Media

		if (($this->AltMediaFileID != $lesson->AltMediaFileID || $this->AltDuration == 0) && $this->AltType == 'audio') {
			if ($this->AltMediaFileID) {
				$mp3 = new MP3($this->AltMediaFile());
				$this->AltDuration = $mp3->getEstimatedDuration();
				$mp3->resample();
			}
		}

		// if the video changes, we need to upload the new file to Vimeo on the next pass
		if ($this->AltMediaFileID != $lesson->AltMediaFileID && $this->AltType == 'video') {
			$this->AltUploaded = 0;
		}
	}

	public function onAfterWrite()
    {
        parent::onAfterWrite();

        if (!$this->UUID) {
            $uuid = Uuid::uuid4();
            $this->UUID = $uuid->toString();
            $this->write();
        }

    }

    public function getMediaLinkObject()
	{
		if ($this->Type == 'video' && $this->VimeoFileID) {

			return $this->VimeoFile()->getLinks();

		} else if (!empty($this->MediaLink)) {

			return [[
				'size' => null,
				'link' => $this->MediaLink
			]];

		} else {

			if ($this->Type == 'audio') {
				// Check if there's a low quality one available
				$link = $this->MediaFile()->AbsoluteURL;
				$lqlink = preg_replace('/\.mp3$/', '-48kbps.mp3', $link);
				if(!file_exists($lqlink)) {
					$lqlink = $link;
				}
				return [[
					'size' => null,
					'link' => $link,
					'link_lq' => $lqlink
				]];
			} else {
				return false;
			}

		}
	}

	public function getAltMediaLinkObject()
	{
		if ($this->AltType == 'video' && $this->AltVimeoFileID) {

			return $this->AltVimeoFile()->getLinks();

		} else if (!empty($this->AltMediaLink)) {

			return [[
				'size' => null,
				'link' => $this->AltMediaLink
			]];

		} else {

			if ($this->AltType == 'audio') {
				// Check if there's a low quality one available
				$link = $this->AltMediaFile()->AbsoluteURL;
				$lqlink = preg_replace('/\.mp3$/', '-48kbps.mp3', $link);
				if(!file_exists($lqlink)) {
					$lqlink = $link;
				}
				return [[
					'size' => null,
					'link' => $link,
					'link_lq' => $lqlink
				]];
			} else {
				return false;
			}

		}
	}

	public function getMediaUpdated()
	{
		if ($this->VimeoFileID) {
			$lastEdited = $this->VimeoFile()->LastEdited;
		} else if (!empty($this->MediaLink)) {
			$lastEdited = $this->MediaLinkUpdated;
		} else {
			$lastEdited = $this->MediaFile()->LastEdited;
		}

		$lastAltEdited = 0;
		if($this->AltMediaFileID) {
			// There is Aleternative media to consider too
			if ($this->AltVimeoFileID) {
				$lastAltEdited = $this->AltVimeoFile()->LastEdited;
			} else if (!empty($this->AltMediaLink)) {
				$lastAltEdited = $this->AltMediaLinkUpdated;
			} else {
				$lastAltEdited = $this->AltMediaFile()->LastEdited;
			}
		}

		return strtotime(max($lastEdited, $lastAltEdited));
	}

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('UUID');
        $fields->removeByName('LessonSortOrder');
        $fields->removeByName('FavouritingUsers');
        $fields->removeByName('CompletingUsers');
        $fields->removeByName('Playlists');
		$fields->removeByName('Uploaded');
		$fields->removeByName('Points');
		$fields->makeFieldReadonly('Duration');
		$fields->makeFieldReadonly('AltDuration');

		// Don't want the client altering this
//		$fields->removeByName('VimeoFileID');

		$fields->addFieldToTab('Root.Main', $uf = new UploadField('Image'));
		$uf->setFolderName('lesson/image');

		$fields->replaceField('CompletionPoints', NumericField::create('CompletionPoints', 'Points for completion (if blank or 0, will give default)', $this->CompletionPoints));

		$fields->replaceField('SubjectAreaID', DropdownField::create('SubjectAreaID', 'Subject Area', SubjectArea::get()->sort(['Subject.ExamLevel.Name' => 'ASC', 'Subject.Name' => 'ASC', 'Title' => 'ASC'])->map('ID', 'Name'), $this->SubjectAreaID));


		// Default Media Tab
		$fields->addFieldToTab('Root.DefaultMedia', $fields->dataFieldByName('Type'));
		$fields->addFieldToTab('Root.DefaultMedia', $fields->dataFieldByName('Duration'));
		$fields->addFieldToTab('Root.DefaultMedia', $fields->dataFieldByName('MediaLink'));
		$fields->addFieldToTab('Root.DefaultMedia', $fields->dataFieldByName('MediaLinkUpdated'));
		$fields->addFieldToTab('Root.DefaultMedia', $uf = new UploadField('MediaFile'));
		$uf->setFolderName('lesson/media');

		$fields->addFieldToTab('Root.DefaultMedia', $uf = new UploadField('SubtitlesFile'));
		$uf->setFolderName('lesson/subtitles');
		$uf->setAllowedExtensions(['srt']);

		// Alternative Media Tab
		$fields->addFieldToTab('Root.AlternativeMedia', $fields->dataFieldByName('AltType')->setTitle('Type'));
		$fields->addFieldToTab('Root.AlternativeMedia', $fields->dataFieldByName('AltDuration')->setTitle('Duration'));
		$fields->addFieldToTab('Root.AlternativeMedia', $fields->dataFieldByName('AltMediaLink')->setTitle('Media Link'));
		$fields->addFieldToTab('Root.AlternativeMedia', $fields->dataFieldByName('AltMediaLinkUpdated')->setTitle('Media Link Updated'));
		$fields->addFieldToTab('Root.AlternativeMedia', $uf = new UploadField('AltMediaFile', 'Media File'));
		$uf->setFolderName('lesson/media');

		$fields->addFieldToTab('Root.AlternativeMedia', $uf = new UploadField('AltSubtitlesFile', 'Subtitles File'));
		$uf->setFolderName('lesson/subtitles');
		$uf->setAllowedExtensions(['srt']);


        return $fields;
    }

    public function getBasic()
    {
		if (! $mediaLink = $this->getMediaLinkObject()) return false;

		$favourited = $this->FavouritingUsers()->filter(['StudentID' => CurrentUser::getUserID()])->first();
		$completed = $this->CompletingUsers()->filter(['StudentID' => CurrentUser::getUserID()])->first();

		$subjectArea = $this->SubjectArea();
		$subject = $subjectArea->Subject();

		$content = new HTMLText();
		$content->setValue($this->Content);

		$image = $this->Image()->exists() ? $this->Image()->ScaleWidth(500)->AbsoluteLink() : null;

		$mediaEditedTS = $this->getMediaUpdated();
		$lastEditedTS = strtotime($this->LastEdited);

		if ($mediaEditedTS > $lastEditedTS) {
			$lastEditedTS = $mediaEditedTS;
		}

		$media = array(array(
			'type' => $this->Type,
			'duration' => $this->Duration,
			'link' => $mediaLink,
			'link_last_updated' => $mediaEditedTS,
			'subtitles_link' => $this->SubtitlesFileID ? $this->SubtitlesFile()->AbsoluteURL : null
		));

		if($altMediaLink = $this->getAltMediaLinkObject()) {
			// We have alternative media too
			$media[] = array(
				'type' => $this->AltType,
				'duration' => $this->AltDuration,
				'link' => $altMediaLink,
				'link_last_updated' => $mediaEditedTS,
				'subtitles_link' => $this->AltSubtitlesFileID ? $this->AltSubtitlesFile()->AbsoluteURL : null
			);
		}

        $array = [
            'id' => $this->UUID,
			'event_id' => $this->EventID,
            'subject_id' => $subject->UUID,
            'subject' => $subject->Name,
            'subject_icon' => $subject->IconID ? $subject->Icon()->AbsoluteURL : null,
            'subject_area' => $subjectArea->Name,
            'name' => $this->Name,
            'favourite' => $favourited ? 1 : 0,
			'favourited' => $favourited ? strtotime($favourited->Created) : null,
            'image' => $image,
            'duration' => $this->Duration,
            'type' => $this->Type,
            'link' =>  $mediaLink,
            'link_last_updated' => $mediaEditedTS,
			'subtitles_link' => $this->SubtitlesFileID ? $this->SubtitlesFile()->AbsoluteURL : null,
            'content' => $content->AbsoluteLinks(),
            'sort_order' => $this->LessonSortOrder,
            'completed' => $completed ? 1 : 0,
            'completed_date' => $completed ? strtotime($completed->Created) : null,
			'can_be_listened' => $this->canBeListened(CurrentUser::getUser()) ? 1 : 0,
            'last_updated' => $lastEditedTS,
			'media' => $media
        ];

        return $array;
    }

	public function getPlaylistLesson($HomeworkPlaylistID = null)
	{
		if (! $mediaLink = $this->getMediaLinkObject()) return false;

		$completed = $this->CompletingHomeworkPlaylistUsers()->filter(array('HomeworkPlaylistID' => $HomeworkPlaylistID, 'StudentiD' => CurrentUser::getUserID()))->first();

		$subjectArea = $this->SubjectArea();
		$subject = $subjectArea->Subject();

		$content = new HTMLText();
		$content->setValue($this->Content);

		$image = $this->Image()->exists() ? $this->Image()->ScaleWidth(500)->AbsoluteLink() : null;

		$mediaEditedTS = $this->getMediaUpdated();
		$lastEditedTS = strtotime($this->LastEdited);

		if ($mediaEditedTS > $lastEditedTS) {
			$lastEditedTS = $mediaEditedTS;
		}

		$Viewed = '0';
		$Completed = '0';
		if($HomeworkPlaylistID) {

			if($ViewHomeworkPlaylistLesson = ViewedHomeworkPlaylistLesson::get()
				->filter(array('HomeworkPlaylistID' => $HomeworkPlaylistID, 'LessonID' => $this->ID, 'StudentID' => CurrentUser::getUserID()))
				->first()){
				$Viewed = $ViewHomeworkPlaylistLesson->Viewed;
			}

			if($CompletedHomeworkPlaylistQuiz = CompletedHomeworkPlaylistLesson::get()
				->filter(array('HomeworkPlaylistID' => $HomeworkPlaylistID, 'LessonID' => $this->ID, 'StudentID' => CurrentUser::getUserID()))
				->first()){
				$Completed = $CompletedHomeworkPlaylistQuiz->Completed;
			}
		}

		$media = array(array(
			'type' => $this->Type,
			'duration' => $this->Duration,
			'link' => $mediaLink,
			'link_last_updated' => $mediaEditedTS,
			'subtitles_link' => $this->SubtitlesFileID ? $this->SubtitlesFile()->AbsoluteURL : null
		));

		if($altMediaLink = $this->getAltMediaLinkObject()) {
			// We have alternative media too
			$media[] = array(
				'type' => $this->AltType,
				'duration' => $this->AltDuration,
				'link' => $altMediaLink,
				'link_last_updated' => $mediaEditedTS,
				'subtitles_link' => $this->AltSubtitlesFileID ? $this->AltSubtitlesFile()->AbsoluteURL : null
			);
		}

		$array = [
			'id' => $this->UUID,
			'subject_id' => $subject->UUID,
			'subject' => $subject->Name,
			'subject_icon' => $subject->IconID ? $subject->Icon()->AbsoluteURL : null,
			'subject_area' => $subjectArea->Name,
			'name' => $this->Name,
			'image' => $image,
			'duration' => $this->Duration,
			'type' => $this->Type,
			'link' =>  $mediaLink,
			'link_last_updated' => $mediaEditedTS,
			'subtitles_link' => $this->SubtitlesFileID ? $this->SubtitlesFile()->AbsoluteURL : null,
			'content' => $content->AbsoluteLinks(),
			'sort_order' => $this->LessonSortOrder,
			'completed' => $completed ? 1 : 0,
			'completed_date' => $completed ? strtotime($completed->Created) : null,
			'can_be_listened' => $this->canBeListened(CurrentUser::getUser()) ? 1 : 0,
			'last_updated' => $lastEditedTS,
			'media' => $media,
			'homework_viewed' => $Viewed,
			'homework_completed' => $Completed,
		];

		return $array;
	}

    public function canBeListened(Member $user)
	{
		if($this->Free){
			return true;
		} else {
			if($user->DeviceCampaign == 1){
				return true;
			}
		}

		return $this->SubjectArea()->Subject()->getHasSubscription($user);
	}

	public function assignPoints(Student $member)
	{
		$settings = SiteConfig::current_site_config();

		if (! $member->CompletedLessons()->filter('LessonID', $this->ID)->first() && ! $settings->AllowPointsForMultipleListens) return false;

		$points = $this->pointsForCompletion();

		$completionPoints = new Points;
		$completionPoints->LessonID = $this->ID;
		$completionPoints->StudentID = $member->ID;
		$completionPoints->Points = $points;
		$completionPoints->Type = 'PointsCompletedLesson';
		$completionPoints->write();

		$completed = 0;
		$lessonCount = $this->SubjectArea()->Lessons()->count();

		foreach ($this->SubjectArea()->Lessons() as $lesson) {
			if ($member->Points()->filter(['LessonID' => $lesson->ID, 'Type' => 'PointsCompletedLesson'])->first()) {
				$completed++;
			}
		}

		if ($completed == $lessonCount) {
			$points += $this->SubjectArea()->assignPoints($member);
		}

		return $points;
	}

	private function pointsForCompletion()
	{
		$settings = SiteConfig::current_site_config();

		if ($this->CompletionPoints) {
			$points = $this->CompletionPoints;
		} else {
			$points = $settings->PointsCompletedLesson;
		}

		if ($settings->DoublePoints) {
			$points = $points * 2;
		}

		return $points;
	}

	public function getIsFree()
	{
		return $this->Free ? 'yes' : 'no';
	}
}