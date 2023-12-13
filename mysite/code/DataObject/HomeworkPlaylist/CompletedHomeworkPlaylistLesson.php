<?php 
class CompletedHomeworkPlaylistLesson extends DataObject {

	protected static $db = array(
		'Completed' => 'Boolean'
	);

    protected static $has_one = array(
    	'Student' => 'Student',
		'Lesson' => 'Lesson',
		'HomeworkPlaylist' => 'HomeworkPlaylist'
	);

}