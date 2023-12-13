<?php 
class ViewedHomeworkPlaylistQuiz extends DataObject {

	protected static $db = array(
		'Viewed' => 'Boolean'
	);

    protected static $has_one = array(
    	'Student' => 'Student',
		'Quiz' => 'Quiz',
		'HomeworkPlaylist' => 'HomeworkPlaylist'
	);

}