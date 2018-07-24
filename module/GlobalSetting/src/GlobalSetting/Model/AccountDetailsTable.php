<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class AccountDetailsTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('gs_account_details');
				$select->join('gs_org_branch','gs_account_details.ORG_BRANCH_ID=gs_org_branch.ORG_BRANCH_ID','BRANCH_NAME');
				$select->join('gs_account_type','gs_account_details.ACCOUNT_TYPE_ID=gs_account_type.ACCOUNT_TYPE_ID','ACCOUNT_TYPE');
				$select->join('c_company','gs_account_details.COMPANY_ID=c_company.COMPANY_ID','COMPANY_NAME');
				$select->order('c_company.COMPANY_NAME, gs_account_type.ACCOUNT_TYPE, gs_org_branch.BRANCH_NAME, gs_account_details.ACCOUNT_NAME ASC');
				$select->order('ACCOUNT_NAME ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new AccountDetails());
				// create a new pagination adapter object
				$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				return $paginator;
			}
			
			if (null === $select)
			$select	= new Select();
			$select->from($this->table);
			$resultSet = $this->selectWith($select);
			$resultSet->buffer();
			return $resultSet;
		}
		
		public function getAccountDetails($id) {
			$id 	= (int) $id;
			$rowSet = $this->tableGateway->select(array('ACCOUNT_DETAILS_ID' => $id));
			$row 	= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function accountDetailsExist($existCheckData) {
			$rowSet	= $this->tableGateway->select($existCheckData);
			$row 	= $rowSet->current();
			return $row;
		}
		
		public function saveAccountDetails(AccountDetails $accountDetails) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$recDate 		= $this->session->recdate;
			$userId 		= $this->session->userid;
			
			//Receivable Chart of Account Generate Start By Akhand
			$maxReceiveableCOACode = '';
			$selectMaxRcvCOA = "
									SELECT 
											COALESCE(MAX(substr(COA_CODE,1,9)),302003000)+1  AS MAX_RECEIVABLE_COA_CODE
									FROM
											gs_coa 		
									WHERE
											substr(COA_CODE,1,9) BETWEEN '302003000' AND '302003999'
								";
			$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
			$selectMaxRcvCOAStatement->prepare();
			$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
			
			if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectMaxRcvCOAResult);
			}
			
			foreach($resultSet as $resultMaxRcvCOA) {
				$maxReceiveableCOACode	= $resultMaxRcvCOA->MAX_RECEIVABLE_COA_CODE;
			}
			//echo $maxReceiveableCOACode;die();
			
			// Get Bank Branch Name Start
			$bBranchId = $accountDetails->ORG_BRANCH_ID;
			$getBankBranchSql 		= "	SELECT 
											  gs_money_mkt_org.ORG_NAME,
											  gs_org_branch.BRANCH_NAME
											  
									FROM 
											  gs_org_branch,
											  gs_money_mkt_org
									WHERE
											  gs_org_branch.ORG_BRANCH_ID = ".$bBranchId."  
									AND       gs_money_mkt_org.ORG_ID = gs_org_branch.ORG_ID
									";
			$getBankBranch			= $this->tableGateway->getAdapter()->createStatement($getBankBranchSql);
			$getBankBranch->prepare();
			$getBankBranchResult 		= $getBankBranch->execute();
			if ($getBankBranchResult instanceof ResultInterface && $getBankBranchResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($getBankBranchResult);
			}
			foreach($resultSet as $bankBranchNameS){
				$orgName		 		= $bankBranchNameS->ORG_NAME;
				$orgBranch		 		= $bankBranchNameS->BRANCH_NAME;
			}
			// Get Bank Branch Name End
			
			// Get Account Type Name Start
			$accTypeId = $accountDetails->ACCOUNT_TYPE_ID;
			$getAccTypeSql 		= "SELECT ACCOUNT_TYPE FROM gs_account_type WHERE ACCOUNT_TYPE_ID = ".$accTypeId."";
			$getAccType			= $this->tableGateway->getAdapter()->createStatement($getAccTypeSql);
			$getAccType->prepare();
			$getAccTypeResult 		= $getAccType->execute();
			if ($getAccTypeResult instanceof ResultInterface && $getAccTypeResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($getAccTypeResult);
			}
			foreach($resultSet as $accTypeNames){
				$accountTypeName = $accTypeNames->ACCOUNT_TYPE;
			}
			// Get Account Type Name End
			
			$accNo = $accountDetails->ACCOUNT_NO;
			
			$RECEIVABLE_COA_CODE 	= $maxReceiveableCOACode;
			$RECEIVABLE_COA_NAME 	= "Interest Receivable from - ".$orgName."-".$orgBranch."-".$accountTypeName."#".$accNo;
			$RECEIVABLE_AUTO_COA 	= 'y';
			
			$marketWiseDivCOASql = "	
									SELECT 
											CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
											C.COA_ID AS RECEIVABLE_COA_ID,
											C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '302003000'
									ORDER BY 
											C.RGT		
								";
			$marketWiseDivCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseDivCOASql);
			$marketWiseDivCOA->prepare();
			$marketWiseDivCOAResult = $marketWiseDivCOA->execute();
			if ($marketWiseDivCOAResult instanceof ResultInterface && $marketWiseDivCOAResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($marketWiseDivCOAResult);
			}
			foreach($resultSet as $resultMaxDivCOA) {
				$RECEIVABLE_COMPANY_ID		= $resultMaxDivCOA->RECEIVABLE_COMPANY_ID;
				$RECEIVABLE_COA_ID			= $resultMaxDivCOA->RECEIVABLE_COA_ID;
				$RECEIVABLE_CASH_FLOW_HEAD	= $resultMaxDivCOA->RECEIVABLE_CASH_FLOW_HEAD;
			}
			//Receivable Chart of Account Generate End By Akhand
			
			//General Account Details Chart of Account Generate Start
			$maxAccDetailsCOACode 	= '';
			$selectMaxAccCOA 		= "
										SELECT 
												COALESCE(MAX(substr(COA_CODE,1,9)),304001000)+1  AS MAX_ACC_DETAILS_COA_CODE
										FROM
												gs_coa 		
										WHERE
												substr(COA_CODE,1,9) BETWEEN '304001000' AND '304001999'
									";
			$selectMaxAccCOAtatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxAccCOA);
			$selectMaxAccCOAtatement->prepare();
			$selectMaxAccCOAResult 	= $selectMaxAccCOAtatement->execute();
			
			if ($selectMaxAccCOAResult instanceof ResultInterface && $selectMaxAccCOAResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($selectMaxAccCOAResult);
			}
			
			foreach($resultSet as $resultMaxRcvCOA) {
				$maxAccDetailsCOACode	= $resultMaxRcvCOA->MAX_ACC_DETAILS_COA_CODE;
			}
			$accNo = $accountDetails->ACCOUNT_NO;
			
			$ACC_DETAILS_COA_CODE 	= $maxAccDetailsCOACode;
			$ACC_DETAILS_COA_NAME 	= $orgName."-".$orgBranch."-".$accountTypeName."#".$accNo;
			$ACC_DETAILS_AUTO_COA 	= 'y';
			
			$marketWiseAccCOASql = "	
									SELECT 
											CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID,
											C.COA_ID AS RECEIVABLE_COA_ID,
											C.CASH_FLOW_HEAD AS RECEIVABLE_CASH_FLOW_HEAD
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '304001000'
									ORDER BY 
											C.RGT		
			";
			$marketWiseAccCOA		= $this->tableGateway->getAdapter()->createStatement($marketWiseAccCOASql);
			$marketWiseAccCOA->prepare();
			$marketWiseAccCOAResult = $marketWiseAccCOA->execute();
			
			if ($marketWiseAccCOAResult instanceof ResultInterface && $marketWiseAccCOAResult->isQueryResult()) {
				$resultSett 			= new ResultSet();
				$resultSett->initialize($marketWiseAccCOAResult);
			}
			
			foreach($resultSett as $resultMaxAccCOA) {
				$ACC_DETAILS_COMPANY_ID		= $resultMaxAccCOA->RECEIVABLE_COMPANY_ID;
				$ACC_DETAILS_COA_ID			= $resultMaxAccCOA->RECEIVABLE_COA_ID;
				$ACC_DETAILS_CASH_FLOW_HEAD	= $resultMaxAccCOA->RECEIVABLE_CASH_FLOW_HEAD;
			}
			//General Account Details Chart of Account Generate End By Akhand
			
			$CoaData = array();
			$CoaData = array(
									"COMPANY_ID"=>array(
															$RECEIVABLE_COMPANY_ID,
															$ACC_DETAILS_COMPANY_ID,
														),
									"PARENT_COA"=>array(
															$RECEIVABLE_COA_ID,
															$ACC_DETAILS_COA_ID,
														),
									"CASH_FLOW_HEAD"=>array(
															$RECEIVABLE_CASH_FLOW_HEAD,
															$ACC_DETAILS_CASH_FLOW_HEAD,
														),
									"COA_CODE"=>array(
															$RECEIVABLE_COA_CODE,
															$ACC_DETAILS_COA_CODE,
														),
									"COA_NAME"=>array(
															$RECEIVABLE_COA_NAME,
															$ACC_DETAILS_COA_NAME,
														),
									"AUTO_COA"=>array(
															$RECEIVABLE_AUTO_COA,
															$ACC_DETAILS_AUTO_COA,
														),
								);
			
			//echo '<pre>'; print_r($CoaData); echo '</pre>'; die();
			$data = array(
				'ORG_BRANCH_ID' 		=> $accountDetails->ORG_BRANCH_ID,
				'ACCOUNT_TYPE_ID' 		=> $accountDetails->ACCOUNT_TYPE_ID,
				'COMPANY_ID' 			=> $accountDetails->COMPANY_ID,				
				'ACCOUNT_NAME' 			=> $accountDetails->ACCOUNT_NAME,
				'ACCOUNT_NO' 			=> $accountDetails->ACCOUNT_NO,
				'INTEREST_RATE' 		=> $accountDetails->INTEREST_RATE,				
				'ACTIVE_DEACTIVE' 		=> $accountDetails->ACTIVE_DEACTIVE,
				'RECEIVABLE_COA'		=> $RECEIVABLE_COA_CODE,
				'ACCOUNT_DETAILS_COA'	=> $ACC_DETAILS_COA_CODE,
				'BUSINESS_DATE' 		=> $businessDate,
				'RECORD_DATE' 			=> $recDate,
				'OPERATE_BY' 			=> $userId,
			);
			//echo "<pre>";print_r($data);die();
			$existCheckData = array(
				'ACCOUNT_NO' => $accountDetails->ACCOUNT_NO,
			);
			$id = (int) $accountDetails->ACCOUNT_DETAILS_ID;
			if($id == 0) {
				if($this->accountDetailsExist($existCheckData)) {
					throw new \Exception("Account ".$accountDetails->ACCOUNT_NAME." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
						$accountDetailsSql 	= $this->accountDetailsExist($existCheckData);
						$accountDetailsId 	= (int) $accountDetailsSql->ACCOUNT_DETAILS_ID;
						$returnData	= array(
								"ACCOUNT_DETAILS_ID" 	=> $accountDetailsId,
								"COA_DATA" 				=> $CoaData,
						  );
						return $returnData;
					} else {
						return false;
					}	
				}
			} else {
				if($this->getaccountDetails($id)) {
					$existingAccDetId	= '';
					$accDetExist		= '';
					$accDetExist 		= $this->accountDetailsExist($existCheckData);
					$existingaccDetId 	=  $accDetExist->ACCOUNT_DETAILS_ID;
					if((!empty($accDetExist)) && ($id!=$existingaccDetId)) {
						throw new \Exception("Account ".$accountDetails->ACCOUNT_NAME." already exist!");
					} else {
						if($this->tableGateway->update($data,array('ACCOUNT_DETAILS_ID' => $id))) {
							return true;
						} else {
							return false;	
						}
					}
				} else {
					throw new \Exception("ID $id does not exist!");
				}
			}
		}
		
		public function portfolioTypeWiseAccountHead ($portfolioCode) {
			$getAccTypeSql 		= "
									 SELECT AD.ACCOUNT_DETAILS_COA AS ADCOA
									  FROM gs_account_details AD
									 WHERE AD.ACCOUNT_DETAILS_COA NOT IN
										   (SELECT AD.ACCOUNT_DETAILS_COA AS ADCOA
											  FROM IS_INVESTOR_DETAILS INV_DET,
												   LS_TEMPLATE         T,
												   LS_PORTFOLIO_TYPE   PT,
												   gs_account_details  AD
											 WHERE INV_DET.PORTFOLIO_CODE = '".$portfolioCode."'
											   AND INV_DET.TEMPLATE_ID = T.TEMPLATE_ID
											   AND T.PORTFOLIO_TYPE_ID = PT.PORTFOLIO_TYPE_ID
											   AND AD.PORTFOLIO_TYPE_ID = PT.PORTFOLIO_TYPE_ID
											   AND LOWER(AD.ACTIVE_DEACTIVE) = 'y')
								  ";
			$getAccType			= $this->tableGateway->getAdapter()->createStatement($getAccTypeSql);
			$getAccType->prepare();
			$getAccTypeResult 		= $getAccType->execute();
			if ($getAccTypeResult instanceof ResultInterface && $getAccTypeResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($getAccTypeResult);
			}
			return $resultSet;
		}
		
		public function deleteAccountDetails($id) {
			$this->tableGateway->delete(array('ACCOUNT_DETAILS_ID' => $id));
		}
		
		public function transectionStart() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
		}
		
		public function transectionEnd() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->commit();
		}
		
		public function transectionInterrupted() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->rollback();
		}
	}
?>	