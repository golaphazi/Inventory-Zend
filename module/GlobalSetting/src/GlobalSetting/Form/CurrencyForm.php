<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class CurrencyForm extends Form {
		protected $currencyTable;
		protected $dbAdapter;
		public function __construct($name = null) {
			parent::__construct('currency');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationCurrencyForm();');			
			$this->add(array(
				'name' => 'CURRENCY_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'CURRENCY_NAME',
				'type' => 'Text',
				'attributes' => array(
					'class' => 'FormTextTypeInput', 
					'id' =>'CURRENCY_NAME',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
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