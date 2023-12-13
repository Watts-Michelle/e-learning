<?php

class Lesson_ModelAdmin extends ModelAdmin {
    public static $managed_models = array('Lesson');
    static $url_segment = 'lesson';
    static $menu_title = 'Lesson';

	private static $model_importers = array(
		'Lesson' => 'LessonImporter',
	);

    /**
     * Add new actions for sortable categories
     */
    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);

        $lesson = $form->Fields()->fieldByName('Lesson');
        if (isset($lesson)) {
        	/** @var GridFieldConfig $config */
        	$config = $lesson->getConfig();
            //$config->addComponent(new GridFieldSortableRows('LessonSortOrder'));

			/** @var GridFieldPaginator $component */
			$component = $config->getComponentByType(new GridFieldPaginator());
			$component->setItemsPerPage(1000);
        }

        return $form;
    }
    
}