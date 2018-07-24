<?php
	namespace HumanResource\Model;
	
	class EmployeeAttendanceInfo {
		public $EMPLOYEE_ATTENDANCE_ID;
		public $EMPLOYEE_ID;
		public $ATTENDANCE_DATE;
		public $IN_TIME;
		public $OUT_TIME;
		public $WORKING_HOUR;
		public $LATE_HOUR;
		public $OVER_HOUR;
		public $STATUS;
		
		public function exchangeArray($data) {
			$this->EMPLOYEE_ATTENDANCE_ID 	= (!empty($data['EMPLOYEE_ATTENDANCE_ID'])) ? $data['EMPLOYEE_ATTENDANCE_ID'] : null;
			$this->EMPLOYEE_ID 				= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->ATTENDANCE_DATE 			= (!empty($data['ATTENDANCE_DATE'])) ? $data['ATTENDANCE_DATE'] : null;
			$this->IN_TIME 					= (!empty($data['IN_TIME'])) ? $data['IN_TIME'] : 0;
			$this->OUT_TIME 				= (!empty($data['OUT_TIME'])) ? $data['OUT_TIME'] : 0;
			$this->WORKING_HOUR 			= (!empty($data['WORKING_HOUR'])) ? $data['WORKING_HOUR'] : 0;
			$this->LATE_HOUR 				= (!empty($data['LATE_HOUR'])) ? $data['LATE_HOUR'] : 0;
			$this->OVER_HOUR 				= (!empty($data['OVER_HOUR'])) ? $data['OVER_HOUR'] : 0;
			$this->STATUS 					= (!empty($data['STATUS'])) ? $data['STATUS'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	