<?php
	namespace HumanResource\Model;
	
	class EmployeePersonalInfo {
		public $EMPLOYEE_ID;
		public $EMPLOYEE_TYPE;
		public $EMPLOYEE_NAME;
		public $FATHER_NAME;
		public $MOTHER_NAME;
		public $DATE_OF_BIRTH;
		public $PLACE_OF_BIRTH;
		public $GENDER;
		public $BLOOD_GROUP;
		public $EMPLOYEE_PHOTO;
		public $NATIONAL_PHOTO;
		public $RELIGION;
		public $MARITAL_STATUS;
		public $NATIONALITY_ID;
		public $COUNTRY_ID;
		public $CITY_ID;
		public $PERMANENT_ADDRESS;
		public $POLICE_STATION;
		public $MOBILE_NUMBER;
		
		public function exchangeArray($data) {
			$this->EMPLOYEE_ID 				= (!empty($data['EMPLOYEE_ID'])) ? $data['EMPLOYEE_ID'] : null;
			$this->EMPLOYEE_TYPE 			= (!empty($data['EMPLOYEE_TYPE'])) ? $data['EMPLOYEE_TYPE'] : null;
			$this->EMPLOYEE_NAME 			= (!empty($data['EMPLOYEE_NAME'])) ? $data['EMPLOYEE_NAME'] : null;
			$this->FATHER_NAME 				= (!empty($data['FATHER_NAME'])) ? $data['FATHER_NAME'] : null;
			$this->MOTHER_NAME 				= (!empty($data['MOTHER_NAME'])) ? $data['MOTHER_NAME'] : null;
			$this->DATE_OF_BIRTH 			= (!empty($data['DATE_OF_BIRTH'])) ? $data['DATE_OF_BIRTH'] : null;
			$this->PLACE_OF_BIRTH 			= (!empty($data['PLACE_OF_BIRTH'])) ? $data['PLACE_OF_BIRTH'] : null;
			$this->GENDER 					= (!empty($data['GENDER'])) ? $data['GENDER'] : null;
			$this->BLOOD_GROUP 				= (!empty($data['BLOOD_GROUP'])) ? $data['BLOOD_GROUP'] : null;
			$this->EMPLOYEE_PHOTO 			= (!empty($data['EMPLOYEE_PHOTO'])) ? $data['EMPLOYEE_PHOTO'] : null;
			$this->NATIONAL_PHOTO 			= (!empty($data['NATIONAL_PHOTO'])) ? $data['NATIONAL_PHOTO'] : null;
			$this->RELIGION 				= (!empty($data['RELIGION'])) ? $data['RELIGION'] : null;
			$this->MARITAL_STATUS 			= (!empty($data['MARITAL_STATUS'])) ? $data['MARITAL_STATUS'] : null;
			$this->NATIONALITY_ID 			= (!empty($data['NATIONALITY_ID'])) ? $data['NATIONALITY_ID'] : null;
			$this->COUNTRY_ID 				= (!empty($data['COUNTRY_ID'])) ? $data['COUNTRY_ID'] : null;
			$this->CITY_ID 					= (!empty($data['CITY_ID'])) ? $data['CITY_ID'] : null;
			$this->PERMANENT_ADDRESS 		= (!empty($data['PERMANENT_ADDRESS'])) ? $data['PERMANENT_ADDRESS'] : null;
			$this->POLICE_STATION 			= (!empty($data['POLICE_STATION'])) ? $data['POLICE_STATION'] : null;
			$this->MOBILE_NUMBER 			= (!empty($data['MOBILE_NUMBER'])) ? $data['MOBILE_NUMBER'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
	}
?>	