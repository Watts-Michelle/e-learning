<?php

/**
 * Adds a way to import data to the GridField's DataList
 */
class MyGridFieldImporter extends GridFieldImporter implements GridField_HTMLProvider, GridField_URLHandler
{

	/**
	 * Pass importer requests to a new GridFieldImporter_Request
	 */
	public function handleImporter($gridField, $request = null)
	{
		$controller = $gridField->getForm()->getController();
		$handler    = new MyGridFieldImporter_Request($gridField, $this, $controller);

		return $handler->handleRequest($request, DataModel::inst());
	}

	/**
	 * Return a configured UploadField instance
	 *
	 * @param  GridField $gridField Current GridField
	 * @return UploadField          Configured UploadField instance
	 */
	public function getUploadField(GridField $gridField)
	{
		$uploadField = parent::getUploadField($gridField);
		$uploadField->setAllowedExtensions(array('csv', 'xls', 'xlsx'));
		return $uploadField;
	}
}
