<?php
	namespace LocalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	class SrTargetForm extends Form {
		protected $srTargetTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;
			//$businessDate = date('d-m-Y',$businessDate);
			date_default_timezone_set("Asia/Dhaka");
			$fromDate = date('d-m-Y');			
			$bMonth = date("m",strtotime($businessDate));
			$bYear = date("Y",strtotime($businessDate));
			
			parent::__construct('srTarget');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationSrTargetForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'SR_TARGET_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'DESIGNATION_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'DESIGNATION_ID',
					'class' => 'FormSelectTypeInput', 
					'onchange'=> 'getEmployeeName(this.value);',
					'style' => 'width:220px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getDesignationSelect(),
				),
			));
			$this->add(array(
				'name' => 'EMPLOYEE_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'EMPLOYEE_ID',
					'class'=>'FormSelectTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					//'label' => 'Controller : ',
					'empty_option'  => '--- please choose ---',
					//'value_options' => $this->getEmployeeSelect(),
				),
			));
			
			/*$this->add(array(
				'name' => 'CALCULATE_IN',
				'type' => 'Select',
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => array(
						 'flat' => 'Flat',
						 'ratio' => 'Ratio',
						 
                     ),
				),
				'attributes' => array(
					'id' => 'CALCULATE_IN',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));*/
			
			$this->add(array(
				'name' => 'TARGET_FROM[]',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'TARGET_FROM_1',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%; width:80px;"
				),
			));
			$this->add(array(
				'name' => 'TARGET_TO[]',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'TARGET_TO_1',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%; width:80px;"
				),
			));
			$this->add(array(
				'name' => 'TARGET_VALUE[]',
				'type' => 'Text',
				'options' => array(
					'label' => '',
				),
				'attributes' => array(					
					'id' => 'TARGET_VALUE_1',
					'maxlength'=> '50',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%; width:80px;"
				),
			));
			$this->add(array(
				'name' => 'REMARKS',
				'type' => 'textarea',
				'attributes' => array(					
					'id' => 'REMARKS',
					'maxlength'=> '150',
					'autocomplete' => 'off',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
				
			));
			
			$this->add(array(
				'name' => 'START_DATE',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'START_DATE',
					'size' => '10',
					//'readonly' => 'readonly',
					'class' => 'FormDateTypeInput',
					//'onkeyup' => 'removeChar(this)',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					'onclick'=> 'showCalender("START_DATE","START_DATE")',
					//'onchange'=> 'checkCalender(this.value,'.date("01-{$bMonth}-Y").');generateVoucherNoList();',
					'onchange'=> 'checkCalender(this.value,"'.date('d-m-Y',strtotime($businessDate)).'");',					
					'value' => date("01-{$bMonth}-{$bYear}"),
				),
			));
			$this->add(array(
				'name' => 'END_DATE',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'END_DATE',
					'size' => '10',
					//'readonly' => 'readonly',
					'class' => 'FormDateTypeInput',
					//'onkeyup' => 'removeChar(this)',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					'onclick'=> 'showCalender("END_DATE","END_DATE")',
					'onchange'=> 'checkCalender(this.value,"'.date('d-m-Y',strtotime($businessDate)).'");',					
					'value' => date('d-m-Y',strtotime($businessDate)),
				),
			));
			
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style'=> "font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
			));
			
			
			
		}
		
		public function getDesignationSelect() {
			$systemNavSql = "SELECT 
							  d.DESIGNATION_ID,
							  d.DESIGNATION
						
							FROM 
								 gs_designation d
							ORDER BY 
							d.DESIGNATION ASC ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['DESIGNATION_ID']] = $selectOption['DESIGNATION'];
			}
			return $selectData;
		}
		
		public function getEmployeeSelect() {
			$systemNavSql = "SELECT 
							  empInfo.EMPLOYEE_ID,
							  empInfo.EMPLOYEE_NAME
						
							FROM 
								 hrms_employee_personal_info empInfo, hrms_employee_posting_info empPosting, gs_designation desg
							   WHERE empInfo.EMPLOYEE_ID = empPosting.EMPLOYEE_ID
							   AND empPosting.DESIGNATION_ID = desg.DESIGNATION_ID
							   AND desg.DESIGNATION_ID = 2
							ORDER BY 
							empInfo.EMPLOYEE_NAME ASC 
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['EMPLOYEE_ID']] = $selectOption['EMPLOYEE_NAME'];
			}
			return $selectData;
		}
		
		public function getZoneSelect() {
			$systemNavSql = "
								SELECT 
										z.ZONE_ID,
										z.NAME
								FROM 
										 ls_zone_info z
							    ORDER BY z.NAME ASC
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['ZONE_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
	}
?>	