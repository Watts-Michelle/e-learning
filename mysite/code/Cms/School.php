<?php

class School_ModelAdmin extends ModelAdmin {
	
    public static $managed_models = array('School', 'SchoolRole');
    static $url_segment = 'school';
    static $menu_title = 'School';

}