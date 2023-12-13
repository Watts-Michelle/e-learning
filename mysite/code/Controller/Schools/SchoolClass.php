<?php

class SchoolClass_Controller extends School_Controller
{

	protected $activePage = 'Classes';

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'all',
		'byID',
		'editByID',
		'deleteByID',
		'schoolClassForm',
		'addSchoolClass',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'schoolClassForm' => 'schoolClassForm',
		'create' => 'addSchoolClass',
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
				'Name' => 'Classes List',
				'Link' => '/school/classes',
			]),
		]);
	}

	public function all()
	{
		$this->title = 'StudyTracks - Classes List';
		$this->pageTitle = 'Manage classes';

		if (in_array($this->user->Role, $this->viewAll)) {
			$classes = $this->user->School()->SchoolClasses();
		} else {
			$classes = $this->user->School()->SchoolClasses()->filter(['StaffID' => $this->user->ID]);
		}

		$row = $this->breadcrumbs->first();
		$row->Active = 1;

		return $this->renderPage('SchoolClassList', [
			'Classes' => $classes
		]);
	}

	public function byID()
	{
		if (! $this->request->param('ID')) {
			return $this->all();
		}

		$class = $this->getClass();

		$this->title = 'StudyTracks - Classes - ' . $class->Name;
		$this->pageTitle = 'Manage students in ' . $class->Name;

		$this->breadcrumbs->add(new ArrayData([
			'Name' => $class->Name,
			'Link' => '',
			'Active' => 1
		]));

		return $this->renderPage('StudentList', [
			'Class' => $class,
			'Students' => $class->Students()->filter(['Deleted' => 0]),
			'SchoolClassID' => $this->request->param('ID'),
			'ExtraButtons' => in_array($this->user->Role, $this->viewAll) ? '
			<a href="/school/classes/' . $this->request->param('ID') . '/edit" class="edit btn btn-default">edit class <i class="glyphicon glyphicon-pencil"></i></a>
			<a href="#" class="delete btn btn-danger" data-type="class" data-id="' . $this->request->param('ID') . '">delete class <i class="glyphicon glyphicon-remove"></i></a>
			' : ''
		]);
	}

	public function editByID()
	{
		$class = $this->getClass(false, false);

		$this->pageTitle = 'Edit ' . $class->Name;

		$this->breadcrumbs->add(new ArrayData([
			'Name' => $class->Name,
			'Link' => '/school/classes/' . $this->request->param('ID'),
			'Active' => 0
		]));

		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'edit',
			'Link' => '',
			'Active' => 1
		]));

		$this->title = 'StudyTracks - Update student';

		return $this->renderPage('SchoolClassForm', [
			'Class' => $class
		]);
	}

	public function addSchoolClass()
	{
		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'create',
			'Link' => '',
			'Active' => 1
		]));

		$this->pageTitle = 'Add a new school class';
		$this->title = 'StudyTracks - Add a new school class';

		return $this->renderPage('SchoolClassForm', []);
	}

	public function schoolClassForm()
	{
		$class = $this->getClass(true);

		$fields = new FieldList([
			FieldGroup::create([
				FieldGroup::create([
					TextField::create('Name', 'Name', isset($class->Name) ? $class->Name : null)->addExtraClass('form-control'),
				])->addExtraClass('col-sm-6'),
				FieldGroup::create([
					DropdownField::create('StaffID', 'Teacher', Staff::get()->filter('SchoolID', $this->school->ID)->map('ID', 'Name'), isset($class->StaffID) ? $class->StaffID : null)->addExtraClass('form-control')->setEmptyString('Select a teacher'),
				])->addExtraClass('col-sm-6'),
			])->addExtraClass('row mt30'),
		]);

		if ($this->request->param('ID')) {
			$fields->push(HiddenField::create('ClassID', '', $this->request->param('ID')));
		}

		if ($class) {
			$buttonText = 'Update';
		} else {
			$buttonText = 'Create class';
		}

		$actions = new FieldList([
			FormAction::create('doSchoolClassForm', $buttonText)->addExtraClass('btn btn-primary mt40')
		]);

		$required = new RequiredFields('Name');

		$form = Form::create($this, 'schoolClassForm', $fields, $actions, $required);

		if ($existingData = Session::get("FormData.{$form->getName()}.data")) {
			unset($existingData['SecurityID']);

			$form->loadDataFrom($existingData);
			Session::clear("FormData.{$form->getName()}.data");
		}

		return $form;
	}

	public function doSchoolClassForm($data, $form)
	{
		Session::set("FormData.{$form->getName()}.data", $data);

		if (isset($data['ClassID'])) {
			$currentClass = $this->school->SchoolClasses()->byID($data['ClassID']);
		}

		$class = $this->school->SchoolClasses()->filter(['Name' => $data['Name']])->first();

		if (! empty ($class) && (empty($currentClass) || $currentClass->Name != $data['Name'])) {
			Session::set('ActionMessage', 'A school class with this name already exists.');
			Session::set('ActionStatus', 'danger');

			return $this->redirectBack();
		}

		if (empty($currentClass)) {
			$currentClass = new SchoolClass;
		}

		$currentClass->Name = $data['Name'];
		$currentClass->StaffID = $data['StaffID'];
		$currentClass->write();

		$this->school->SchoolClasses()->add($currentClass);
		Session::clear("FormData.{$form->getName()}.data");

		return $this->response->redirect('/school/classes');
	}

	public function deleteByID()
	{
		if (! $this->request->isDELETE()) {
			return $this->httpError(404);
		}

		if (! $this->request->param('ID')) {
			return $this->httpError(404);
		}

		$schoolClass = $this->school->SchoolClasses()->byID($this->request->param('ID'));

		if ($schoolClass) {
			$schoolClass->destroy();
			$schoolClass->delete();
		}
	}

	private function getClass($allowNoID = false, $allowOwn = true)
	{
		if (in_array($this->user->Role, $this->viewAll)) {
			if ($id = $this->request->param('ID')) {
				$class = $this->user->School()->SchoolClasses()->byID($id);
			} else {
				$class = $this->user->School()->SchoolClasses();
			}
		} else {
			if ($allowOwn) {
				if ($id = $this->request->param('ID')) {
					$class = $this->user->School()->SchoolClasses()->filter(['StaffID' => $this->user->ID])->byID($id);
				} else {
					$class = $this->user->School()->SchoolClasses()->filter(['StaffID' => $this->user->ID]);
				}
			} else {
				return $this->httpError(404);
			}
		}

		if ($allowNoID) {
			if (! $class && $this->request->param('ID')) {
				return $this->httpError(404);
			}
		} else if (! $class) {
			return $this->httpError(404);
		}

		return $class;
	}
}