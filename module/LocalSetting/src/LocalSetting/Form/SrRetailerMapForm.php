<?php
	namespace LocalSetting\Form;
	
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class SrRetailerMapForm extends Form {
		protected $srRetailerMapTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('srRetailerMap');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationSrRetailerMapForm();');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'SR_RETAILER_MAP_ID',
				'type' => 'Hidden',
			));
			
			$this->add(array(
				'name' => 'DESIGNATION_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'DESIGNATION_ID',
					'class' => 'FormSelectTypeInput', 
					'onchange'=> 'getEmployeeName(this.value);',
					'style' => 'width:220px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
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
			
			$this->add(array(
				'name' => 'RETAILER_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'RETAILER_ID',
					'class'=>'FormSelectTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					//'label' => 'Controller : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getRetailerSelect(),
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
							empInfo.EMPLOYEE_NAME ASC ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['EMPLOYEE_ID']] = $selectOption['EMPLOYEE_NAME'];
			}
			return $selectData;
		}
		
		public function getRetailerSelect() {
			$systemNavSql = "SELECT 
							  ri.RETAILER_ID,
							  ri.SHOP_NAME NAME
						
							FROM 
								 ls_retailer_info ri
							ORDER BY 
							ri.NAME ASC";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['RETAILER_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
	}
?>	