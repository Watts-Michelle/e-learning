<?php 
class FavouriteLesson extends DataObject {

    protected static $has_one = array(
    	'Student' => 'Student',
		'Lesson' => 'Lesson'
	);

}