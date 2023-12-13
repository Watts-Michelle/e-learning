<?php

class AppOptions_ModelAdmin extends ModelAdmin {
    public static $managed_models = array(
        'Country', 'ExamLevel', 'ExamCountry', 'Ethnicity', 'Student', 'SubscriptionType', 'HomeworkPlaylist', 'Playlist'
    );

	private static $model_importers = array(
		'Student' => 'MemberImporter',
	);

    static $url_segment = 'options';
    static $menu_title = 'App Variables';
}