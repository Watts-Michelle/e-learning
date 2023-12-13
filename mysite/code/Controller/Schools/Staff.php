<?php

class Staff_Controller extends School_Controller
{

	protected $activePage = 'Staff';
	private $exit;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'all',
		'byID',
		'editByID',
		'deleteByID',
		'staffForm',
		'doStaffForm',
		'deleteStaff',
		'addStaff'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'create' => 'addStaff',
		'staffForm' => 'staffForm',
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
				'Name' => 'Staff List',
				'Link' => '/school/staff'
			]),
		]);
	}

	public function all()
	{
		if (! in_array($this->user->Role, $this->viewAll)) {
			return $this->httpError(404);
		}

		$this->title = 'StudyTracks - Staff List';
		$this->pageTitle = 'Manage staff';

		$staff = $this->school->Staff();

		$row = $this->breadcrumbs->first();
		$row->Active = 1;

		$settings = SiteConfig::current_site_config();

		$video = $settings->VimeoTeacherHelpVideo;

		return $this->renderPage('UserList', [
			'Users' => $staff,
			'HelpVideo' => $video
		]);
	}

	public function byID()
	{
		if (! $this->request->param('ID')) {
			return $this->all();
		}

		if (! in_array($this->user->Role, $this->viewAll) && $this->request->param('ID') != $this->user->ID) {
			return $this->httpError(404);
		}

		$staff = $this->school->Staff()->byID($this->request->param('ID'));

		if (! $staff) {
			return $this->httpError(404);
		}

		$this->pageTitle = $staff->FirstName . ' ' . $staff->Surname;
		$this->title = 'StudyTracks - Staff - ' . $staff->FirstName . ' ' . $staff->Surname;
		$this->breadcrumbs->add(new ArrayData([
			'Name' => $staff->FirstName . ' ' . $staff->Surname,
			'Link' => '',
			'Active' => 1
		]));

		return $this->renderPage('UserScreen', [
			'Staff' => $staff
		]);
	}

	public function addStaff()
	{
		if (! $this->school->canAddStaff()) {

			Session::set('ActionMessage', 'You have reached your school\'s staff cap, please contact StudyTracks for details of how to increase this.');
			Session::set('ActionStatus', 'warning');

			return $this->redirectBack();
		}

		if (! in_array($this->user->Role, $this->viewAll)) {
			return $this->httpError(404);
		}

		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'create',
			'Link' => '',
			'Active' => 1
		]));

		$this->pageTitle = 'Add a new staff member';
		$this->title = 'StudyTracks - Add a new staff member';

		return $this->renderPage('UserForm', []);
	}

	public function editByID()
	{
		if (! in_array($this->user->Role, $this->viewAll) && $this->request->param('ID') != $this->user->ID) {
			return $this->httpError(404);
		}

		$staff = $this->school->Staff()->byID($this->request->param('ID'));

		if (! $staff) {
			return $this->httpError(404);
		}

		$this->breadcrumbs->add(new ArrayData([
			'Name' => $staff->FirstName . ' ' . $staff->Surname,
			'Link' => '/school/staff/' . $this->request->param('ID'),
			'Active' => 0
		]));

		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'edit',
			'Link' => '',
			'Active' => 1
		]));

		$this->pageTitle = 'Edit ' . $staff->FirstName . ' ' . $staff->Surname;
		$this->title = 'StudyTracks - Update staff';

		return $this->renderPage('UserForm', [
			'Staff' => $staff
		]);
	}

	public function staffForm()
	{
		if ($this->request->param('ID')) {
			if (!in_array($this->user->Role, $this->viewAll) && $this->request->param('ID') != $this->user->ID) {
				return $this->httpError(404);
			}
		}

		$staff = $this->school->Staff()->byID($this->request->param('ID'));

		if (! $staff && $this->request->param('ID')) {
			return $this->httpError(404);
		}

		$fields = new FieldList([
			FieldGroup::create([
				FieldGroup::create([
					TextField::create('FirstName', null, !empty($staff) ? $staff->FirstName : null)->addExtraClass('form-control'),
				])->addExtraClass('col-sm-6'),
				FieldGroup::create([
					TextField::create('Surname', null, !empty($staff) ? $staff->Surname : null)->addExtraClass('form-control'),
				])->addExtraClass('col-sm-6'),
			])->addExtraClass('row mt30'),
			FieldGroup::create([
				FieldGroup::create([
					EmailField::create('Email', null, !empty($staff) ? $staff->Email : null)->addExtraClass('form-control'),
				])->addExtraClass('col-sm-6'),
			])->addExtraClass('row mt20'),
		]);

		if (in_array($this->user->Role, $this->viewAll)) {
			$fields->push(
				FieldGroup::create([
					FieldGroup::create([
						DropdownField::create('Role', 'Role', singleton('Staff')->dbObject('Role')->enumValues(), !empty($staff) ? $staff->Role: null)->addExtraClass('form-control')->setEmptyString('Select Role'),
					])->addExtraClass('col-sm-6'),
				])->addExtraClass('row mt20')
			);
		}

		$buttonText = 'Update';

		$empty = false;

		if ((empty($this->request->param('ID')) && ! $this->request->postVar('UserID')) || $empty = ($this->request->param('ID') == $this->user->ID || $this->request->postVar('UserID') == $this->user->ID)) {
			$fields->push(
				FieldGroup::create([
					ConfirmedPasswordField::create('Password')->setAttribute('class', 'form-control')->setCanBeEmpty($empty)
				])->addExtraClass('row mt20'));

		}

		if ($this->request->param('ID')) {
			$fields->push(HiddenField::create('UserID', null, $staff->ID));
		} else {
			$buttonText = 'Create User';
		}

		$actions = new FieldList(
			FormAction::create('doStaffForm', $buttonText)->addExtraClass('btn btn-primary mt40')
		);

		if (in_array($this->user->Role, $this->viewAll)) {
			$actions->push(
				FormAction::create('doStaffFormExit', $buttonText . ' and exit record')->addExtraClass('btn btn-primary mt40')
			);
		}

		$required = new RequiredFields(
			'Email',
			'FirstName',
			'Surname'
		);

		$form = new Form($this, 'staffForm', $fields, $actions, $required);

		if ($existingData = Session::get("FormData.{$form->getName()}.data")) {
			unset($existingData['SecurityID']);
			unset($existingData['Password']);

			$form->loadDataFrom($existingData);
		}

		return $form;
	}

	public function doStaffFormExit($data, $form) {
		$this->exit = true;

		return $this->doStaffForm($data, $form);
	}

	public function doStaffForm($data, $form)
	{

		Session::set("FormData.{$form->getName()}.data", $data);
		$new = false;

		if (isset($data['UserID'])) {
			$staff = $this->school->Staff()->filter(['ID' => $data['UserID']])->first();

			if (empty($staff)) {
				Session::set('ActionMessage', 'User not found');
				Session::set('ActionStatus', 'danger');

				$this->redirectBack();
			}

		} else {
			$staff = new Staff;
			$new = true;
		}

		if (isset($data['Email'])) {
			if ($data['Email'] != $staff->Email) {
				if (Member::get()->filter(['Email' => $data['Email']])->first()) {

					Session::set('ActionMessage', 'This email address has already been registered.');
					Session::set('ActionStatus', 'danger');
					return $this->redirectBack();
				}
			}
		}

		try {
			$form->saveInto($staff);
			$staff->write();
		} catch(Exception $e) {
			Session::set('ActionMessage', $e->getMessage());
			Session::set('ActionStatus', 'danger');

			return $this->redirectBack();
		}

		//add student into school
		$staff->SchoolID = $this->user->SchoolID;

		try {
			$staff->write();
		} catch(Exception $e) {
			Session::set('ActionMessage', $e->getMessage());
			Session::set('ActionStatus', 'danger');

			return $this->redirectBack();
		}

		Session::clear("FormData.{$form->getName()}.data");
		Session::set('ActionMessage', $staff->FirstName . ' saved');
		Session::set('ActionStatus', 'success');

		return $this->response->redirect($this->exit ? '/school/staff/' : '/school/staff/' . $staff->ID . '/edit');
	}

	public function deleteByID()
	{
		if (! $this->request->isDELETE()) {
			return $this->httpError(404);
		}

		if (! $this->request->param('ID')) {
			return $this->httpError(404);
		}

		$user = $this->school->Staff()->filter(['UUID' => $this->request->param('ID')])->first();
		$user->destroy();
		$user->delete();
	}

}