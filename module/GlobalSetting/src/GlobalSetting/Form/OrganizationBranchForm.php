<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class OrganizationBranchForm extends Form {
		protected $organizationBranchTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('organizationBranch');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationOrgBranchForm();');			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'ORG_BRANCH_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'ORG_ID',
				'type' => 'Select',
				'options' => array(
					'label' => '',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getOrganizationForSelect(),
				),
				'attributes' => array(
					'id' => 'ORG_ID',
					'class'=>'FormSelectTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
			));
			$this->add(array(
				'name' => 'BRANCH_NAME',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'BRANCH_NAME',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '250',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input organization branch here',
				),
			));
			$this->add(array(
				'name' => 'BRANCH_CODE',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(
					'id' => 'BRANCH_CODE',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '100',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'input branch code',
				),
			));
			
			$this->add(array(
				'name' => 'ADDRESS',
				'type' => 'textarea',
				'attributes' => array(
					'id' => 'ADDRESS',
					'rows' => '3',
					'cols' => '24',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
					'placeholder' => 'input organization branch address if available',
				),
			));
			$this->add(array(
				'name' => 'PHONE',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'input organization phone no.',
				),
			));
			$this->add(array(
				'name' => 'FAX',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'input organization fax no.',
				),
			));
			$this->add(array(
				'name' => 'EMAIL',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'input organization email address',
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
		
		public function getOrganizationForSelect() {
			$orgtypeSql = 'SELECT * FROM gs_money_mkt_org ORDER BY ORG_NAME ASC';
			$statement = $this->dbAdapter->query($orgtypeSql);
			$orgtypeData    = $statement->execute();
			
			$selectData = array();
			foreach ($orgtypeData as $selectOption) {
				$selectData[$selectOption['ORG_ID']] = $selectOption['ORG_NAME'];
			}
			return $selectData;
		}
	}
?>	