<?php
	namespace CompanyInformation\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class BranchForm extends Form {
		
		protected $companyTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct($name);
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return addBranch();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'BRANCH_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'COMPANY_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'COMPANY_ID',
					'required' => 'required',
					'autofocus' => 'autofocus',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					'label' => '',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCompanyForSelect(),
					
				),
			));
			$this->add(array(
				'name' => 'BRANCH_NAME',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'BRANCH_NAME',
					'required' => 'required',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Branch Name : ',
				),
			));
			$this->add(array(
				'name' => 'BRANCH_CODE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'BRANCH_CODE',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '3',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Branch Code : ',
				),
			));
			$this->add(array(
				'name' => 'ADDRESS',
				'type' => 'Textarea',
				'attributes' => array(
					'id' => 'ADDRESS',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Address : ',
				),
			));
			$this->add(array(
				'name' => 'PHONE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'PHONE',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Phone Number : ',
				),
			));
			$this->add(array(
				'name' => 'FAX',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'FAX',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Fax Number : ',
				),
			));
			$this->add(array(
				'name' => 'EMAIL',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'EMAIL',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Email Address : ',
				),
			));
			$this->add(array(
				'name' => 'WEB',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'WEB',
					'class'=>'FormTextTypeInput',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				'options' => array(
					//'label' => 'Web Address : ',
				),
			));
			$this->add(array(
				'name' => 'ACTIVE_DEACTIVE',
				'type' => 'Hidden',
				'attributes' => array(
					'value' => 'y',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
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
		
		public function getCompanyForSelect() {
			$companySql 	= "SELECT * FROM c_company WHERE ACTIVE_DEACTIVE = 'y'";
			$statement 		= $this->dbAdapter->query($companySql);
			$companyData    = $statement->execute();
			$selectData 	= array();
			foreach ($companyData as $selectOption) {
				$selectData[$selectOption['COMPANY_ID']] = $selectOption['COMPANY_NAME'];
			}
			return $selectData;
		}
	}
?>	