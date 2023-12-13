<?php

/**
 * Created by PhpStorm.
 * User: jonathanlittle
 * Date: 10/11/2016
 * Time: 14:07
 */
class MyGridFieldDeleteAction extends GridFieldDeleteAction
{

	/**
	 * Handle the actions and apply any changes to the GridField
	 *
	 * @param GridField $gridField
	 * @param string $actionName
	 * @param mixed $arguments
	 * @param array $data - form data
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		parent::handleAction($gridField, $actionName, $arguments, $data);
		return Controller::curr()->redirectBack();
	}

}