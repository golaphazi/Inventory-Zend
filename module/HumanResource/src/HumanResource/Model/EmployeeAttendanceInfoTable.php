<?php
	namespace HumanResource\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeeAttendanceInfoTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function getAllEmployeeAttendanceInfo($CONTROLLER_NAME) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= date("Y-m-d", strtotime($this->session->businessdate));
			
			$CONDITION	= '';
			if($CONTROLLER_NAME == 'In') {
				$CONDITION	= "AND 	ATTENDANCEINFO.OUT_TIME IS NULL";
			} else if($CONTROLLER_NAME == 'Out') {
				$CONDITION	= "AND 	ATTENDANCEINFO.IN_TIME IS NOT NULL";	
			} else {
				$CONDITION	= '';
			}
			
			$getEmployeeDetailsSql	= "
										SELECT 
												PERSONALINFO.EMPLOYEE_ID 				AS EMPLOYEE_ID,
												PERSONALINFO.EMPLOYEE_NAME 				AS EMPLOYEE_NAME,
												PERSONALINFO.EMPLOYEE_TYPE 				AS EMPLOYEE_TYPE,
												BRANCH.BRANCH_NAME 						AS BRANCH_NAME,
												POSTINGINFO.DIVISION_NAME 				AS DIVISION_NAME,
												DESIGNATION.DESIGNATION 				AS DESIGNATION,
												ATTENDANCEINFO.IN_TIME 					AS IN_TIME,
												ATTENDANCEINFO.OUT_TIME					AS OUT_TIME,
												ATTENDANCEINFO.EMPLOYEE_ATTENDANCE_ID	AS EMPLOYEE_ATTENDANCE_ID
										FROM 
												hrms_employee_posting_info  	POSTINGINFO,
												c_branch                    	BRANCH,
												gs_designation              	DESIGNATION,
												hrms_employee_personal_info 	PERSONALINFO
										LEFT JOIN 
												hrms_employee_attendance_info ATTENDANCEINFO
										ON 		PERSONALINFO.EMPLOYEE_ID 		= ATTENDANCEINFO.EMPLOYEE_ID
										AND		ATTENDANCEINFO.ATTENDANCE_DATE	= '".$businessDate."'
										WHERE 
												PERSONALINFO.EMPLOYEE_ID 	= POSTINGINFO.EMPLOYEE_ID
										AND 	POSTINGINFO.END_DATE IS NULL
										AND 	POSTINGINFO.BRANCH_ID 		= BRANCH.BRANCH_ID
										AND 	POSTINGINFO.DESIGNATION_ID 	= DESIGNATION.DESIGNATION_ID
										{$CONDITION}
										ORDER BY 
												DESIGNATION.DESIGNATION ASC
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
		
		public function getDailyEmployeeAttendanceInfo($EMPLOYEE_ID,$START_DATE) {
			$START_DATE	= date("Y-m-d", strtotime($START_DATE));
			$getEmployeeDetailsSql	= "
										SELECT 
												PERSONALINFO.EMPLOYEE_ID 				AS EMPLOYEE_ID,
												PERSONALINFO.EMPLOYEE_NAME 				AS EMPLOYEE_NAME,
												PERSONALINFO.EMPLOYEE_TYPE 				AS EMPLOYEE_TYPE,
												BRANCH.BRANCH_NAME 						AS BRANCH_NAME,
												POSTINGINFO.DIVISION_NAME 				AS DIVISION_NAME,
												DESIGNATION.DESIGNATION 				AS DESIGNATION,
												ATTENDANCEINFO.IN_TIME 					AS IN_TIME,
												ATTENDANCEINFO.OUT_TIME					AS OUT_TIME,
												ATTENDANCEINFO.EMPLOYEE_ATTENDANCE_ID	AS EMPLOYEE_ATTENDANCE_ID,
												ATTENDANCEINFO.ATTENDANCE_DATE			AS ATTENDANCE_DATE,
												ATTENDANCEINFO.WORKING_HOUR				AS WORKING_HOUR,
												ATTENDANCEINFO.LATE_HOUR				AS LATE_HOUR,
												ATTENDANCEINFO.OVER_HOUR				AS OVER_HOUR,
												ATTENDANCEINFO.STATUS					AS STATUS
										FROM 
												hrms_employee_posting_info  	POSTINGINFO,
												c_branch                    	BRANCH,
												gs_designation              	DESIGNATION,
												hrms_employee_personal_info 	PERSONALINFO
										LEFT JOIN 
												hrms_employee_attendance_info ATTENDANCEINFO
										ON 		PERSONALINFO.EMPLOYEE_ID 		= ATTENDANCEINFO.EMPLOYEE_ID
										AND 	ATTENDANCEINFO.ATTENDANCE_DATE 	= '".$START_DATE."'
										WHERE 
												PERSONALINFO.EMPLOYEE_ID	= '".$EMPLOYEE_ID."' 	
										AND		PERSONALINFO.EMPLOYEE_ID 	= POSTINGINFO.EMPLOYEE_ID
										AND 	POSTINGINFO.BRANCH_ID 		= BRANCH.BRANCH_ID
										AND 	POSTINGINFO.DESIGNATION_ID 	= DESIGNATION.DESIGNATION_ID
										AND 	POSTINGINFO.END_DATE IS NULL
										ORDER BY 
												ATTENDANCEINFO.ATTENDANCE_DATE ASC
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
		
		public function getDailyAttendanceInfo($BRANCH_ID,$EMPLOYEE_ID,$START_DATE) {
			$COND	= '';
			if($EMPLOYEE_ID) {
				$COND	= "AND PERSONALINFO.EMPLOYEE_ID	= '".$EMPLOYEE_ID."'";	
			} else {
				$COND	= '';	
			}
			
			$BRANCH_COND 		= '';
			if($BRANCH_ID) {
				$BRANCH_COND	= "AND BRANCH.BRANCH_ID	= '".$BRANCH_ID."'";	
			} else {
				$BRANCH_COND	= '';	
			}
			
			$START_DATE	= date("Y-m-d", strtotime($START_DATE));
			$getEmployeeDetailsSql	= "
										SELECT 
												BRANCH.BRANCH_ID						AS BRANCH_ID,
												BRANCH.BRANCH_NAME						AS BRANCH_NAME,
												PERSONALINFO.EMPLOYEE_ID 				AS EMPLOYEE_ID,
												PERSONALINFO.EMPLOYEE_NAME 				AS EMPLOYEE_NAME,
												PERSONALINFO.EMPLOYEE_TYPE 				AS EMPLOYEE_TYPE,
												BRANCH.BRANCH_NAME 						AS BRANCH_NAME,
												POSTINGINFO.DIVISION_NAME 				AS DIVISION_NAME,
												DESIGNATION.DESIGNATION 				AS DESIGNATION,
												ATTENDANCEINFO.IN_TIME 					AS IN_TIME,
												ATTENDANCEINFO.OUT_TIME					AS OUT_TIME,
												ATTENDANCEINFO.EMPLOYEE_ATTENDANCE_ID	AS EMPLOYEE_ATTENDANCE_ID,
												ATTENDANCEINFO.ATTENDANCE_DATE			AS ATTENDANCE_DATE,
												ATTENDANCEINFO.WORKING_HOUR				AS WORKING_HOUR,
												ATTENDANCEINFO.LATE_HOUR				AS LATE_HOUR,
												ATTENDANCEINFO.OVER_HOUR				AS OVER_HOUR,
												ATTENDANCEINFO.STATUS					AS STATUS
										FROM 
												hrms_employee_posting_info  	POSTINGINFO,
												c_branch                    	BRANCH,
												gs_designation              	DESIGNATION,
												hrms_employee_personal_info 	PERSONALINFO
										LEFT JOIN 
												hrms_employee_attendance_info ATTENDANCEINFO
										ON 		PERSONALINFO.EMPLOYEE_ID 		= ATTENDANCEINFO.EMPLOYEE_ID
										AND 	ATTENDANCEINFO.ATTENDANCE_DATE 	= '".$START_DATE."'
										WHERE 
												PERSONALINFO.EMPLOYEE_ID 	= POSTINGINFO.EMPLOYEE_ID
										AND 	POSTINGINFO.BRANCH_ID 		= BRANCH.BRANCH_ID
										AND 	POSTINGINFO.DESIGNATION_ID 	= DESIGNATION.DESIGNATION_ID
										AND 	POSTINGINFO.END_DATE IS NULL
										{$BRANCH_COND}
										{$COND}
										ORDER BY 
												BRANCH.BRANCH_ID,
												POSTINGINFO.DIVISION_NAME,
												DESIGNATION.DESIGNATION,
												PERSONALINFO.EMPLOYEE_NAME
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
		
		public function getDailyAttendanceStatus($START_DATE,$EMPLOYEE_ID) {
			$START_DATE	= date("Y-m-d", strtotime($START_DATE));
			$getEmployeeDetailsSql	= "
										SELECT 
												ATTENDANCEINFO.EMPLOYEE_ATTENDANCE_ID	AS EMPLOYEE_ATTENDANCE_ID,
												ATTENDANCEINFO.IN_TIME 					AS IN_TIME,
												ATTENDANCEINFO.OUT_TIME					AS OUT_TIME,
												ATTENDANCEINFO.ATTENDANCE_DATE			AS ATTENDANCE_DATE,
												ATTENDANCEINFO.WORKING_HOUR				AS WORKING_HOUR,
												ATTENDANCEINFO.LATE_HOUR				AS LATE_HOUR,
												ATTENDANCEINFO.OVER_HOUR				AS OVER_HOUR,
												ATTENDANCEINFO.STATUS					AS STATUS
										FROM 
												hrms_employee_posting_info  	POSTINGINFO,
												c_branch                    	BRANCH,
												gs_designation              	DESIGNATION,
												hrms_employee_personal_info 	PERSONALINFO
										LEFT JOIN 
												hrms_employee_attendance_info ATTENDANCEINFO
										ON 		PERSONALINFO.EMPLOYEE_ID 		= ATTENDANCEINFO.EMPLOYEE_ID
										AND 	ATTENDANCEINFO.ATTENDANCE_DATE 	= '".$START_DATE."'
										AND 	ATTENDANCEINFO.EMPLOYEE_ID 		= ".$EMPLOYEE_ID."
										WHERE 
												PERSONALINFO.EMPLOYEE_ID 	= POSTINGINFO.EMPLOYEE_ID
										AND 	POSTINGINFO.EMPLOYEE_ID 	= ".$EMPLOYEE_ID."		
										AND 	POSTINGINFO.BRANCH_ID 		= BRANCH.BRANCH_ID
										AND 	POSTINGINFO.DESIGNATION_ID 	= DESIGNATION.DESIGNATION_ID
										AND 	POSTINGINFO.END_DATE IS NULL
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
		
		public function saveEmployeeAttendanceInfo(EmployeeAttendanceInfo $employeeAttendanceInfo) {
			//echo "<pre>"; print_r($employeeAttendanceInfo); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("Y-m-d", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$id 	= (int) $employeeAttendanceInfo->EMPLOYEE_ATTENDANCE_ID;
			$data 	= array(
				'EMPLOYEE_ATTENDANCE_ID' 	=> $employeeAttendanceInfo->EMPLOYEE_ATTENDANCE_ID,
				'EMPLOYEE_ID' 				=> $employeeAttendanceInfo->EMPLOYEE_ID,	
				'ATTENDANCE_DATE' 			=> date("Y-m-d", strtotime($employeeAttendanceInfo->ATTENDANCE_DATE)),
				'IN_TIME' 					=> $employeeAttendanceInfo->IN_TIME,
				'OUT_TIME' 					=> $employeeAttendanceInfo->OUT_TIME,
				'WORKING_HOUR' 				=> $employeeAttendanceInfo->WORKING_HOUR,
				'LATE_HOUR' 				=> $employeeAttendanceInfo->LATE_HOUR,
				'OVER_HOUR' 				=> $employeeAttendanceInfo->OVER_HOUR,
				'STATUS' 					=> $employeeAttendanceInfo->STATUS,
				'BUSINESS_DATE' 			=> $businessDate,
				'RECORD_DATE' 				=> $recDate,
				'OPERATE_BY' 				=> $userId,
			);
			//echo "<pre>"; print_r($data); die();
			if($id == 0) {
				if($this->tableGateway->insert($data)) {
					return true;
				} else {
					return false;	
				}
			} else {
				if($this->tableGateway->update($data,array('EMPLOYEE_ATTENDANCE_ID' => $id))){
					return true;
				} else {
					return false;	
				}
			}
		}
		
		public function updateAttendanceFlag() {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("Y-m-d", strtotime($businessDate));
			
			// Update ATTENDANCE_CLOSE Flag to L_BUSINESS_DATE Table Start by Akhand
			$getMaxSql 				= "
										SELECT 
												EMPLOYEE_ATTENDANCE_ID
										FROM 
												hrms_employee_attendance_info
										WHERE 
												ATTENDANCE_DATE	= '".$businessDate."'
										AND 	OUT_TIME IS NULL			
			";
			$getMaxStatement		= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
			$getMaxStatement->prepare();
			$getMaxResult 			= $getMaxStatement->execute();
			
			if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
				$resultSet 	= new ResultSet();
				$resultSet->initialize($getMaxResult);
			}
			$DATA_FOUND	= 0;
			foreach($resultSet as $MAX_ID) {
				$DATA_FOUND	= 1;
			}
			if($DATA_FOUND==0) {
				// Update ATTENDANCE_CLOSE Flag to L_BUSINESS_DATE Table Start by Akhand
				$getUpdateSql	= "
									UPDATE
											l_business_date
									SET
											ATTENDANCE_CLOSE 			= 'y'
									WHERE
											ATTENDANCE_CLOSE			IS NULL
									AND		BUSINESS_DATE				= '".$businessDate."'
				";
				$getUpdateStatement		= $this->tableGateway->getAdapter()->createStatement($getUpdateSql);
				$getUpdateStatement->prepare();
				if($getUpdateStatement->execute()) {
					$success	= true;
				} else {
					$success	= false;
				}
				// Update ATTENDANCE_CLOSE Flag to L_BUSINESS_DATE Table End by Akhand
			}
		}
	}
?>	