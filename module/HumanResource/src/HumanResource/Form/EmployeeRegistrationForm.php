<?php
	namespace HumanResource\Form;
	
	use Zend\Form\Form;	
	use Zend\InputFilter;
	use Zend\Form\Element;
	use Zend\Filter\File\Rename;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	use Zend\Validator\NotEmpty;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
		
	class EmployeeRegistrationForm extends Form {
		protected $investormanagementTable;
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			parent::__construct($name);
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationEmployeeRegistrationForm();');
			$this->setAttribute('enctype','multipart/form-data');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->postedValues = $postedValues;
			
			$this->add(array(
				'name' => 'EMPLOYEE_ID',
				'type' => 'Hidden',
			));
			
			$this->add(array(
				'name' => 'EMPLOYEE_TYPE',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'EMPLOYEE_TYPE',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Type : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeTypeForSelect(),
				),
			));
			$this->add(array(
				'name' => 'GENDER',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'GENDER',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Gender : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeGenderForSelect(),
				),
			));
			$this->add(array(
				'name' => 'BLOOD_GROUP',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'BLOOD_GROUP',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Blood Group : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeBloodGroupForSelect(),
				),
			));
			$this->add(array(
				'name' => 'RELIGION',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'RELIGION',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Religion : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeReligionForSelect(),
				),
			));
			$this->add(array(
				'name' => 'MARITAL_STATUS',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'MARITAL_STATUS',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'MArital Status : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeMaritalStatusForSelect(),
				),
			));
			$this->add(array(
				'name' => 'NATIONALITY_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'NATIONALITY_ID',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Nationality : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getNationalityForSelect(),
				),
			));
			$this->add(array(
				'name' => 'COUNTRY_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'COUNTRY_ID',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Country : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCountryForSelect(),
				),
			));
			$this->add(array(
				'name' => 'CITY_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'CITY_ID',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'City : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCityForSelect(),
				),
			));
			$this->add(array(
				'name' => 'OCCUPATION_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'OCCUPATION_ID',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Occupation : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getOccupationForSelect(),
				),
			));
			$this->add(array(
				'name' => 'LAST_CERTIFICATE',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'LAST_CERTIFICATE',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Certificate : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeCertificateListForSelect(),
				),
			));
			$this->add(array(
				'name' => 'CLASS_DIVISION',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'CLASS_DIVISION',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Class/Division : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeClassDivisionForSelect(),
				),
			));
			$this->add(array(
				'name' => 'DESIGNATION_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'DESIGNATION_ID',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Designation : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getDesignationForSelect(),
				),
			));
			$this->add(array(
				'name' => 'BRANCH_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'BRANCH_ID',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Branch : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getBranchForSelect(),
				),
			));
			$this->add(array(
				'name' => 'DIVISION_NAME',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'DIVISION_NAME',
					'style' => 'font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Division Name : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getEmployeeDivisionForSelect(),
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'onclick' => '',
				),
			));
		}
		
		public function getEmployeeTypeForSelect() {
			$employeeType	= array(
				"Permanent"		=>'Permanent',
				"Temporary"		=>'Temporary',
				"Contractual"	=>'Contractual',
				"Consultant"	=>'Consultant',
			);
			
			$selectData = array();
			foreach ($employeeType as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}
		
		public function getEmployeeGenderForSelect() {
			$employeeGender	= array(
				"Male"		=>'Male',
				"Female"	=>'Female',
			);
			
			$selectData = array();
			foreach ($employeeGender as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}
		
		public function getEmployeeBloodGroupForSelect() {
			$employeeBloodGroup	= array(
				"A+"	=>'A+',
				"A-"	=>'A-',
				"B+"	=>'B+',
				"B-"	=>'B-',
				"AB+"	=>'AB+',
				"AB-"	=>'AB-',
				"O+"	=>'O+',
				"O-"	=>'O-',
			);
			
			$selectData = array();
			foreach ($employeeBloodGroup as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}
		
		public function getEmployeeReligionForSelect() {
			$employeeReligion	= array(
				"Budhism"		=>'Budhism',
				"Christianty"	=>'Christianty',
				"Hinduism"		=>'Hinduism',
				"Islam"			=>'Islam',
				"Others"		=>'Others',
			);
			
			$selectData = array();
			foreach ($employeeReligion as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}
		
		public function getEmployeeMaritalStatusForSelect() {
			$employeeMaritalStatus	= array(
				"Married"		=>'Married',
				"Un Married"	=>'Un Married',
				"Single"		=>'Single',
			);
			
			$selectData = array();
			foreach ($employeeMaritalStatus as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}
		
		public function getNationalityForSelect() {
			$nationalitySql = "
								SELECT 
										NATIONALITY_ID,
										NATIONALITY
								FROM 
										gs_nationality 
								ORDER BY 
										NATIONALITY ASC
							";
			$nationalityStatement	= $this->dbAdapter->query($nationalitySql);
			$nationalityResult		= $nationalityStatement->execute();
			
			$selectData 		= array();
			foreach ($nationalityResult as $selectOption) {
				$selectData[$selectOption['NATIONALITY_ID']] = $selectOption['NATIONALITY'];
			}
			return $selectData;
		}
		
		public function getCountryForSelect() {
			$countrySql = "
							SELECT 
									COUNTRY_ID,
									COUNTRY
							FROM 
									gs_country 
							ORDER BY 
									COUNTRY ASC
						";
			$countryStatement	= $this->dbAdapter->query($countrySql);
			$countryResult		= $countryStatement->execute();
			
			$selectData 		= array();
			foreach ($countryResult as $selectOption) {
				$selectData[$selectOption['COUNTRY_ID']] = $selectOption['COUNTRY'];
			}
			return $selectData;
		}
		
		public function getCityForSelect() {
			$citySql = "
						SELECT 
								CITY_ID,
								CITY
						FROM 
								gs_city 
						ORDER BY 
								CITY ASC
					";
			$cityStatement	= $this->dbAdapter->query($citySql);
			$cityResult		= $cityStatement->execute();
			
			$selectData 		= array();
			foreach ($cityResult as $selectOption) {
				$selectData[$selectOption['CITY_ID']] = $selectOption['CITY'];
			}
			return $selectData;
		}
		
		public function getOccupationForSelect() {
			$occupationSql = "
								SELECT 
										OCCUPATION_ID,
										OCCUPATION
								FROM 
										gs_occupation 
								ORDER BY 
										OCCUPATION ASC
							";
			$occupationStatement	= $this->dbAdapter->query($occupationSql);
			$occupationResult		= $occupationStatement->execute();
			
			$selectData 		= array();
			foreach ($occupationResult as $selectOption) {
				$selectData[$selectOption['OCCUPATION_ID']] = $selectOption['OCCUPATION'];
			}
			return $selectData;
		}
		
		public function getEmployeeCertificateListForSelect() {
			$employeeCertificate	= array(
				"SSC"		=>'SSC',
				"HSC"		=>'HSC',
				"BA"		=>'BA',
				"BSC"		=>'BSC',
				"BCOM"		=>'BCOM',
				"HONORS"	=>'HONORS',
				"MASTERS"	=>'MASTERS',
			);
			
			$selectData = array();
			foreach ($employeeCertificate as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}
		
		public function getEmployeeClassDivisionForSelect() {
			$employeeClassDivision	= array(
				"First Class/Division"		=>'First Class/Division',
				"Second Class/Division"		=>'Second Class/Division',
				"Third Class/Division"		=>'Third Class/Division',
			);
			
			$selectData = array();
			foreach ($employeeClassDivision as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}

		public function getDesignationForSelect() {
			$designationSql = "
								SELECT 
										DESIGNATION_ID,
										DESIGNATION
								FROM 
										gs_designation 
								ORDER BY 
										DESIGNATION ASC
							";
			$designationStatement	= $this->dbAdapter->query($designationSql);
			$designationResult		= $designationStatement->execute();
			
			$selectData 		= array();
			foreach ($designationResult as $selectOption) {
				$selectData[$selectOption['DESIGNATION_ID']] = $selectOption['DESIGNATION'];
			}
			return $selectData;
		}
		
		public function getBranchForSelect() {
			$branchSql = "
							SELECT 
									BRANCH_ID,
									BRANCH_NAME
							FROM 
									c_branch
							WHERE 
									COMPANY_ID	= 	1	 
							ORDER BY 
									BRANCH_NAME ASC
						";
			$branchStatement	= $this->dbAdapter->query($branchSql);
			$branchResult		= $branchStatement->execute();
			
			$selectData 		= array();
			foreach ($branchResult as $selectOption) {
				$selectData[$selectOption['BRANCH_ID']] = $selectOption['BRANCH_NAME'];
			}
			return $selectData;
		}
		
		public function getEmployeeDivisionForSelect() {
			$employeeMaritalStatus	= array(
				"Sales Division"		=>'Sales Division',
				"Burnish Division"		=>'Burnish Division',
				"Accounts Division"		=>'Accounts Division',
			);
			
			$selectData = array();
			foreach ($employeeMaritalStatus as $selectOption) {
				$selectData[$selectOption] = $selectOption;
			}
			return $selectData;	
		}
	}
?>	