<?php
	namespace Inventory\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class PurchaseEntryEditForm extends Form {
		
		//protected $investormanagementTable;
		//protected $defaultPortfolioCode;
		protected $dbAdapter;
		protected $postedValues;

		
		//public function __construct($name = null, AdapterInterface $dbAdapter) {
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;
			date_default_timezone_set("Asia/Dhaka");
			$fromDate = date('d-m-Y');			
			$bMonth = date("m",strtotime($businessDate));	
			$bYear = date("Y",strtotime($businessDate));	
			parent::__construct('purchaseentryedit');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationPStatementForm();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			
			$this->add(array(
				'name' => 'PARENT_CATEGORY',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'PARENT_CATEGORY',
					//'multiple' => 'multiple',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					//'selected' => '41',
					'value_options' => $this->getSystemCategorySelect(),
				),
			));
			
			$this->add(array(
				'name' => 'SUPPLIER_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'SUPPLIER_ID',
					//'multiple' => 'multiple',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					//'selected' => '41',
					'value_options' => $this->getSupplierSelect(),
				),
			));
			$this->add(array(
				'name' => 'RETAILER_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'retID',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					//'selected' => '41',
					'value_options' => $this->getRetailerSelect(),
				),
			));
			
			$this->add(array(
				'name' => 'tranDateFrom',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'tranDateFrom',
					'size' => '10',
					'class' => 'FormDateTypeInput',
					//'onkeyup' => 'removeChar(this)',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					//'onclick'=> 'showCalender("tranDateFrom","tranDateFrom")',
					'onchange'=> 'checkCalender(this.value,"'.date('d-m-Y',strtotime($businessDate)).'")',				
					'value' => date("01-{$bMonth}-{$bYear}"),
				),
			));
			$this->add(array(
				'name' => 'tranDateTo',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'tranDateTo',
					'size' => '10',
					//'readonly' => 'readonly',
					'class' => 'FormDateTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					//'onclick'=> 'showCalender("tranDateTo","tranDateTo")',
					'onchange'=> 'checkCalender(this.value,"'.date('d-m-Y',strtotime($businessDate)).'")',					
					'value' => date('d-m-Y',strtotime($businessDate)),
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
		}
		
		public function getSystemCategorySelect() {
			$systemNavSql = "
								SELECT 
										C.CATEGORY_ID, 
										rpad(' ',COUNT(C.CATEGORY_NAME)*5,'-')  AS CONTROLLER_DOT, 
										C.CATEGORY_NAME,
										C.LFT
								FROM 
										ls_category C, 
										ls_category P
								WHERE 
										C.LFT BETWEEN P.LFT AND P.RGT
								GROUP BY 
										C.CATEGORY_ID, C.CATEGORY_NAME, C.LFT 
								ORDER BY 
										C.LFT
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['CATEGORY_ID']] = $selectOption['CONTROLLER_DOT'].$selectOption['CATEGORY_NAME'];
			}
			return $selectData;
		}
		
		
		public function getSupplierSelect() {
			$systemNavSql = "
								SELECT 
										LSI.SUPPLIER_INFO_ID, 
										LSI.NAME
								FROM 
										ls_supplier_info LSI
								ORDER BY 
										LSI.NAME ASC
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['SUPPLIER_INFO_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
		public function getRetailerSelect() {
			$systemNavSql = "
								SELECT 
										RI.RETAILER_ID, 
										RI.NAME,
										RI.SHOP_NAME
								FROM 
										ls_retailer_info RI
								ORDER BY 
										RI.NAME ASC
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['RETAILER_ID']] = $selectOption['SHOP_NAME'];
			}
			return $selectData;
		}
	}
?>	