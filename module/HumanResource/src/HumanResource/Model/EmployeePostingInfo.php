<?php
	namespace HumanResource\Model;
	
	class EmployeePostingInfo {
		public $EMPLOYEE_POSTING_ID;
		public $EMPLOYEE_ID;
		public $DESIGNATION_ID;
		public $BRANCH_ID;
		public $DIVISION_NAME;
		
		public function exchangeArray($data) {
			$this->EMPLOYEE_POSTING_ID 		= (!empty($data['EMPLOYEE_POSTING_ID'])) ? $data['EMPLOYEE_POSTING_ID'] : null;
			$this->EMPLOYEE_ID 				= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->DESIGNATION_ID 			= (!empty($data['DESIGNATION_ID'])) ? $data['DESIGNATION_ID'] : null;
			$this->BRANCH_ID 				= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->DIVISION_NAME 			= (!empty($data['DIVISION_NAME'])) ? $data['DIVISION_NAME'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	