<?php

class Quiz_ModelAdmin extends ModelAdmin {
    public static $managed_models = array('Quiz');
    static $url_segment = 'quiz';
    static $menu_title = 'Quiz';


    /**
     * Add new actions for sortable categories
     */
    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);

        $quiz = $form->Fields()->fieldByName('Quiz');
        if (isset($quiz)) {
			$config = $quiz->getConfig();
        	//$config->addComponent(new GridFieldSortableRows('SortOrder'));

			/** @var GridFieldPaginator $component */
			$component = $config->getComponentByType(new GridFieldPaginator());
			$component->setItemsPerPage(1000);
        }

        return $form;
    }
}