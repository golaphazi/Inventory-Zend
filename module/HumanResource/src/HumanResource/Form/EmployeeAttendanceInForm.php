<?php
	namespace HumanResource\Form;
	
	use Zend\InputFilter;
	use Zend\Form\Element;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	use Zend\Session\Container as SessionContainer;
		
	class EmployeeAttendanceInForm extends Form {	
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			date_default_timezone_set("Asia/Dhaka");
			
			$this->session	= new SessionContainer('post_supply');
			$businessDate	= $this->session->businessdate;
			$fromDate 		= date('d-m-Y');			
			$bMonth 		= date("m",strtotime($businessDate));	
			$bYear 			= date("Y",strtotime($businessDate));	
			
			parent::__construct($name);
			$this->setAttribute('method','post');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationEmployeeAttendanceIn();');
			
			$this->add(array(
				'name' 	=> 'ATTENDANCE_DATE',
				'type' 	=> 'text',
				'attributes' 	=> array(
					'id'		=> 'ATTENDANCE_DATE',
					'class'		=> 'FormDateTypeInput',
					'style'		=> 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
					'value' 	=> date('d-m-Y',strtotime($businessDate)),
					'readonly'	=> 'readonly',
				),
				'options' => array(
					//'label' 	=> 'Attendance Date: ',
				),
			));
			
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' 	=> 'Submit',
					'id' 		=> 'submitbutton',
					'class' 	=> 'FormSubmitBtn',
				),
			));
		}
	}
?>	