<?php 
class SubscriptionPurchaseTest extends DataObject {

    /** @var array  Define the required fields for the ExamCountry table */
    protected static $db = array(
        'OrderId' => 'Varchar(255)',
        'PackageName' => 'Varchar(255)',
        'ProductId' => 'Varchar(255)',
        'PurchaseTime' => 'Varchar(255)',
        'PurchaseState' => 'Varchar(255)',
        'DeveloperPayload' => 'Varchar(255)',
        'PurchaseToken' => 'Varchar(255)',
        'AutoRenewing' => 'Varchar(255)'
    );

    protected static $has_one = array(
        'Student' => 'Student'
    );

    public function getBasic()
    {
        $subscription = [
            'OrderId' => $this->OrderId,
            'PackageName' => $this->PackageName,
            'ProductId' => $this->ProductId,
            'PurchaseTime' => $this->PurchaseTime,
            'PurchaseState' => $this->PurchaseState,
            'DeveloperPayload' => $this->DeveloperPayload,
            'PurchaseToken' => $this->PurchaseToken,
            'AutoRenewing' => $this->AutoRenewing
        ];

        return $subscription;
    }
}