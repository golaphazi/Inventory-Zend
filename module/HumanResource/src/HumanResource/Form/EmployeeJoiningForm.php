<?php
	namespace HumanResource\Form;
	
	use Zend\InputFilter;
	use Zend\Form\Element;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
		
	class EmployeeJoiningForm extends Form {	
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			parent::__construct($name);
			
			$this->addElements();
			
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationEmployeeJoining();');
		}
		
		public function addElements() {
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Submit',
					'id' => 'submitbutton',
					'class' =>'FormSubmitBtn',
				),
			));
		}
	}
?>	