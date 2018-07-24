<?php
	namespace CompanyInformation\Model;
		
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Session\Container as SessionContainer;
	
	class CompanyTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll() {
			$resultSet = $this->tableGateway->select(function(Select $select){
							//$select->where("ACTIVE_DEACTIVE='y'");
							$select->order('COMPANY_NAME ASC');
						 });
			return $resultSet;
		}
		
		public function getCompany($id) {
			$id 		= (int) $id;
			$rowSet 	= $this->tableGateway->select(array('COMPANY_ID' => $id));
			$row 		= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function companyExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row 	= $rowSet->current();
			return $row;
		}
		
		public function saveCompany(Company $company) {
			$data = array(
				'COMPANY_NAME' 		=> $company->COMPANY_NAME,
				'COMPANY_CODE' 		=> $company->COMPANY_CODE,
				'ADDRESS' 			=> $company->ADDRESS,
				'PHONE' 			=> $company->PHONE,
				'FAX' 				=> $company->FAX,
				'EMAIL' 			=> $company->EMAIL,
				'WEB' 				=> $company->WEB,
				'ACTIVE_DEACTIVE' 	=> $company->ACTIVE_DEACTIVE,
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'COMPANY_NAME' 		=> $company->COMPANY_NAME,
			);
			$id 	= (int) $company->COMPANY_ID;
			
			if($id == 0) {
				if($this->companyExist($existCheckData)) {
					throw new \Exception("Company ".$company->COMPANY_NAME." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
						return true;
					} else {
						return false;	
					}
				}
			} else {
				if($this->getCompany($id)) {
					$existingCompanyId	= '';
					$companyExist		= '';
					$companyExist 		= $this->companyExist($existCheckData);
					$existingCompanyId 	=  $companyExist->COMPANY_ID;
					if((!empty($companyExist)) && ($id!=$existingCompanyId)) {
						throw new \Exception("Company ".$company->COMPANY_NAME." already exist!");
					} else {
						if($this->tableGateway->update($data,array('COMPANY_ID' => $id))) {
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
		
		public function activeDeactiveCompany($companyId,$ststus) {
			$data	= array();
			$data = array(
				'ACTIVE_DEACTIVE' 	=> $ststus,
			);
			if($this->tableGateway->update($data,array('COMPANY_ID' => $companyId))) {
				return true;	
			} else {
				throw new \Exception("Sorry! There is problem during active deactivate company.");
			}
		}
	}
?>