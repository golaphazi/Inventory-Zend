<?php
	namespace HumanResource\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeeSpouseInfoTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function saveEmployeeSpouseInfo(EmployeeSpouseInfo $employeeSpouseInfo) {
			//return true; echo "<pre>"; print_r($employeeSpouseInfo); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("d-M-Y", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$id 	= (int) $employeeSpouseInfo->EMPLOYEE_SPOUSE_ID;
			$data 	= array(
				'EMPLOYEE_ID' 		=> $employeeSpouseInfo->EMPLOYEE_ID,	
				'SPOUSE_NAME' 		=> $employeeSpouseInfo->SPOUSE_NAME,
				'OCCUPATION_ID' 	=> $employeeSpouseInfo->OCCUPATION_ID,
				'SPOUSE_ADDRESS' 	=> $employeeSpouseInfo->SPOUSE_ADDRESS,
				'BUSINESS_DATE' 	=> $businessDate,
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
			);
			
			if($id == 0) {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;	
				}
			} else {
				//echo "<pre>"; print_r($data); die();
				$updateEmployeeSpouseSql   = "
												UPDATE
														hrms_employee_spouse_info
												SET
														SPOUSE_NAME 		= '".$employeeSpouseInfo->SPOUSE_NAME."',	
														OCCUPATION_ID 		= '".$employeeSpouseInfo->OCCUPATION_ID."',
														SPOUSE_ADDRESS 		= '".$employeeSpouseInfo->SPOUSE_ADDRESS."',
														BUSINESS_DATE 		= '".$businessDate."',
														RECORD_DATE 		= '".$recDate."',
														OPERATE_BY 			= ".$userId."
												WHERE
														EMPLOYEE_ID			= ".$id."
				";
				$updateEmployeeSpouseStatement = $this->tableGateway->getAdapter()->createStatement($updateEmployeeSpouseSql);
				$updateEmployeeSpouseStatement->prepare();
				if($updateEmployeeSpouseStatement->execute()) {
					return true;
				} else {
					return false;	
				}
			}
		}
	}
?>	