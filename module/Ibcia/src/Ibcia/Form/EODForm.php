<?php
	//filename : module/Ibcia/src/Ibcia/Form/EODForm.php
	namespace Ibcia\Form;
	 
	use Zend\Form\Form;
	//use Zend\InputFilter;
	 
	class EODForm extends Form {
		public function __construct() {
			parent::__construct('EOD');
			
			$this->setAttribute('method', 'post');
			//$this->setAttribute('onsubmit', 'return check()');
			
			$this->add(array(
				'name' => 'btnEOD',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Run EOD',
					'class' => 'submit',
					'style' => 'float:none;',
					'onclick' => 'return check();'
				),
			));
			
			$this->add(array(
				'name' => 'btnBack',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Back',
					'class' => 'submit',
					'style' => 'float:none;',
					'onclick' => 'if(confirm("Do you want to go back home page now?")){ return true; } else { return false; }'
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