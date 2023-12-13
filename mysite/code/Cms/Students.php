<?php

class Student_ModelAdmin extends ModelAdmin {
    public static $managed_models = array('Student');
    static $url_segment = 'student';
    static $menu_title = 'Students';

    public function getExportFields() {
        return array(
            'Email' => 'Email',
            'ExamLevel.Name' => 'ExamLevel',
            'ExamCountry.Name' => 'ExamCountry'
        );
    }

}