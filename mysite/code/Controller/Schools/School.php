<?php

class School_Controller extends ContentController
{

	protected $user;
	protected $school;
	protected $activePage;
	protected $title;
	protected $breadcrumbs;
	protected $pageTitle = '';
	protected $settings;
	protected $overrideMain = false;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'student', 'dashboard'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'dashboard',
	);

	protected $viewAll = [
		'Admin', 'Staff'
	];

	public function init()
	{
		parent::init();

		if (! $user = Member::currentUser()) {
			return Security::PermissionFailure($this->controller, 'You must be logged in to access this page');
		}

		if (! $this->user = Staff::get()->byID($user->ID)) {
			return Security::PermissionFailure($this->controller, 'You must be logged in as a staff member to access this page');
		}

		if (! $this->school = $this->user->School()) {
			return Security::PermissionFailure($this->controller, 'You are not a member of a school');
		}

		if ($this->school->Suspended) {
			return Security::PermissionFailure($this->controller, 'This school has been suspended, please contact StudyTracks');
		}

		$this->settings = SiteConfig::current_site_config();

		Requirements::css("{$this->ThemeDir()}/css/main.css");
		Requirements::css("http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css");

		Requirements::javascript("{$this->ThemeDir()}/js/main.min.js");
	}

	public function getShowHeader() {return true;}
	public function getShowFooter() {return true;}
	public function getFullWidth() {return true;}

	public function getMenu($level = 1)
	{
		$menu = new ArrayList([
			$staff = new ArrayData([
				'Name' => 'Staff',
				'Link' => '/school/staff',
				'Active' => $this->activePage == 'Staff' ? true : false
			]),
			$students = new ArrayData([
				'Name' => 'Students',
				'Link' => '/school/students',
				'Active' => $this->activePage == 'Students' ? true : false
			]),
			$tracks = new ArrayData([
				'Name' => 'Tracks',
				'Link' => '/school/tracks',
				'Active' => $this->activePage == 'Tracks' ? true : false
			]),
			$classes = new ArrayData([
				'Name' => 'Classes',
				'Link' => '/school/classes',
				'Active' => $this->activePage == 'Classes' ? true : false
			]),
			$homework = new ArrayData([
			    'Name' => 'Homework',
                'Link' => '/school/homework',
                'Active' => $this->activePage == 'Homework' ? true : false
            ]),
			$classes = new ArrayData([
				'Name' => 'Logout',
				'Link' => '/Security/logout',
				'Active' => false
			])
		]);

		if (!in_array($this->user->Role, $this->viewAll)) {
			$menu->remove($staff);
			$menu->remove($students);
			$menu->push(new ArrayData([
				'Name' => 'Edit Settings',
				'Link' => '/school/staff/' . $this->user->ID . '/edit',
				'Active' => $this->activePage == 'Staff' ? true : false
			]));
		}

		return $menu;
	}

	protected function renderPage($template, $data)
	{
		return $this->customise([
			'Menu' => new ArrayList([
				new ArrayData([
					'Link' => '/Security/logout',
					'MenuTitle' => 'Logout',
					'Title' => 'Logout'
				])
			]),
			'Title' => $this->title,
			'Breadcrumbs' => $this->breadcrumbs,
			'OverrideMain' => $this->overrideMain,
			'CanAdd' => in_array($this->user->Role, $this->viewAll)
		])->renderWith('School', [
			'Layout' => $this->renderWith('Schools', [
				'PageTitle' => $this->pageTitle,
				'Menu' => $this->getMenu(),
				'School' => $this->getSchool(),
				'Content' => $this->renderWith($template, $data)
			])
		]);
	}

	protected function getSchool()
	{
		return $this->user->School();
	}

	public function student(SS_HTTPRequest $request)
	{
		return (new Students_Controller())->all();
	}

	public function dashboard(SS_HTTPRequest $request)
	{
		$this->init();
		$this->title = 'StudyTracks - School Dashboard';

		$this->pageTitle = '';
		$this->overrideMain = true;

		$students = $this->school->Students()->count();
		$classes = $this->school->SchoolClasses()->count();
		$ExamLevels = ExamLevel::get()->count();


		$now = SS_Datetime::now()->Format('Y-m-d');

		$ActiveStudents = $this->school->Students()->filter(array(
			'LastAccess:GreaterThanOrEqual' => $now
		))->count();

		return $this->renderPage('Dashboard', [
			'Students' => $students,
			'Classes' => $classes,
			'ActiveStudents' => $ActiveStudents,
			'ExamLevels' => $ExamLevels
		]);
	}

	public function getFlashMessageText()
	{
		$message = Session::get('ActionMessage') ?: $this->flashMessage;
		Session::clear('ActionMessage');
		return $message;
	}

	public function getFlashMessageStatus()
	{
		$message = Session::get('ActionStatus') ?: $this->flashStatus;
		Session::clear('ActionStatus');
		return $message;
	}

}