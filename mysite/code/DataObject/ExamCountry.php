<?php 
class ExamCountry extends DataObject {

    /** @var array  Define the required fields for the ExamCountry table */
    protected static $db = array(
        'Name' => 'Varchar(255)'
    );

    protected static $has_one = array(
        'Flag' => 'Image'
    );

    protected static $has_many = array(
        'ExamLevels' => 'ExamLevel',
        'Students' => 'Student',
        'Schools' => 'School'
    );

    protected static $summary_fields = array(
        'Name' => 'Name',
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Students');
        return $fields;
    }

    public function getBasic()
	{

        $image = $this->Flag()->exists() ? $this->Flag()->ScaleWidth(500)->AbsoluteLink() : null;

		$array = [
			'name' => $this->Name,
            'flag' => $image,
			'exam_levels' => []
		];

		foreach ($this->ExamLevels()->filter(['Live' => 1]) as $examLevel) {
			$array['exam_levels'][] = $examLevel->getBasic();
		}

		return $array;
	}

}