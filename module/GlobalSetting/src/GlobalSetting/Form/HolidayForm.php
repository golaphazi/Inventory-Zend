<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	
	class HolidayForm extends Form {
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('holiday');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationHolidayForm();');		
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->add(array(
				'name' => 'HOLIDAY_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'HOLIDAY_DATE',
				'type' => 'text',
				'attributes' => array(
					'id'	=> 'HOLIDAY_DATE',
					'class'=>'FormTextTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'click to box for select calender'
				),
			));
			$this->add(array(
				'name' => 'HOLIDAY_TYPE',
				'type' => 'Select',
				'options' => array(					
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getHolidayTypeForSelect(),
				),
				'attributes' => array(
					'class' => 'FormSelectTypeInput', 
					'id' =>'HOLIDAY_TYPE',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));	
			$this->add(array(
				'name' => 'HOLIDAY_DESCRIPTION',
				'type' => 'textarea',
				'attributes' => array(
					'id' => 'HOLIDAY_DESCRIPTION',
					'rows' => '3',
					'cols' => '24',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'maxlength'=> '250',
					'placeholder' => 'holiday description here if available'
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
		public function getHolidayTypeForSelect() {
			$selectData = array();
			
			$selectData['Weekend'] 		= 'Weekend';
			$selectData['Government'] 	= 'Government';
			$selectData['Unexpected'] 	= 'Unexpected';
			
			return $selectData;
		}
	}
?>	