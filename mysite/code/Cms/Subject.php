<?php

class Subject_ModelAdmin extends ModelAdmin {
    public static $managed_models = array('ExamLevel', 'SubjectGrouping');
    static $url_segment = 'subject';
    static $menu_title = 'Subjects';

}