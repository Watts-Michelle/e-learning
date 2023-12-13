<?php 
class CompletedHomeworkPlaylistQuiz extends DataObject {

	protected static $db = array(
		'Completed' => 'Boolean'
	);

    protected static $has_one = array(
    	'Student' => 'Student',
		'Quiz' => 'Qiuz',
		'HomeworkPlaylist' => 'HomeworkPlaylist'
	);

}