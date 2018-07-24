<?php
	//filename : module/Ibcia/src/Ibcia/Form/SODForm.php
	namespace Ibcia\Form;
	 
	use Zend\Form\Form;
	//use Zend\InputFilter;
	 
	class SODForm extends Form {
		public function __construct() {
			parent::__construct('SOD');
			
			$this->setAttribute('method', 'post');
			//$this->setAttribute('onsubmit', 'return check()');
			
			$this->add(array(
				'name' => 'btnSOD',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Run SOD',
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