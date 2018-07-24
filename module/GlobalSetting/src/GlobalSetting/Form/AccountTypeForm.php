<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class AccountTypeForm extends Form {
		protected $accountTypeTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('accountType');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationAccTypeForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->add(array(
				'name' => 'ACCOUNT_TYPE_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'ACCOUNT_TYPE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ACCOUNT_TYPE',
					'maxlength'=> '50',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'input account type',
				),
			));
			$this->add(array(
				'name' => 'DESCRIPTION',
				'type' => 'textarea',
				'attributes' => array(
					'id' => 'DESCRIPTION',
					'rows' => '3',
					'cols' => '24',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'maxlength'=> '250',
					'placeholder' => 'input account type description if available',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
	}
?>	