<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class MoneyMarketOrganizationForm extends Form {
		protected $moneyMarketOrganizationTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('moneyMarketOrganization');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return moneyMarketOrganitionForm();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'ORG_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'ORG_TYPE_ID',
				'type' => 'Select',
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getOrgtypeForSelect(),
				),
				'attributes' => array(
					'id' => 'ORG_TYPE_ID',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",					
				),
			));
			$this->add(array(
				'name' => 'ORG_NAME',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ORG_NAME',
					'maxlength'=> '250',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization name here',
				),
			));
			$this->add(array(
				'name' => 'ORG_ADDRESS',
				'type' => 'textarea',
				'attributes' => array(
					'id' => 'ORG_ADDRESS',
					'rows' => '3',
					'cols' => '24',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					'maxlength'=> '500',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization address if available here',
				),
			));
			$this->add(array(
				'name' => 'ORG_PHONE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ORG_PHONE',
					'maxlength'=> '50',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization phone number',
				),
			));
			$this->add(array(
				'name' => 'ORG_FAX',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ORG_FAX',
					'maxlength'=> '50',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization fax no. here',
				),
			));
			$this->add(array(
				'name' => 'ORG_EMAIL',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ORG_EMAIL',
					'maxlength'=> '50',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization email here',
				),
			));
			$this->add(array(
				'name' => 'ORG_WEB',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ORG_WEB',
					'maxlength'=> '50',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization web address here',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style'=> "font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
			));
		}
		
		public function getOrgtypeForSelect() {
			$orgtypeSql 	= 'SELECT * FROM gs_org_type ORDER BY ORG_TYPE ASC';
			$statement 		= $this->dbAdapter->query($orgtypeSql);
			$orgtypeData    = $statement->execute();
			
			$selectData = array();
			foreach ($orgtypeData as $selectOption) {
				$selectData[$selectOption['ORG_TYPE_ID']] = $selectOption['ORG_TYPE'];
			}
			return $selectData;
		}
	}
?>	