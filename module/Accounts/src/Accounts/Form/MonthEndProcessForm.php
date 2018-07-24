<?php
	namespace Accounts\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class MonthEndProcessForm extends Form {
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			//parent::__construct($name);
			parent::__construct('monthendprocess');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationPaymentEntry();');
			
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
					'onchange' => 'blankAllFields()',
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
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCompBranchListSelect(),
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
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',								
					'value' => date('d-m-Y',strtotime($businessDate)),
					'onchange'=> 'checkCalender(this.value,"'.date('d-m-Y',strtotime($businessDate)).'")',
				),
			));
			$this->add(array(
				'name' => 'INVOICE_NO',
				'type' => 'Text',
				'required'	=> false,
				'attributes' => array(
					'id' => 'INVOICE_NO',
					//'class' => 'FormNumberTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;width:120px;',
					'value' => '',
				),
			));
			$this->add(array(
				'name' => 'MONEY_RECEIPT_NO',
				'type' => 'Text',
				'required'	=> false,
				'attributes' => array(
					'id' => 'MONEY_RECEIPT_NO',
					'class' => 'FormNumberTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;width:120px;',								
					'value' => '',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Submit',
					'id' => 'insertPayment',					
				),
			));
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
		public function getPortfolioTypeForSelect() {
			$portTypeSql 	= 'SELECT * FROM LS_PORTFOLIO_TYPE';
			$statement 		= $this->dbAdapter->query($portTypeSql);
			$portTypeData	= $statement->execute();
			$selectData 	= array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['PORTFOLIO_TYPE_ID']] = $selectOption['PORTFOLIO_TYPE'];
			}
			return $selectData;
		}
		
		public function getMasterTCodeForDAInvst() {
			$portTypeSql 	= 'SELECT PORTFOLIO_CODE FROM IS_INVESTOR_DETAILS WHERE INVESTOR_DETAILS_ID=141';
			$statement 		= $this->dbAdapter->query($portTypeSql);
			$portTypeData   = $statement->execute();
			$selectData 	= array();
			foreach ($portTypeData as $selectOption) {
				$tradeCode 	= $selectOption['PORTFOLIO_CODE'];
			}
			return $tradeCode;
		}
		
		public function getPortfolioInfo() {						
			$portTypeSql = 'SELECT 
									INVESTOR_DETAILS_ID,
								   	PORTFOLIO_CODE,
								   	CASE WHEN INVD.INSTITUTION_NAME IS NULL THEN
										  INVD.INVESTOR_NAME
									  ELSE
										  INVD.INSTITUTION_NAME
									  END AS INVESTOR_NAME
							  FROM 
							  		IS_INVESTOR_DETAILS INVD
							  WHERE 
							  		INVESTOR_DETAILS_ID NOT IN (141)';
			$statement 		= $this->dbAdapter->query($portTypeSql);
			$portfolioData	= $statement->execute();
			$selectData 	= array();
			$investor 		= '';
			foreach ($portfolioData as $selectOption) {
				$investorDetailsID 	= $selectOption['INVESTOR_DETAILS_ID'];
				$portfolioCode 		= $selectOption['PORTFOLIO_CODE'];
				$investorName 		= $selectOption['INVESTOR_NAME'];
				$selectData[$selectOption['PORTFOLIO_CODE']] 	= $investorName.' - Acc Code: [ '.$selectOption['PORTFOLIO_CODE'].' ]';
			}
			return $selectData;
		}
		public function getCompanyForSelect() {
			$companySql 	= 'SELECT * FROM C_COMPANY';
			$statement 		= $this->dbAdapter->query($companySql);
			$companyData    = $statement->execute();
			$selectData 	= array();
			$selected = '';
			$portfolioTypeList = array();
			foreach ($companyData as $selectOption) {
				//$selectData[$selectOption['COMPANY_ID']] = $selectOption['COMPANY_NAME'];
				if($selectOption['COMPANY_ID'] == 21) {
					$selected = "selected = 'selected'";
					$portfolioTypeList[$selectOption['COMPANY_ID']] = $selectOption['COMPANY_NAME'];
				}
				//$portfolioTypeList[$selectOption['COMPANY_ID']] = $selectOption['COMPANY_NAME'];
				//$portfolioTypeList[] = "<option value='".$selectOption['COMPANY_ID']."' {$selected}>".$selectOption['COMPANY_NAME']."</option>";
			}
			return $portfolioTypeList;
		}
	}
?>	