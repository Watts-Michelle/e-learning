<?php

class StudentCsvBulkLoader extends CsvBulkLoader {

    public $columnMap = array(
        'First Name' => 'FirstName',
        'Surname' => 'Surname',
        'Email' => 'Email',
        'Class Name' => 'ClassName'
    );

    public $duplicateChecks = array(
        'Email' => 'Email'
    );

    public $relationCallbacks = array();

    public function load($filepath) {
        increase_time_limit_to(3600);
        increase_memory_limit_to('512M');

        return $this->processAll($filepath);
    }

    function randomPassword($StringLength = 8){

        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            .'0123456789!@#$%^&*()'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, $StringLength) as $k) $rand .= $seed[$k];

        return $rand;
    }

    public $DuplicateMessage;

    public $DuplicateRecords = array();

    public $EmptyEmail;

    protected function processRecord($record, $columnMap, &$results, $preview = false) {

        foreach ($record as $key => $value) {
            $record[$key] = trim($value);
        }

        if (empty($record['Email'])){
            $this->EmptyEmail = 'Email required!';
        }

        if(isset($record['FirstName'])){
            if(strlen(trim($record['FirstName'])) == 0 ){
                return $this->EmptyEmail = 'Email required!';
            }
        }

        $CurrentSchool = Session::get('SchoolID');

        if (Member::get()->filter(['Email' => $record['Email']])->first()){

            array_push($this->DuplicateRecords, $record['Email']);
            $this->DuplicateMessage = 'Duplicate email(s):';

            return false;
        }

        if (! EmailField::create('Email', 'Email')->setValue($record['Email'])->validate(new RequiredFields)) return false;

        $member = new Student;

        if (! empty($record['FirstName'])) {
            $name = explode(' ', $record['FirstName']);
            $member->FirstName = $name[0];

            if (isset($name[1])) {
                unset($name[0]);
                $member->Surname = implode(' ', $name);
            }
        }

        $password = $this->randomPassword(8);

        $member->Surname = $record['Surname'];
        $member->Email = $record['Email'];
        $member->SchoolID = $CurrentSchool;
        $member->Test = $password;
        $member->Verified = 1;
        $member->ExamLevelID = 1;
        $member->ExamCountryID = 1;
        $member->write();

        $student = Member::get()->byID($member->ID);
        $student->Password = $password;

        if(! empty($record['ClassName'])){

            $Class = SchoolClass::get()->filter(array(
                'SchoolID' => $CurrentSchool,
                'Name' => $record['ClassName']
            ))->first();

            if($Class){

                $student->SchoolClasses()->add($Class->ID);

            } else {
                $SchoolClass = SchoolClass::create();
                $SchoolClass->Name = $record['ClassName'];
                $SchoolClass->SchoolID = $CurrentSchool;
                $SchoolClass->write();

                $student->SchoolClasses()->add($SchoolClass->ID);
            }
        }

        $student->write();

        if($student->School()->French){
            $member->sendFrenchSchoolRegistrationEmail($password);
        } else {
            $member->sendSchoolRegistrationEmail($password);
        }

        return 0;
    }

}