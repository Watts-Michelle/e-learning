<?php

/**
 * createSchool task needs to be run before all others. Followed by createStaff, createStudents, finally createClasses.
 *
 * Class WestData_Controller
 */

class WestData_Controller extends Base_Controller {

    protected $auth = false;

    private $base = 'https://passport.schoolmessenger.com/datahub/services';

    private $token;

    private static $allowed_actions = array(
        'getToken',
        'getStudents',
        'getSchools',
        'createSchool',
        'createClasses',
        'createStaff',
        'createStudents'
    );

    private static $url_handlers = array(
        'token' => 'getToken',
        'students' => 'getStudents',
        'schools' => 'getSchools',
        'create/schools' => 'createSchool',
        'create/classes' => 'createClasses',
        'create/staff' => 'createStaff',
        'create/students' => 'createStudents'
    );

//	public function init() {
//
//		parent::init();
//
//		if (php_sapi_name() != "cli") {
//			if (!Member::currentUser()) {
//
//				return Security::PermissionFailure($this->controller, 'You must be logged in to access this page');
//			}
//
//			if (!Member::currentUser()->inGroups(array('administrators'))) {
//				return Security::PermissionFailure($this->controller, 'You must be logged in as admin to access this page');
//			}
//		}
//	}

    /**
     * Get access token and store it.
     *
     * @return mixed|string
     */
    public function getToken()
    {
        date_default_timezone_set('Europe/London');

        $CurrentToken = '';

        foreach (WestOauthAccessToken::get() as $token) {

            /* If token expiration date is greater than today assign token. */
            if (strtotime($token->ExpireTime) >= strtotime(date('d-m-Y H:i:s', time()))) {

                $CurrentToken = $token->AccessToken;
                break;
            }
        }

        if (empty($CurrentToken)) {

            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => WestDataClientID,
                'clientSecret' => WestDataClientSecret,
                'redirectUri' => 'none',
                'urlAuthorize' => 'none',
                'urlAccessToken' => 'https://passport.schoolmessenger.com/datahub/oauth/token',
                'urlResourceOwnerDetails' => 'none'
            ]);

