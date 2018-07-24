<?php
	namespace HumanResource\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeePostingInfoTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function saveEmployeePostingInfo(EmployeePostingInfo $employeePostingInfo) {
			//return true; echo "<pre>"; print_r($employeePostingInfo); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("d-M-Y", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$id 	= (int) $employeePostingInfo->EMPLOYEE_POSTING_ID;
			$data 	= array(
				'EMPLOYEE_ID' 		=> $employeePostingInfo->EMPLOYEE_ID,	
				'DESIGNATION_ID' 	=> $employeePostingInfo->DESIGNATION_ID,
				'BRANCH_ID' 		=> $employeePostingInfo->BRANCH_ID,
				'DIVISION_NAME' 	=> $employeePostingInfo->DIVISION_NAME,
				'BUSINESS_DATE' 	=> $businessDate,
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
				'START_DATE' 		=> $businessDate,
			);
			
			if($id == 0) {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;	
				}
			} else {
				$data1['END_DATE'] 		= $businessDate;
				if($this->tableGateway->update($data1,array('EMPLOYEE_POSTING_ID' => $id))){
					if($this->tableGateway->insert($data)) {
						return true;
					} else {
						return false;	
					}
				} else {
					return false;	
				}
			}
		}
	}
?>	