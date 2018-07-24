<?php
	namespace Inventory\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class ConsumptionEntryForm extends Form {
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
			parent::__construct('consumptionentry');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationPaymentEntry();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			$this->add(array(
				'name' => 'BRANCH_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'BRANCH_ID',
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
				'name' => 'SUPPLIER_INFO_ID1',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'SUPPLIER_INFO_ID_1',
					'class' => 'FormSelectTypeInput', 
					'onchange'=> 'getSuppCOACode(this.value,1);',
					'style' => 'width:220px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCBCodeList(),
				),
			));
			$this->add(array(
				'name' => 'CONSUMPTION_NO',
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
			/*$portTypeSql = "SELECT c_branch.BRANCH_ID, c_branch.BRANCH_NAME
							  FROM c_branch
							MINUS
							SELECT DISTINCT P.BRANCH_ID, P.BRANCH_NAME
							  FROM c_branch P, A_TRIAL_BAL T
							 WHERE P.BRANCH_ID = T.BRANCH_ID";
			$statement = $this->dbAdapter->query($portTypeSql);
			$portTypeData    = $statement->execute();*/
			
			$selectData = array();
			/*foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['BRANCH_ID']] = $selectOption['BRANCH_NAME'];
			}*/
			return $selectData;
		}
		public function getCBCodeList() {
			$getCbCode   = "SELECT DISTINCT SUPPLIER_INFO_ID ,NAME
							  FROM ls_supplier_info
							 ";
			$statement = $this->dbAdapter->query($getCbCode);
			$portTypeData    = $statement->execute();
			$selectData = array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['SUPPLIER_INFO_ID']] = $selectOption['SUPPLIER_INFO_ID'] . '-' . $selectOption['NAME'];
			}
			return $selectData;
		}
		public function getMaxStockOrderNo() {
			$defaultOrderNo = 1;
			$maxOrderNo = '';
			$maxOrderSql = "SELECT MAX(CONSUMPTION_NO) AS MAXORDER FROM i_consumption";
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