            try {

                $accessToken = $provider->getAccessToken('client_credentials');

                $this->storeToken($accessToken->getToken(), $accessToken->getExpires());

                $CurrentToken = $accessToken->getToken();

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                exit($e->getMessage());
            }
        }

        $this->token = $CurrentToken;
        return $this->token;
    }

    /**
     * Store access token.
     *
     * @param $token
     * @param $expireTime
     */
    public function storeToken($token, $expireTime)
    {
        $query = new WestOauthAccessToken();
        $query->AccessToken = $token;
        $query->ExpireTime = date('Y-m-d H:i:s', $expireTime);
        $query->write();
    }

    /**
     * Request endpoint with multiple data entities.
     *
     * @param $uri
     * @return mixed
     * @throws Exception
     */
    public function requestAll($uri)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base . $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ". $this->getToken(),
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $errno    = curl_errno($curl);
        $errmsg   = curl_error($curl);

        curl_close($curl);

        if ($errno != 0) {
            throw new Exception($errmsg, $errno);
        } else {
            return $response;
        }
    }

    /**
     * Request endpoint with single data entity.
     *
     * @param $id
     * @param $uri
     * @return mixed
     * @throws Exception
     */
    public function requestSingle($id, $uri)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base . $uri .'/' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ". $this->getToken(),
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $errno    = curl_errno($curl);
        $errmsg   = curl_error($curl);

        curl_close($curl);

        if ($errno != 0) {
            throw new Exception($errmsg, $errno);
        } else {
            return $response;
        }
    }

    /**
     * Json decode response.
     *
     * @param $response
     * @return mixed
     */
    public function decodeResponse($response)
    {
        return json_decode($response);
    }

    /**
     * Request get all schools endpoint.
     *
     * @return mixed
     * @throws Exception
     */
    public function getSchools($uri = null)
    {
        if($uri){
            return $this->decodeResponse($this->requestAll($uri));
        } else {
            return $this->decodeResponse($this->requestAll('/v1/schools'));
        }
    }

    /**
     * Create schools
     *
     * @return SS_HTTPResponse
     */
    public function createSchool()
    {
        ini_set('max_execution_time', 0);

        $uri = '/v1/schools/';

        $PageTotal = $this->getSchools($uri)->paging->total;

        $i = 0;

        do {
            $i++;

            $response = $this->getSchools($uri);

            foreach ($response->data as $WestSchool) {

                if(School::get()->filter('WestID', $WestSchool->data->id)->first()){
                    $School = School::get()->filter('WestID', $WestSchool->data->id)->first();
                } else {
                    $School = School::create();
                }

                $config = SiteConfig::current_site_config();

                $ExamCountry = ExamCountry::get()->filter('name', 'United Kingdom')->first();

                $School->Name = $WestSchool->data->name;
                $School->StudentCap = $config->SchoolStudentCap;
                $School->StaffCap = $config->SchoolStaffCap;
                $School->ExamCountryID = $ExamCountry->ID;
                $School->WestID = $WestSchool->data->id;
                $School->WestDistrictID = $WestSchool->data->district;
                $School->WestStateID = $WestSchool->data->state_id;
                $School->WestSchoolNumber = $WestSchool->data->school_number;
                $School->WestLowGrade = $WestSchool->data->low_grade;
                $School->WestHighGrade = $WestSchool->data->high_grade;
                $School->WestAddressLine1 = $WestSchool->data->location->address;
                $School->WestAddressLineCity = $WestSchool->data->location->city;
                $School->WestAddressLineState = $WestSchool->data->location->state;
                $School->WestAddressZip = $WestSchool->data->location->zip;
                $School->write();
            }

            foreach ($response->links as $link) {

                if ($link->rel == 'next') {
                    $uri = $link->uri;
                }
            }

        } while ($i < $PageTotal);

        echo date('Y-m-d H:i:s') . ': Create school task run successfully.';
    }

    /**
     * Request get all teachers endpoint.
     *
     * @return mixed
     * @throws Exception
     */
    public function getStaff($uri = null)
    {
        if($uri){
            return $this->decodeResponse($this->requestAll($uri));
        } else {
            return $this->decodeResponse($this->requestAll('/v1/teachers'));
        }
    }

    /**
     * Create staff and send registration email with password.
     *
     * @return SS_HTTPResponse
     */
    public function createStaff()
    {
        ini_set('max_execution_time', 0);

        $uri = '/v1/teachers/';

        $PageTotal = $this->getStaff($uri)->paging->total;

        $i = 0;

        do {
            $i++;

            $response = $this->getStaff($uri);

            foreach ($response->data as $WestTeacher) {

                /* If school exists. */
                if($CurrentSchool = School::get()->filter('WestID', $WestTeacher->data->school)->first()) {

                    if (Staff::get()->filter('WestID', $WestTeacher->data->id)->first()) {
                        $Staff = Staff::get()->filter('WestID', $WestTeacher->data->id)->first();
                    } else {
                        $Staff = Staff::create();
                    }

                    $Staff->SchoolID = $CurrentSchool->ID;
                    $Staff->FirstName = $WestTeacher->data->name->first;
                    $Staff->Surname = $WestTeacher->data->name->last;
                    $Staff->Role = 'Teacher';
                    $Staff->WestID = $WestTeacher->data->id;
                    $Staff->WestDistrictID = $WestTeacher->data->district;
                    $Staff->WestForename = $WestTeacher->data->name->first;
                    $Staff->WestSurname = $WestTeacher->data->name->last;
                    $Staff->WestEmail = $WestTeacher->data->email;
                    $Staff->WestCreatedAt = $WestTeacher->data->created;
                    $Staff->WestLastModified = $WestTeacher->data->last_modified;
                    $Staff->write();

                    $this->sendStaffRegistrationEmails($Staff->ID);

                } else {

                    echo date('Y-m-d H:i:s') . ': Can not create staff member: '.$WestTeacher->data->id.' School '. $WestTeacher->data->school.' does not exist.';
                }
            }

            foreach ($response->links as $link) {

                if ($link->rel == 'next') {
                    $uri = $link->uri;
                }
            }

        } while ($i < $PageTotal);

        echo date('Y-m-d H:i:s') . ': Create staff task run successfully.';
    }

    /**
     * Send staff registration email.
     *
     * @param $StaffID
     * @return SS_HTTPResponse
     */
    public function sendStaffRegistrationEmails($StaffID)
    {
        ini_set('max_execution_time', 0);

        if ($Staff = Staff::get()->byID($StaffID)) {

            if($Staff->Verified === false) {

                $Staff->Verified = true;
                $Staff->write();

                $Staff->Password = $this->randomPassword(10);
                $Staff->write();

                // Send staff email.
            }
        }

        $email = new Email();
        $email
            ->setFrom('admin@studytracks.com')
            ->setTo('michelle@flipsidegroup.com')
            ->setSubject('registration')
            ->setBody('this is a test!');

//        $email->send();


        return true;
//        return (new JsonApi)->formatReturn([]);
    }

    /**
     * Request get all students endpoint.
     *
     * @return mixed
     * @throws Exception
     */
    public function getAllStudents($uri = null)
    {
        if($uri){
            return $this->decodeResponse($this->requestAll($uri));
        } else {
            return $this->decodeResponse($this->requestAll('/v1/students'));
        }
    }

    /**
     * Request get student endpoint.
     *
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function getStudent($id)
    {
        return $this->decodeResponse($this->requestSingle($id, 'students'));
    }

    /**
     * Create students
     *
     * @return SS_HTTPResponse
     */
    public function createStudents()
    {
        ini_set('max_execution_time', 0);

        $uri = '/v1/students/';

        $PageTotal = $this->getAllStudents($uri)->paging->total;

        $i = 0;

        do {
            $i++;

            $response = $this->getAllStudents($uri);

            foreach ($response->data as $WestStudent) {

                 /* If school exists */
                if ($CurrentSchool = School::get()->filter('WestID', $WestStudent->data->school)->first()) {

                    if (Student::get()->filter('WestID', $WestStudent->data->id)->first()) {
                        $Student = Student::get()->filter('WestID', $WestStudent->data->id)->first();
                    } else {
                        $Student = Student::create();
                    }

                    $ExamCountry = ExamCountry::get()->filter('name', 'United Kingdom')->first();

                    $Student->FirstName = $WestStudent->data->name->first;
                    $Student->Surname = $WestStudent->data->name->last;
                    $Student->WestID = $WestStudent->data->id;
                    $Student->WestStudentNumber = $WestStudent->data->student_number;
                    $Student->WestDistrictID = $WestStudent->data->district;
                    $Student->WestSurname = $WestStudent->data->name->last;
                    $Student->WestForename = $WestStudent->data->name->first;
                    $Student->WestEmail = $WestStudent->data->email;
                    $Student->WestCreatedAt = $WestStudent->data->created;
                    $Student->WestLastModified = $WestStudent->data->last_modified;
                    $Student->SchoolID = $CurrentSchool->ID;
                    $Student->ExamCountryID = $ExamCountry->ID;

                    $Student->write();

                    $this->sendStudentRegistrationEmails($Student->ID);

                } else {
                    echo date('Y-m-d H:i:s') . ': You need to create the school first!';
                }
            }

            foreach ($response->links as $link) {

                if ($link->rel == 'next') {
                    $uri = $link->uri;
                }
            }

        } while ($i < $PageTotal);

        echo date('Y-m-d H:i:s') . ': Create students task run successfully.';
    }

    /**
     * Send students registration emails.
     *
     * @param $SchoolID
     * @return SS_HTTPResponse
     */
    public function sendStudentRegistrationEmails($StudentID)
    {
        ini_set('max_execution_time', 0);

        if ($Student = Student::get()->byID($StudentID)) {

            if($Student->Verified === false) {

                $Student->Verified = true;
                $Student->write();

                $Student->Password = $this->randomPassword(10);
                $Student->write();

                // Send staff email.
            }
        }

        $email = new Email();
        $email
            ->setFrom('admin@studytracks.com')
            ->setTo('michelle@flipsidegroup.com')
            ->setSubject('registration')
            ->setBody('this is a test!');

//        $email->send();

        return true;
//        return (new JsonApi)->formatReturn([]);
    }

    /**
     * Request get all classes endpoint.
     *
     * @return mixed
     * @throws Exception
     */
    public function getClasses($uri = null)
    {
        if($uri){
            return $this->decodeResponse($this->requestAll($uri));
        } else {
            return $this->decodeResponse($this->requestAll('/v1/sections'));
        }
    }

    /**
     * Create classes.
     *
     * @return SS_HTTPResponse
     */
    public function createClasses()
    {
        ini_set('max_execution_time', 0);

        $uri = '/v1/sections/';

        $PageTotal = $this->getClasses($uri)->paging->total;

        $i = 0;

        do {
            $i++;

            $response = $this->getClasses($uri);

            foreach ($response->data as $WestClass) {

                /* If school exists. */
                if($CurrentSchool = School::get()->filter('WestID', $WestClass->data->school)->first()) {

                    /*  If class has a teacher. */
                    if ($Staff = Staff::get()->filter('WestID', $WestClass->data->teacher)->first()) {

                        if (SchoolClass::get()->filter('WestID', $WestClass->data->id)->first()) {
                            $Class = SchoolClass::get()->filter('WestID', $WestClass->data->id)->first();
                        } else {
                            $Class = SchoolClass::create();
                        }

                        $Class->Name = $WestClass->data->name;
                        $Class->SchoolID = $CurrentSchool->ID;
                        $Class->WestID = $WestClass->data->id;
                        $Class->WestDistrictID = $WestClass->data->district;
                        $Class->WestName = $WestClass->data->name;
                        $Class->WestCreatedAt = $WestClass->data->created;
                        $Class->WestLastModified = $WestClass->data->last_modified;
                        $Class->StaffID = $Staff->ID;

                        $Class->write();

                        $Staff->SchoolClass()->add($Class);

                        $this->assignStudentsClass($WestClass->data->students, $Class->ID);

                    }

                } else {
                    echo date('Y-m-d H:i:s') . ': You need to create the school first!';
                }
            }

            foreach ($response->links as $link) {

                if ($link->rel == 'next') {
                    $uri = $link->uri;
                }
            }

        } while ($i < $PageTotal);

        echo date('Y-m-d H:i:s') . ': Create classes task run successfully.';
    }

    /**
     * Assign a students class.
     *
     * @param array $Students
     * @param $ClassID
     */
    public function assignStudentsClass(array $Students, $ClassID)
    {
        $Class = SchoolClass::get()->byID($ClassID);

        foreach($Students as $WestStudentID){

            if($Student = Student::get()->filter('WestID', $WestStudentID)->first()){

                var_dump('SchoolStudent: '. $WestStudentID . 'Has been added to school class: '.$ClassID);

                $Student->SchoolClasses()->add($Class);
                $Class->Students()->add($Student);

            } else {
                echo('Failed');
            }
        }
    }

    /**
     * Generate password.
     *
     * @param int $StringLength
     * @return string
     */
    public function randomPassword($StringLength = 8){

        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            .'0123456789!@#$%^&*()');
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, $StringLength) as $k) $rand .= $seed[$k];

        return $rand;
    }
}
