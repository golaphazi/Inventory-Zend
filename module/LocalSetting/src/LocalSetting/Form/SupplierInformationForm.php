<?php
	namespace LocalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class SupplierInformationForm extends Form {
		protected $supplierInformationTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('supplierInformation');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationSupplierInformationForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'SUPPLIER_INFO_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'NAME',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'NAME',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'supplier name here'
				),
			));
			$this->add(array(
				'name' => 'SHORT_NAME',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'SHORT_NAME',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'short name'
				),
			));
			$this->add(array(
				'name' => 'ADDRESS',
				'type' => 'textarea',
				'attributes' => array(					
					'id' => '',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'supplier address if available'
				),
				
			));
			$this->add(array(
				'name' => 'PHONE',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'PHONE',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'phone no.'
				),
			));
			$this->add(array(
				'name' => 'FAX',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'FAX',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'fax no.'
				),
			));
			$this->add(array(
				'name' => 'MOBILE',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'MOBILE',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'mobile/cell no.'
				),
			));
			$this->add(array(
				'name' => 'WEB',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'WEB',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'web address here'
				),
			));
			$this->add(array(
				'name' => 'EMAIL',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'EMAIL',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;",
					'placeholder' => 'email address here'
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
	}
?>	