<?php

/**
 * Request handler that provides a seperate interface
 * for users to map columns and trigger import.
 */
class MyGridFieldImporter_Request extends GridFieldImporter_Request
{

	/**
	 * RequestHandler allowed actions
	 * @var array
	 */
	private static $allowed_actions = array(
		'preview', 'upload', 'import'
	);

	/**
	 * RequestHandler url => action map
	 * @var array
	 */
	private static $url_handlers = array(
		'upload!' => 'upload',
		'$Action/$FileID' => '$Action'
	);

	/**
	 * Action for getting preview interface.
	 * @param  SS_HTTPRequest $request
	 * @return string
	 */
	public function preview(SS_HTTPRequest $request)
	{
		$file = File::get()
			->byID($request->param('FileID'));
		if (!$file) {
			return "file not found";
		}

		$this->importFile(
			$file->getFullPath(),
			[],
			true,
			false
		);

		$controller = $this->getToplevelController();
		return $controller->redirectBack();
	}

	/**
	 * The import form for creating mapping,
	 * and choosing options.
	 * @return Form
	 */
	public function MapperForm()
	{
		$fields = new FieldList(
			LabelField::create('Question will be created with Question row, each subsequent column will create a new answer object.'),
			CheckboxField::create("HasHeader",
				"This data includes a header row.",
				true
			)
		);
		if ($this->component->getCanClearData()) {
			$fields->push(
				CheckboxField::create("ClearData",
					"Remove all existing records before import."
				)
			);
		}
		$actions = new FieldList(
			new FormAction("import", "Import CSV"),
			new FormAction("cancel", "Cancel")
		);
		$form = new Form($this, __FUNCTION__, $fields, $actions);

		return $form;
	}

	/**
	 * Import the current file
	 * @param  SS_HTTPRequest $request
	 */
	public function import(SS_HTTPRequest $request)
	{

		$hasheader = (bool)$request->postVar('HasHeader');
		$cleardata = $this->component->getCanClearData() ?
			(bool)$request->postVar('ClearData') :
			false;

		if ($request->postVar('action_import')) {
			$file = File::get()
				->byID($request->param('FileID'));
			if (!$file) {
				return "file not found";
			}

			$colmap = [];
			//save mapping to cache
			$this->cacheMapping($colmap);
			//do import
			$results = $this->importFile(
				$file->getFullPath(),
				$colmap,
				$hasheader,
				$cleardata
			);
			$this->gridField->getForm()
				->sessionMessage($results->getMessage(), 'good');

		}
		$controller = $this->getToplevelController();
		//$controller->redirectBack();
	}

	/**
	 * Do the import using the configured importer.
	 * @param  string $filepath
	 * @param  array|null $colmap
	 * @return BulkLoader_Result
	 */
	public function importFile($filepath, $colmap = null, $hasheader = true, $cleardata = false)
	{
		$loader = $this->component->getLoader($this->gridField);
		$loader->deleteExistingRecords = $cleardata;

		//set or merge in given col map
		if (is_array($colmap)) {
			$loader->columnMap = $loader->columnMap ?
				array_merge($loader->columnMap, $colmap) : $colmap;
		}
		$loader->getSource()
			->setFilePath($filepath)
			->setHasHeader($hasheader);

		return $loader->load();
	}

	/**
	 * Get's the previous URL that lead up to the current request.
	 *
	 * NOTE: Honestly, this should be built into SS_HTTPRequest, but we can't depend on that right now... so instead,
	 * this is being copied verbatim from Controller (in the framework).
	 *
	 * @param SS_HTTPRequest $request
	 * @return string
	 */
	protected function getBackURL(SS_HTTPRequest $request) {
		// Initialize a sane default (basically redirects to root admin URL).
		$controller = $this->getToplevelController();
		$url = method_exists($this->requestHandler, "Link") ?
			$this->requestHandler->Link() :
			$controller->Link();

		// Try to parse out a back URL using standard framework technique.
		if($request->requestVar('BackURL')) {
			$url = $request->requestVar('BackURL');
		} else if($request->isAjax() && $request->getHeader('X-Backurl')) {
			$url = $request->getHeader('X-Backurl');
		} else if($request->getHeader('Referer')) {
			$url = $request->getHeader('Referer');
		}

		return $url;
	}

}
