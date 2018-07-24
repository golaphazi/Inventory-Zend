<?php
	namespace Inventory\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class SRWiseStockReturnForm extends Form {
		protected $investormanagementTable;
		protected $defaultPortfolioCode;
		protected $dbAdapter;
		protected $postedValues;
		protected $tradeCode;
		
		//public function __construct($name = null, AdapterInterface $dbAdapter) {
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;			
			date_default_timezone_set("Asia/Dhaka");
			$fromDate = date('d-m-Y');			
			$bMonth = date("m",strtotime($businessDate));
			$bYear = date("Y",strtotime($businessDate));
			parent::__construct('srwisestockdistribution');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationPaymentEntry();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			$this->add(array(
				'name' => 'EMPLOYEE_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'EMPLOYEE_ID',
					'onchange'=> 'getRetCOACode(this.value,1);',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCompBranchListSelect(),
				),
				/*'attributes' => array(
					'value' => '41',
				),*/
			));
			$this->add(array(
				'name' => 'ORDER_NO',
				'type' => 'hidden',
				'required'	=> true,
				'attributes' => array(
					'id' => 'ORDER_NO',
					'value' => $this->getMaxStockOrderNo(),
				),
			));
			$this->add(array(
				'name' => 'tranDateTo',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'tranDateTo',
					'size' => '10',
					'readonly' => 'readonly',
					'class' => 'FormDateTypeInput',
					'onkeyup' => 'removeChar(this)',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					'onchange'=> 'checkCalender(this.value,"'.date('d-m-Y',strtotime($businessDate)).'")',					
					'value' => date('d-mm-Y',strtotime($businessDate)),
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'onclick' => '',
				),
			));
			$this->add(array(
				'name' => 'btnClose',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Close',
					'id' => '',
					'onclick' => 'return chkButtonClose();',
				),
			));
		}		
		public function getCompBranchListSelect() {
			$portTypeSql = "SELECT hrms_employee_personal_info.EMPLOYEE_ID, hrms_employee_personal_info.EMPLOYEE_NAME
							  FROM hrms_employee_personal_info,hrms_employee_posting_info,gs_designation
							 WHERE hrms_employee_personal_info.EMPLOYEE_ID = hrms_employee_posting_info.EMPLOYEE_ID
							 AND gs_designation.DESIGNATION_ID = hrms_employee_posting_info.DESIGNATION_ID
							 AND gs_designation.DESIGNATION = 'sales representative'
							 ";
			$statement = $this->dbAdapter->query($portTypeSql);
			$portTypeData    = $statement->execute();
			
			$selectData = array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['EMPLOYEE_ID']] = $selectOption['EMPLOYEE_NAME'];
			}
			return $selectData;
		}
		public function getMaxStockOrderNo() {
			$defaultOrderNo = 1;
			$maxOrderNo = '';
			$maxOrderSql = "SELECT MAX(ORDER_NO) AS MAXORDER FROM i_sr_stock_dist";
			$statement = $this->dbAdapter->query($maxOrderSql);
			$maxOrderData    = $statement->execute();
			$selectData = array();
			foreach ($maxOrderData as $maxOrderNo) {
				$defaultOrderNo = $maxOrderNo['MAXORDER']+1;
			}
			return $defaultOrderNo;
		}
	}
?>	