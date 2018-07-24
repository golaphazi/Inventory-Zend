<?php
	namespace HumanResource\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeeContactInfoTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function getEmployeeContactInfo($id) {
			$getEmployeeDetailsSql	= "
										SELECT
												CONTACTINFO.EMAIL_ADDRESS               AS EMAIL_ADDRESS
										FROM
												hrms_employee_personal_info             PERSONALINFO,
												hrms_employee_contact_info              CONTACTINFO
										WHERE
												PERSONALINFO.EMPLOYEE_ID            = ".$id."
										AND 	PERSONALINFO.EMPLOYEE_ID            = CONTACTINFO.EMPLOYEE_ID
			";
			$employeeDetailsStatement	= $this->tableGateway->getAdapter()->createStatement($getEmployeeDetailsSql);
			$employeeDetailsStatement->prepare();
			$employeeDetailsResult 		= $employeeDetailsStatement->execute();
			if ($employeeDetailsResult instanceof ResultInterface && $employeeDetailsResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($employeeDetailsResult);
			}
			return $resultSet;
		}
		
		public function saveEmployeeContactInfo(EmployeeContactInfo $employeeContactInfo) {
			//return true; echo "<pre>"; print_r($employeeContactInfo); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("d-M-Y", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$id 	= (int) $employeeContactInfo->EMPLOYEE_CONTACT_ID;
			$data 	= array(
				'EMPLOYEE_ID' 				=> $employeeContactInfo->EMPLOYEE_ID,	
				'CONTACT_ADDRESS_FIRST' 	=> $employeeContactInfo->CONTACT_ADDRESS_FIRST,
				'CONTACT_ADDRESS_SECOND' 	=> $employeeContactInfo->CONTACT_ADDRESS_SECOND,
				'TELEPHONE' 				=> $employeeContactInfo->TELEPHONE,
				'EMAIL_ADDRESS' 			=> $employeeContactInfo->EMAIL_ADDRESS,
				'BUSINESS_DATE' 			=> $businessDate,
				'RECORD_DATE' 				=> $recDate,
				'OPERATE_BY' 				=> $userId,
			);
			
			if($id == 0) {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;	
				}
			} else {
				//echo "<pre>"; print_r($data); die();
				$updateEmployeeContactSql   = "
												UPDATE
														hrms_employee_contact_info
												SET
														CONTACT_ADDRESS_FIRST 	= '".$employeeContactInfo->CONTACT_ADDRESS_FIRST."',	
														CONTACT_ADDRESS_SECOND 	= '".$employeeContactInfo->CONTACT_ADDRESS_SECOND."',
														TELEPHONE 				= '".$employeeContactInfo->TELEPHONE."',
														EMAIL_ADDRESS 			= '".$employeeContactInfo->EMAIL_ADDRESS."',
														BUSINESS_DATE 			= '".$businessDate."',
														RECORD_DATE 			= '".$recDate."',
														OPERATE_BY 				= ".$userId."
												WHERE
														EMPLOYEE_ID				= ".$id."
				";
				$updateEmployeeContactStatement = $this->tableGateway->getAdapter()->createStatement($updateEmployeeContactSql);
				$updateEmployeeContactStatement->prepare();
				if($updateEmployeeContactStatement->execute()) {
					return true;
				} else {
					return false;	
				}
			}
		}
	}
?>	