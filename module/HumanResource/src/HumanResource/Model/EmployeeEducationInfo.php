<?php
	namespace HumanResource\Model;
	
	class EmployeeEducationInfo {
		public $EMPLOYEE_EDUCATION_ID;
		public $EMPLOYEE_ID;
		public $LAST_CERTIFICATE;
		public $INSTITUTE_NAME;
		public $PASSING_YEAR;
		public $MARKS_OBTAIN;
		public $CLASS_DIVISION;
		public $EDU_PHOTO;
		
		public function exchangeArray($data) {
			$this->EMPLOYEE_EDUCATION_ID 	= (!empty($data['EMPLOYEE_EDUCATION_ID'])) ? $data['EMPLOYEE_EDUCATION_ID'] : null;
			$this->EMPLOYEE_ID 				= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->LAST_CERTIFICATE 		= (!empty($data['LAST_CERTIFICATE'])) ? $data['LAST_CERTIFICATE'] : null;
			$this->INSTITUTE_NAME 			= (!empty($data['INSTITUTE_NAME'])) ? $data['INSTITUTE_NAME'] : null;
			$this->PASSING_YEAR 			= (!empty($data['PASSING_YEAR'])) ? $data['PASSING_YEAR'] : null;
			$this->MARKS_OBTAIN 			= (!empty($data['MARKS_OBTAIN'])) ? $data['MARKS_OBTAIN'] : null;
			$this->CLASS_DIVISION 			= (!empty($data['CLASS_DIVISION'])) ? $data['CLASS_DIVISION'] : null;
			$this->EDU_PHOTO 				= (!empty($data['EDU_PHOTO'])) ? $data['EDU_PHOTO'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	