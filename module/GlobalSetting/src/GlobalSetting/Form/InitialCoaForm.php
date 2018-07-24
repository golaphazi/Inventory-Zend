<?php
	namespace GlobalSetting\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Session\Container as SessionContainer;
	
	class InitialCoaForm extends Form {
		protected $dbAdapter;
		protected $postedValues;
		
		//public function __construct($name = null, AdapterInterface $dbAdapter) {
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;
			date_default_timezone_set("Asia/Dhaka");
			
			parent::__construct('initialcoa');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationInitialCoa();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			
			$this->add(array(
			'name' => 'submit',
			'type' => 'Submit',
			'attributes' => array(
				'value' => 'Process Start',
				'id' => 'submitbutton',
				'style'=> "font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
			),
		));
			
			
		}
		
		
	}
?>	