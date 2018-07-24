<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class TechForm extends Form {
		protected $TechTable;
		protected $dbAdapter;
		public function __construct($name = null) {
			parent::__construct('Tech');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationTechForm();');			
			$this->add(array(
				'name' => 'Tech_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'name',
				'type' => 'Text',
				'attributes' => array(
					'class' => 'FormTextTypeInput', 
					'id' =>'Tech',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'Tech name here'
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
					'placeholder' => 'Tech short name'
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