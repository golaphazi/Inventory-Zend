<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class StaffInformationForm extends Form {
		protected $staffinformationTable;
		protected $dbAdapter;
		
		public function __construct($name = null) {
			parent::__construct('staffinformation');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationStaffForm();');
			
			$this->add(array(
				'name' => 'id',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'name',
				'type' => 'text',
				'attributes' => array(
					'id' => 'name',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'required' => 'required',
					'autofocus' => 'autofocus',
					'placeholder' => 'staff name here',
				),
			));
			$this->add(array(
				'name' => 'address',
				'type' => 'textarea',
				'attributes' => array(
					'id' => 'address',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'placeholder' => 'staff address here',
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