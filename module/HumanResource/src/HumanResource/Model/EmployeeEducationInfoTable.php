<?php
	namespace HumanResource\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeeEducationInfoTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function saveEmployeeEducationInfo(EmployeeEducationInfo $employeeEducationInfo) {
			//return true; echo "<pre>"; print_r($employeeEducationInfo); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("d-M-Y", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$id 	= (int) $employeeEducationInfo->EMPLOYEE_EDUCATION_ID;
			$data 	= array(
				'EMPLOYEE_ID' 			=> $employeeEducationInfo->EMPLOYEE_ID,	
				'LAST_CERTIFICATE' 		=> $employeeEducationInfo->LAST_CERTIFICATE,
				'INSTITUTE_NAME' 		=> $employeeEducationInfo->INSTITUTE_NAME,
				'PASSING_YEAR' 			=> $employeeEducationInfo->PASSING_YEAR,
				'MARKS_OBTAIN' 			=> $employeeEducationInfo->MARKS_OBTAIN,
				'CLASS_DIVISION' 		=> $employeeEducationInfo->CLASS_DIVISION,
				'EDU_PHOTO' 			=> $employeeEducationInfo->EDU_PHOTO,
				'BUSINESS_DATE' 		=> $businessDate,
				'RECORD_DATE' 			=> $recDate,
				'OPERATE_BY' 			=> $userId,
			);
			
			if($id == 0) {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;	
				}
			} else {
				//echo "<pre>"; print_r($data); die();
				$updateEmployeeEducationSql   = "
												UPDATE
														hrms_employee_education_info
												SET
														LAST_CERTIFICATE 	= '".$employeeEducationInfo->LAST_CERTIFICATE."',	
														INSTITUTE_NAME 		= '".$employeeEducationInfo->INSTITUTE_NAME."',
														PASSING_YEAR 		= '".$employeeEducationInfo->PASSING_YEAR."',
														MARKS_OBTAIN 		= '".$employeeEducationInfo->MARKS_OBTAIN."',
														CLASS_DIVISION 		= '".$employeeEducationInfo->CLASS_DIVISION."',
														EDU_PHOTO	 		= '".$employeeEducationInfo->EDU_PHOTO."',
														BUSINESS_DATE 		= '".$businessDate."',
														RECORD_DATE 		= '".$recDate."',
														OPERATE_BY 			= ".$userId."
												WHERE
														EMPLOYEE_ID			= ".$id."
				";
				$updateEmployeeEducationStatement = $this->tableGateway->getAdapter()->createStatement($updateEmployeeEducationSql);
				$updateEmployeeEducationStatement->prepare();
				if($updateEmployeeEducationStatement->execute()) {
					return true;
				} else {
					return false;	
				}
			}
		}
	}
?>	