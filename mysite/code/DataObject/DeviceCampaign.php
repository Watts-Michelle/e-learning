<?php 
class DeviceCampaign extends DataObject
{

    /** @var array  Define the required fields for the ExamCountry table */
    protected static $db = array(
        'Name' => 'Varchar(255)',
        'Message' => 'HTMLText',
        'CampaignStartDate' => 'SS_DateTime',
        'CampaignEndDate' => 'SS_DateTime'
    );

    protected static $has_many = array(
        'DeviceTypes' => 'DeviceType'
    );

    public function getTitle()
    {
        return $this->Name;
    }

    protected static $summary_fields = array(
        'Name' => 'Name',
        'Message' => 'Message',
        'CampaignStartDate' => 'CampaignStartDate',
        'CampaignEndDate' => 'CampaignEndDate'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', TextField::create('Name'));
        $fields->addFieldToTab('Root.Main', HtmlEditorField::create('Message')->setRows(3));
        $fields->addFieldToTab('Root.Main', DateField::create('CampaignStartDate'));
        $fields->addFieldToTab('Root.Main', DateField::create('CampaignEndDate'));

//        $fields->addFieldToTab('Root.DeviceTypes', GridField::create(
//            'DeviceTypes',
//            'Device Types',
//            $this->DeviceTypes(),
//            GridFieldConfig_RecordEditor::create()
//        ));

        if ($this->ID) {
            $fields->replaceField('DeviceTypes', $gf = GridField::create('DeviceTypes', 'Device Types', $this->DeviceTypes(), $gfc = new GridFieldConfig_RecordEditor()));
            $importer = new MyGridFieldImporter('before');
            $deviceImporter = new DeviceTypeImporter(new DeviceType());
            $deviceImporter->setSource(new CsvBulkLoaderSource());
            $deviceImporter->setDevice($this);
            $importer->setLoader($deviceImporter);
            $gfc->addComponent($importer);
        }

        return $fields;
    }

}