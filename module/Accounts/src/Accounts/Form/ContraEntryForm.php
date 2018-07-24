<?php
	namespace Accounts\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class ContraEntryForm extends Form {
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			//parent::__construct($name);
			parent::__construct('contraentry');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationContraEntry();');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->postedValues = $postedValues;
		
			date_default_timezone_set("Asia/Dhaka");
			$this->session 	= new SessionContainer('post_supply');
			$businessDate	= $this->session->businessdate;
			$fromDate 		= date('d-m-Y');			
			$bMonth 		= date("m",strtotime($businessDate));	
		
			$this->add(array(
				'name' => 'COMPANY_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'COMPANY_ID',
					'onchange' => '',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',	
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCompanyForSelect(),					
				),
			));
			$this->add(array(
				'name' => 'BRANCH_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'BRANCH_ID',
					'onchange' => 'blankAllFields()',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',	
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
				'name' => 'HIDDEN_BUSINESS_DATE',
				'type' => 'hidden',
				'required'	=> true,
				'attributes' => array(
					'id' => 'HIDDEN_BUSINESS_DATE',
					'size' => '10',
					'readonly' => '',
					'class' => 'FormDateTypeInput',
					'style' => '',								
					'value' => $this->getFirstBusinessDate(),
					'onchange'=> '',
				),
			));
			$this->add(array(
				'name' => 'TRANSACTION_DATE',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'TRANSACTION_DATE2',
					'size' => '10',
					'readonly' => '',
					'class' => 'FormDateTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',								
					'value' => date('d-m-Y',strtotime($businessDate)),
					'onchange'=> 'checkCalender(this.value,"'.date('d-m-Y',strtotime($businessDate)).'")',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Submit',
					'id' => 'insertContra',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
		
		public function getCompanyForSelect() {
			$companySql 	= 'SELECT * FROM c_company';
			$statement 		= $this->dbAdapter->query($companySql);
			$companyData    = $statement->execute();
			$selectData 	= array();
			$selected = '';
			$portfolioTypeList = array();
			foreach ($companyData as $selectOption) {
				if($selectOption['COMPANY_ID'] == 1) {
					$selected = "selected = 'selected'";
					$portfolioTypeList[$selectOption['COMPANY_ID']] = $selectOption['COMPANY_NAME'];
				}
			}
			return $portfolioTypeList;
		}
		public function getCompBranchListSelect() {
			$portTypeSql = "SELECT BRANCH_ID,BRANCH_NAME FROM c_branch WHERE ACTIVE_DEACTIVE = 'y' ORDER BY BRANCH_ID ASC";
			$statement = $this->dbAdapter->query($portTypeSql);
			$portTypeData    = $statement->execute();
			
			$selectData = array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['BRANCH_ID']] = $selectOption['BRANCH_NAME'];
			}
			return $selectData;
		}
		public function getFirstBusinessDate() {
			$portTypeSql 	= "SELECT BUSINESS_DATE FIRST_BUSINESS_DATE FROM l_business_date";
			$statement 		= $this->dbAdapter->query($portTypeSql);
			$portTypeData   = $statement->execute();
			$selectData 	= array();
			foreach ($portTypeData as $selectOption) {
				$fisrtBusinessDate 	= $selectOption['FIRST_BUSINESS_DATE'];
			}
			return $fisrtBusinessDate;
		}
	}
?>	