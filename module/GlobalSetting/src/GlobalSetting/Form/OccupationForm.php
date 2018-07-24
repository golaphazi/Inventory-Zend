<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class OccupationForm extends Form {
		protected $occupationTable;
		protected $dbAdapter;
		
		public function __construct($name = null) {
			parent::__construct('occupation');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationOccuForm();');
			
			$this->add(array(
				'name' => 'OCCUPATION_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'OCCUPATION',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'OCCUPATION',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'placeholder' => 'occupation name here'
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