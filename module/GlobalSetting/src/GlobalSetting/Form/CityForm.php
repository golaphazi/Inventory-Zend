<?php
	namespace GlobalSetting\Form;
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	class CityForm extends Form {
		protected $countryTable;
		protected $dbAdapter;
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('city');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationCityForm();');		
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->add(array(
				'name' => 'CITY_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'COUNTRY_ID',
				'type' => 'Select',
				'options' => array(					
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCountryForSelect(),
				),
				'attributes' => array(
					'class' => 'FormSelectTypeInput', 
					'id' =>'COUNTRY_ID',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
			$this->add(array(
				'name' => 'CITY',
				'type' => 'Text',				
				'attributes' => array(
					'class' => 'FormTextTypeInput', 
					'id' =>'CITY',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'city name here'
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
		public function getCountryForSelect() {
			$countrySql 	= 'SELECT * FROM gs_country';
			$statement 		= $this->dbAdapter->query($countrySql);
			$countryData    = $statement->execute();
			
			$selectData = array();
			foreach ($countryData as $selectOption) {
				$selectData[$selectOption['COUNTRY_ID']] = $selectOption['COUNTRY'];
			}
			return $selectData;
		}
	}
?>	