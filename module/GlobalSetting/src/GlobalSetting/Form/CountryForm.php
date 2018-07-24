<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class CountryForm extends Form {
		protected $countryTable;
		protected $dbAdapter;
		public function __construct($name = null) {
			parent::__construct('country');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationCountryForm();');			
			$this->add(array(
				'name' => 'COUNTRY_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'COUNTRY',
				'type' => 'Text',
				'attributes' => array(
					'class' => 'FormTextTypeInput', 
					'id' =>'COUNTRY',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'country name here'
				),
			));
			$this->add(array(
				'name' => 'SHORT_NAME',
				'type' => 'Text',
				'attributes' => array(
					'class' => 'FormTextTypeInput', 
					'id' =>'SHORT_NAME',
					'maxlength'=> '20',
					'autocomplete' => 'off',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'country short name'
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