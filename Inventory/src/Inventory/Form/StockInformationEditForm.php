<?php
	namespace Inventory\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class StockInformationEditForm extends Form {
		
		//protected $investormanagementTable;
		//protected $defaultPortfolioCode;
		protected $dbAdapter;
		protected $postedValues;

		
		//public function __construct($name = null, AdapterInterface $dbAdapter) {
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;			
			parent::__construct('stockinformationedit');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationBatchUpdateScripForm();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			$this->add(array(
				'name' => 'SUPPLIER_INFO_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'SUPPLIER_INFO_ID',
					//'onchange'=> 'coa_code_suggest();',
					'style' => 'width:200px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getSPList(),
				),
			));
			$this->add(array(
				'name' => 'pfStatementDateFrom',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'pfStatementDateFrom',
					'size' => '10',
					'readonly' => 'readonly',
					'class' => 'FormDateTypeInput',
					'onkeyup' => 'removeChar(this)',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					//'onclick'=> 'showCalender("pfStatementDateFrom","pfStatementDateFrom")',
					//'onchange'=> 'checkCalender(this.value,'.date("01-{$bMonth}-Y").')',					
					'value' => date('d-m-Y',strtotime($businessDate)),
				),
			));
			/*$this->add(array(
				'name' => 'pfStatementDateTo',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'pfStatementDateTo',
					'size' => '10',
					'readonly' => 'readonly',
					'class' => 'FormDateTypeInput',
					'onkeyup' => 'removeChar(this)',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					'onclick'=> 'showCalender("pfStatementDateTo","pfStatementDateTo")',
					'onchange'=> 'checkCalender(this.value,'.$businessDate.')',					
					'value' => $businessDate,
				),
			));*/
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
		public function getSPList() {
			$getCbCode   = "SELECT DISTINCT SUPPLIER_INFO_ID ,NAME
							  FROM ls_supplier_info
							 ";
			$statement = $this->dbAdapter->query($getCbCode);
			$portTypeData    = $statement->execute();
			$selectData = array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['SUPPLIER_INFO_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
	}
?>	