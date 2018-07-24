<?php
	namespace Inventory\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class SalesReturnForm extends Form {
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
			parent::__construct('salesreturn');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationPaymentEntry();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			$this->add(array(
				'name' => 'ZONE_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'ZONE_ID',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:110%;width:220px;',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getZoneListSelect(),
				),
				/*'attributes' => array(
					'value' => '41',
				),*/
			));
			$this->add(array(
				'name' => 'RETAILER_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'retID',
					'value' => (empty($this->postedValues['RETAILER_ID'])) ? "" : $this->postedValues['RETAILER_ID'],
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:110%;width:220px;',
					'class' => 'FormSelectTypeInput',
					'onchange'=> 'getRetCOACode(this.value,1);',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => (empty($this->postedValues['ZONE_ID'])) ? array() : $this->getZoneListSelect($this->postedValues['ZONE_ID']),
				),
			));
			$this->add(array(
				'name' => 'EMPLOYEE_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'srID',
					'value' => (empty($this->postedValues['EMPLOYEE_ID'])) ? "" : $this->postedValues['EMPLOYEE_ID'],
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:110%;width:220px;',
					'class' => 'FormSelectTypeInput',
					'onchange'=> 'getSRCOACode(this.value,1);',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => (empty($this->postedValues['ZONE_ID'])) ? array() : $this->getZoneListSelect($this->postedValues['ZONE_ID']),
				),
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
			$portTypeSql = "SELECT ls_retailer_info.RETAILER_ID, ls_retailer_info.NAME
							  FROM ls_retailer_info
							 ORDER BY ls_retailer_info.NAME ASC
							 ";
			$statement = $this->dbAdapter->query($portTypeSql);
			$portTypeData    = $statement->execute();
			
			$selectData = array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['RETAILER_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
		public function getZoneListSelect() {
			$portTypeSql = "SELECT ls_zone_info.ZONE_ID, ls_zone_info.NAME
							  FROM ls_zone_info
							 ORDER BY ls_zone_info.NAME ASC
							 ";
			$statement = $this->dbAdapter->query($portTypeSql);
			$portTypeData    = $statement->execute();
			
			$selectData = array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['ZONE_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
		public function getMaxStockOrderNo() {
			$defaultOrderNo = 1;
			$maxOrderNo = '';
			$maxOrderSql = "SELECT MAX(ORDER_NO) AS MAXORDER FROM i_retailer_stock_dist";
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