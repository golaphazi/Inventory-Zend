<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class NationalityForm extends Form {
		protected $nationalityTable;
		protected $dbAdapter;
		
		public function __construct($name = null) {
			parent::__construct('nationality');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationNationalityForm();');
			$this->add(array(
				'name' => 'NATIONALITY_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'NATIONALITY',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'NATIONALITY',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'placeholder' => 'nationality name here'
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