<?php
	//filename : module/Ibcia/src/Ibcia/Form/BusinessDateSetupForm.php
	namespace Ibcia\Form;
	 
	use Zend\Form\Form;
	//use Zend\InputFilter;
	 
	class BusinessDateSetupForm extends Form {
		public function __construct($suggestedBusinessDate = '', $jBMinDate = '') {
			parent::__construct('businessDateSetup');
			//echo $suggestedBusinessDate;die();
			$this->setAttribute('method', 'post');
			//$this->setAttribute('onsubmit', 'return check();');
			$this->add(array(
				'name' => 'BUSINESS_DATE',
				'type' => 'Text',
				'attributes' => array(
					'value' => $suggestedBusinessDate,
					'placeholder' => 'dd-mm-yyyy',
					'id' => 'businessdate',
					'required' => true,
					'autocomplete' => 'off',
				),
			));
			 
			 $this->add(array(
				'name' => 'actBDate',
				'type' => 'hidden',
				'attributes' => array(
					'value' => $jBMinDate,
					'id' => 'actBDate',
				),
			));
			
			$this->add(array(
				'name' => 'uEHDays',
				'type' => 'hidden',
				'attributes' => array(
					'id' => 'uEHDays',
				),
			));
			 
			 $this->add(array(
				'name' => 'btnSave',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Save',
					'class' => 'submit',
					'style' => 'float:none;',
					'onclick' => 'return check();'
				),
			));
			
			$this->add(array(
				'name' => 'btnLogout',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Logout',
					'class' => 'submit',
					'style' => 'float:none;',
					'onclick' => 'if(confirm("Are you sure you want to logout?")){ return true; } else { return false; }'
				),
			));
		}
	}
?>	