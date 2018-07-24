<?php
	namespace GlobalSetting\Form;
	
	use Zend\Form\Form;	
	use Zend\InputFilter;
	use Zend\Form\Element;
	use Zend\Filter\File\Rename;
	
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	class AccountDetailsForm extends Form {
		protected $accountDetailsTable;
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			parent::__construct('accountDetails');
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return addAccountInformation()');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			
			$this->add(array(
				'name' => 'ACCOUNT_DETAILS_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'COMPANY_ID',
				'type' => 'Select',
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCompanyForSelect(),
				),
				'attributes' => array(
					'id' => 'COMPANY_ID',
					'style'=> "padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;"
				),
			));
			$this->add(array(
				'name' => 'ORG_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'ORG_ID',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(					
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getOrgForSelect(),
				),
			));
			$this->add(array(
				'name' => 'PORTFOLIO_TYPE_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'PORTFOLIO_TYPE_ID',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					
					'empty_option'  => '--- please choose ---',
					//'value_options' => $this->getPortfolioTypeForSelect(),
				),
			));
			$this->add(array(
				'name' => 'ORG_BRANCH_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'orgBranchID',
					'value' => (empty($this->postedValues['ORG_BRANCH_ID'])) ? "" : $this->postedValues['ORG_BRANCH_ID'],
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => (empty($this->postedValues['ORG_ID'])) ? array() : $this->getOrgBranch($this->postedValues['ORG_ID']),
				),
			));
			$this->add(array(
				'name' => 'ACCOUNT_TYPE_ID',
				'type' => 'Select',
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getAcctypeForSelect(),
				),
				'attributes' => array(
					'id' => 'ACCOUNT_TYPE_ID',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));			
			$this->add(array(
				'name' => 'ACCOUNT_NAME',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ACCOUNT_NAME',
					'maxlength'=> '30',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'input account name',
				),
			));
			$this->add(array(
				'name' => 'ACCOUNT_NO',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ACCOUNT_NO',
					'maxlength'=> '20',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'input account number here',
				),
			));
			$this->add(array(
				'name' => 'INTEREST_RATE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'INTEREST_RATE',
					'maxlength'=> '5',
					'onkeyup' => 'removeChar(this)',
					'style' => 'width:50px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'size' => '20',
					'placeholder' => 'rate',
				),
			));	
			$this->add(array(
				'name' => 'ACTIVE_DEACTIVE',
				'type' => 'radio',
				'options' => array(
				'value_options' => array(
					   'y'  => 'Active', 'n'  => 'Deactive',
					  ),
				),
				'attributes' => array(
					'id' => 'ACTIVE_DEACTIVE',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			 ));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
		
		public function getCompanyForSelect() {
			$companySql		= "SELECT * FROM c_company WHERE ACTIVE_DEACTIVE = 'y' ORDER BY COMPANY_NAME ASC";
			$statement 		= $this->dbAdapter->query($companySql);
			$orgtypeData	= $statement->execute();
			
			$selectData = array();
			foreach ($orgtypeData as $selectOption) {
				$selectData[$selectOption['COMPANY_ID']] = $selectOption['COMPANY_NAME'];
			}
			return $selectData;
		}
		
		public function getAcctypeForSelect() {
			$accTypeSql 	= 'SELECT * FROM gs_account_type ORDER BY ACCOUNT_TYPE ASC';
			$statement 		= $this->dbAdapter->query($accTypeSql);
			$acctypeData    = $statement->execute();
			
			$selectData = array();
			foreach ($acctypeData as $selectOption) {
				$selectData[$selectOption['ACCOUNT_TYPE_ID']] = $selectOption['ACCOUNT_TYPE'];
			}
			return $selectData;
		}
		
		public function getOrgBranch($mktOrgId) {
			$orgBranchSql = '
								SELECT 
										ORG_BRANCH_ID, 
										BRANCH_NAME 
								FROM 
										gs_org_branch
										'.((empty($mktOrgId)) ? "" : ' 
								WHERE ORG_ID ='. $mktOrgId)
							;
			$statement 		= $this->dbAdapter->query($orgBranchSql);
			$orgBranchData	= $statement->execute();
			
			$selectData = array();
			foreach ($orgBranchData as $selectOption) {
				$selectData[$selectOption['ORG_BRANCH_ID']] = $selectOption['BRANCH_NAME'];
			}
			return $selectData;
		}
		
		public function getOrgForSelect() {
			$mmOrgSql = "
							SELECT 
								ORG_ID,
								ORG_NAME 
							FROM 
								gs_money_mkt_org,
								gs_org_type 
							WHERE 
								gs_org_type.ORG_TYPE_ID = gs_money_mkt_org.ORG_TYPE_ID
						";
			$statement 	= $this->dbAdapter->query($mmOrgSql);
			$mmOrgData	= $statement->execute();
			
			$selectData = array();
			foreach ($mmOrgData as $selectOption) {
				$selectData[$selectOption['ORG_ID']] = $selectOption['ORG_NAME'];
			}
			return $selectData;
		}
	}
?>	