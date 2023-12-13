<?php 
class ViewedHomeworkPlaylistLesson extends DataObject {

	protected static $db = array(
		'Viewed' => 'Boolean'
	);

    protected static $has_one = array(
    	'Student' => 'Student',
		'Lesson' => 'Lesson',
		'HomeworkPlaylist' => 'HomeworkPlaylist'
	);

}