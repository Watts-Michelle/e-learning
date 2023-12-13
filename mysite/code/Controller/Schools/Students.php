<?php

class Students_Controller extends School_Controller
{

	protected $activePage = 'Students';

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'all',
		'byID',
		'editByID',
		'deleteByID',
		'StudentUpload',
		'doStudentUpload',
		'BulkClassChange',
		'studentForm',
		'doStudentForm',
		'addStudent',
		'filter'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'filter' => 'filter',
		'StudentUpload' => 'StudentUpload',
		'class' => 'BulkClassChange',
		'create' => 'addStudent',
		'studentForm' => 'studentForm',
		'$ID/edit//$dummy' => 'editByID',
		'$ID/delete//$dummy' => 'deleteByID',
		'$ID//$dummy' => 'byID',
		'' => 'all',
	);

	public function init()
	{
		parent::init();

		$this->breadcrumbs = new ArrayList([
			new ArrayData([
				'Name' => 'Student List',
				'Link' => '/school/students'
			]),
		]);
	}

	public function filter(){
		$Choice = $this->request->postVar('filter');

		Session::set('StudentDateFilter', $Choice);

		return $this->redirect('/school/students');
	}

	public function all()
	{
		$this->init();
		$this->title = 'StudyTracks - Student List';

		$this->pageTitle = 'Manage Students';

		$row = $this->breadcrumbs->first();
		$row->Active = 1;

		$students = $this->school->Students()->filter(['Deleted' => 0]);
		$studentArray = new ArrayList;

		foreach ($students as $student) {
			if ($student->canView()) {
				$studentArray->push($student);
			}
		}

		$classes = SchoolClass::get();

		$SchoolClasses = SchoolClass::get()->filter('SchoolID', $this->school->ID);

		$Choice = Session::get('StudentDateFilter');

		if($Choice){
			$StudentDateFilter = $Choice;
		} else {
			Session::set('StudentDateFilter', 7);
			$StudentDateFilter = 7;
		}

		$settings = SiteConfig::current_site_config();

		$video = $settings->VimeoStudentHelpVideo;

		return $this->renderPage('StudentList', [
			'Students' => $studentArray,
			'School' => $this->school,
			'Classes' => $classes,
			'StudentDateFilter' => $StudentDateFilter,
			'BulkSchoolClasses' => $SchoolClasses,
			'HelpVideo' => $video
		]);
	}

	public function byID()
	{
		if (! $this->request->param('ID')) {
			return $this->all();
		}

		/** @var Student $student */
		$student = $this->school->Students()->filter(['UUID' => $this->request->param('ID'), 'Deleted' => 0])->first();

		if (! $student || ! $student->canView()) {
			return $this->httpError(404);
		}

		$this->pageTitle = $student->FirstName . ' ' . $student->Surname;
		$this->breadcrumbs->add(new ArrayData([
			'Name' => $student->FirstName . ' ' . $student->Surname,
			'Link' => '',
			'Active' => 1
		]));

		return $this->renderPage('StudentScreen', [
			'Student' => $student
		]);
	}

	public function editByID()
	{

		$student = $this->school->Students()->filter(['UUID' => $this->request->param('ID'), 'Deleted' => 0])->first();

		if (! $student) {
			return $this->httpError(404);
		}

		$this->pageTitle = 'Edit ' . $student->FirstName . ' ' . $student->Surname;

		$this->breadcrumbs->add(new ArrayData([
			'Name' => $student->FirstName . ' ' . $student->Surname,
			'Link' => '/school/students/' . $this->request->param('ID'),
			'Active' => 0
		]));

		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'edit',
			'Link' => '',
			'Active' => 1
		]));

		$this->title = 'StudyTracks - Update student';

		return $this->renderPage('StudentForm', [
			'Student' => $student
		]);
	}



	public function addStudent()
	{
		if (! $this->school->canAddStudent()) {

			Session::set('ActionMessage', 'You have reached your school\'s student cap, please contact StudyTracks for details of how to increase this.');
			Session::set('ActionStatus', 'warning');

			return $this->redirectBack();
		}

		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'create',
			'Link' => '',
			'Active' => 1
		]));

		$this->pageTitle = 'Add a new student';
		$this->title = 'StudyTracks - Add a new student';
		Session::clear('CurrentStudentID');

		return $this->renderPage('StudentForm', []);
	}

	public function studentForm()
	{

		$student = $this->school->Students()->filter(['UUID' => $this->request->param('ID') ?: Session::get('CurrentStudentID')])->first();

		if (! $student && $this->request->param('ID')) {
			return $this->httpError(404);
		}

		if ($student) {
			Session::set('CurrentStudentID', $student->UUID);
		}

		$fields = new FieldList([
			FieldGroup::create([
				FieldGroup::create([
					TextField::create('FirstName', null, !empty($student) ? $student->FirstName : null)->addExtraClass('form-control'),
				])->addExtraClass('col-sm-6'),
				FieldGroup::create([
					TextField::create('Surname', null, !empty($student) ? $student->Surname : null)->addExtraClass('form-control'),
				])->addExtraClass('col-sm-6'),
			])->addExtraClass('row mt30'),
			FieldGroup::create([
				FieldGroup::create([
					EmailField::create('Email', null, !empty($student) ? $student->Email : null)->addExtraClass('form-control'),
				])->addExtraClass('col-sm-6'),
			])->addExtraClass('row mt20'),

		]);

		if ($student) {

			$fields->push(FieldGroup::create([
				FieldGroup::create([
					$gf = GridField::create('SchoolClasses', 'School Classes', $student->SchoolClasses(), $gc = new GridFieldConfig)
				])->addExtraClass('col-sm-12'),
			])->addExtraClass('row mt20'));
			$gc->addComponent(new GridFieldToolbarHeader());
			$gc->addComponent(new GridFieldButtonRow('before'));
			$gc->addComponent(new GridFieldDataColumns());
			$gc->addComponent(new MyGridFieldDeleteAction(true));
			$gc->addComponent(new GridFieldDetailForm());
			$gc->addComponent($ac = new MyGridFieldAddExistingAutocompleter('buttons-before-right'));
			$ac->setSearchList(SchoolClass::get()->filter('SchoolID', $this->school->ID));

		}

		$buttonText = 'Update';

		if (empty($this->request->param('ID')) && ! $this->request->postVar('UserID')) {

			$fields->push(
				FieldGroup::create([
					ConfirmedPasswordField::create('Password')->setAttribute('class', 'form-control')
				])->addExtraClass('row mt20'));

			$buttonText = 'Create User';
		} elseif ($this->request->param('ID')) {
			$fields->push(HiddenField::create('UserID', null, $student->UUID));
		}

		$actions = new FieldList(
			FormAction::create('doStudentForm', $buttonText)->addExtraClass('btn btn-primary mt40')
		);


		$required = new RequiredFields(
			'Email',
			'FirstName',
			'Surname'
		);

		$form = new Form($this, 'studentForm', $fields, $actions, $required);

		if ($existingData = Session::get("FormData.{$form->getName()}.data")) {
			unset($existingData['SecurityID']);
			unset($existingData['Password']);

			$form->loadDataFrom($existingData);
			Session::clear("FormData.{$form->getName()}.data");
		}

		return $form;
	}

	public function doStudentForm($data, $form)
	{
		Session::set("FormData.{$form->getName()}.data", $data);
		$this->redirectBack();

		$newUser = false;

		if (isset($data['UserID'])) {
			$student = $this->school->Students()->filter(['UUID' => $data['UserID']])->first();

			if (empty($student)) {
				Session::set('ActionMessage', 'Student not found');
				Session::set('ActionStatus', 'danger');

				$this->redirectBack();
			}
		} else {
			$student = new Student;
			$newUser = true;
		}

		if (isset($data['Email'])) {
			if ($data['Email'] != $student->Email) {
				if (Member::get()->filter(['Email' => $data['Email']])->first()) {

					Session::set('ActionMessage', 'This email address has already been registered.');
					Session::set('ActionStatus', 'danger');
					return $this->redirectBack();
				}
			}
		}

		$pass = false;

		foreach ($this->school->SchoolAllowedDomains() as $domain)
		{
			if ($domain->check($data['Email'])) {
				$pass = true;
			}
		}

		if (! $pass) {
			Session::set('ActionMessage', 'Email not in allowed domains. If this domain should be added, please contact StudyTracks.');
			Session::set('ActionStatus', 'danger');

			return $this->redirectBack();
		}

		try {
			$form->saveInto($student);
			$student->Password = null;
			$student->write();
		} catch(Exception $e) {
			Session::set('ActionMessage', $e->getMessage());
			Session::set('ActionStatus', 'danger');

			return $this->redirectBack();
		}

		//add student into school
		$student->SchoolID = $this->user->SchoolID;
		$student->Password = $data['Password']['_Password'];
		$student->CountryID = 236;
		$student->ExamCountryID = 1;
		$student->ExamLevelID = 1;
		$student->Verified = 1;
		$student->write();

		if ($newUser) {
			if($student->School()->French){
				$student->sendFrenchSchoolRegistrationEmail($data['Password']['_Password']);
			} else {
				$student->sendSchoolRegistrationEmail($data['Password']['_Password']);
			}

		}

		Session::clear("FormData.{$form->getName()}.data");
		Session::set('ActionMessage', $student->FirstName . ' saved');
		Session::set('ActionStatus', 'success');
		Session::clear('CurrentStudentID');
		return $this->response->redirect('/school/students');
	}

	public function deleteByID()
	{
		if (! $this->request->isDELETE()) {
			return $this->httpError(404);
		}

		if (! $this->request->param('ID')) {
			return $this->httpError(404);
		}

		$user = $this->school->Students()->filter(['UUID' => $this->request->param('ID')])->first();

		$user->Deleted = 1;
		$user->write();
	}

	public function StudentUpload() {

		$fields = new FieldList([
			FieldGroup::create([FileField::create('CsvFile', false)->addExtraClass('btn')])
		]);

		$actions = new FieldList(
			FormAction::create('doStudentUpload', 'Upload Students')->addExtraClass('btn btn-upload')
		);

		$required = new RequiredFields('CsvFile');

		$form = new Form($this, 'StudentUpload', $fields, $actions, $required);

		return $form;
	}

	public function doStudentUpload($data, $form) {

		Session::set('SchoolID', $this->user->SchoolID);

		if(!isset($_FILES['CsvFile']['tmp_name'])){
			return $this->all();
		}

		$loader = new StudentCsvBulkLoader('Student');

		$results = $loader->load($_FILES['CsvFile']['tmp_name']);

		$messages = array();

		if($loader->EmptyEmail){
			$messages[] = $loader->EmptyEmail;
		}

		if($loader->DuplicateMessage){
			$messages[0] = $loader->DuplicateMessage;

			foreach($loader->DuplicateRecords as $key => $record){
				$messages[$key+1] = $record;
			}
		}

		if(!$messages) {
			$messages[] = 'Success, your import has been uploaded!';
		}

		$form->sessionMessage(implode('</br>', $messages), 'good', false);

		return $this->redirect('/school/students');
	}

	public function BulkClassChange(SS_HTTPRequest $request){

		$SchoolClassID = $this->request->postVar('SchoolClass');

		if(isset($_POST['StudentID'])){
			foreach($_POST['StudentID'] as $StudentID){

				$Student = Student::get()->byID($StudentID);

				$Student->SchoolClasses()->add($SchoolClassID);
			}
		}
		return $this->redirect('/school/students');

	}

}