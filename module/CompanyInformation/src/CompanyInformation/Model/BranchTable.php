<?php
	namespace CompanyInformation\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	class BranchTable {
		protected $tableGateway;
		protected $dbAdapter;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll() {
			$companyWiseBranchSql = "	
										SELECT 
												COMP.COMPANY_ID, 
												COMP.COMPANY_NAME,
												BRAN.BRANCH_ID,
												BRAN.BRANCH_NAME,
												BRAN.BRANCH_CODE,
												BRAN.ADDRESS,
												BRAN.PHONE,
												BRAN.FAX,
												BRAN.EMAIL,
												BRAN.WEB,
												BRAN.ACTIVE_DEACTIVE
										FROM 
												c_company COMP,
												c_branch BRAN
										WHERE
												BRAN.COMPANY_ID			= COMP.COMPANY_ID
										AND  	COMP.ACTIVE_DEACTIVE	= 'y'	
										ORDER BY 
												COMP.COMPANY_NAME ASC,
												BRAN.BRANCH_NAME ASC
									";
			$companyWiseBranch 			= $this->tableGateway->getAdapter()->createStatement($companyWiseBranchSql);
			$companyWiseBranch->prepare();
			$companyWiseBranchResult 	= $companyWiseBranch->execute();
			
			if ($companyWiseBranchResult instanceof ResultInterface && $companyWiseBranchResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($companyWiseBranchResult);
			}
			return $resultSet;
		}
		
		public function getBranch($id) {
			$id 		= (int) $id;
			$rowSet 	= $this->tableGateway->select(array('BRANCH_ID' => $id));
			$row 		= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function branchExist($existCheckData) {
			$rowSet 	= $this->tableGateway->select($existCheckData);
			$row 		= $rowSet->current();
			return $row;
		}
		
		public function saveBranch(Branch $branch) {
			$data = array(
				'COMPANY_ID' 		=> $branch->COMPANY_ID,
				'BRANCH_NAME' 		=> $branch->BRANCH_NAME,
				'BRANCH_CODE' 		=> $branch->BRANCH_CODE,
				'ADDRESS' 			=> $branch->ADDRESS,
				'PHONE' 			=> $branch->PHONE,
				'FAX' 				=> $branch->FAX,
				'EMAIL' 			=> $branch->EMAIL,
				'WEB' 				=> $branch->WEB,
				'ACTIVE_DEACTIVE' 	=> $branch->ACTIVE_DEACTIVE,
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'COMPANY_ID' 	=> $branch->COMPANY_ID,
				'BRANCH_NAME' 	=> $branch->BRANCH_NAME,
			);
			$id = (int) $branch->BRANCH_ID;
			
			if($id == 0) {
				if($this->branchExist($existCheckData)) {
					throw new \Exception("Branch ".$branch->BRANCH_NAME." already exist!");
				} else {
					$this->tableGateway->insert($data);
				}
			} else {
				if($this->getBranch($id)) {
					$existingBranchId	= '';
					$branchExist		= '';
					$branchExist 		= $this->branchExist($existCheckData);
					$existingBranchId 	=  $branchExist->BRANCH_ID;
					if((!empty($branchExist)) && ($id!=$existingBranchId)) {
						throw new \Exception("Branch ".$branch->BRANCH_NAME." already exist!");
					} else {
						$this->tableGateway->update($data,array('BRANCH_ID' => $id));
					}
				} else {
					throw new \Exception("ID $id does not exist!");
				}
			}
		}		
		
		public function deleteBranch($id) {
			$this->tableGateway->delete(array('BRANCH_ID' => $id));
		}
		
		public function getBranchForSelect() {
			$getTblDataSql   = "SELECT * FROM c_branch WHERE COMPANY_ID	= 1 ORDER BY BRANCH_NAME ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function getBranchInfoforPortStatement($portfolioCode) {			
			// 21 means compnay name that is Beta One Investment Limited
			$compBranchSql 	= "
								SELECT 
										c_company.COMPANY_NAME,
										c_branch.BRANCH_NAME,
										c_branch.BRANCH_CODE,
										c_branch.ADDRESS,
										c_branch.PHONE,
										c_branch.FAX,
										c_branch.EMAIL,
										c_branch.WEB
								FROM 
										c_company, 
										c_branch
								WHERE 
										c_company.COMPANY_ID 		= c_branch.COMPANY_ID
								AND 	c_branch.COMPANY_ID 		= 1
								AND		c_branch.BRANCH_ID			= 1
								AND 	c_branch.ACTIVE_DEACTIVE 	= 'y'
							";
			$chkBDateExistStatement = $this->tableGateway->getAdapter()->createStatement($compBranchSql);
			$chkBDateExistStatement->prepare();
			$chkBDateExistResult = $chkBDateExistStatement->execute();
			if ($chkBDateExistResult instanceof ResultInterface && $chkBDateExistResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($chkBDateExistResult);
			}
			return $resultSet;
		}
		
		public function getBranchInfoFinancialStatement($branchID) {
			$compBranchSql 	= "
								SELECT 
										c_company.COMPANY_NAME,
										c_branch.BRANCH_NAME,
										c_branch.BRANCH_CODE,
										c_company.ADDRESS,
										c_company.PHONE,
										c_company.FAX,
										c_company.EMAIL,
										c_company.WEB
								FROM 
										c_company, 
										c_branch
								WHERE 
										c_company.COMPANY_ID 		= c_branch.COMPANY_ID
								AND 	c_branch.ACTIVE_DEACTIVE 	= 'y'
								AND		c_branch.BRANCH_ID			='".$branchID."'
			";
			$chkBDateExistStatement = $this->tableGateway->getAdapter()->createStatement($compBranchSql);
			$chkBDateExistStatement->prepare();
			$chkBDateExistResult = $chkBDateExistStatement->execute();
			if ($chkBDateExistResult instanceof ResultInterface && $chkBDateExistResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($chkBDateExistResult);
			}
			return $resultSet;
		}
		
		public function activeDeactiveBranch($companyId,$branchId,$ststus) {
			$data	= array();
			$data = array(
				'ACTIVE_DEACTIVE' 	=> $ststus,
			);
			if($this->tableGateway->update($data,array('COMPANY_ID' => $companyId,'BRANCH_ID' => $branchId))) {
				return true;	
			} else {
				throw new \Exception("Sorry! There is problem during active deactivate branch.");
			}
		}
		public function getBranchList($comapnyID) {
			$getTblDataSql   = "SELECT * FROM c_branch WHERE COMPANY_ID	= {$comapnyID} AND ACTIVE_DEACTIVE = 'y' ORDER BY BRANCH_ID ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
	}
?>	