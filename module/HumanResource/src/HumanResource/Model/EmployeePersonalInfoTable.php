<?php
	namespace HumanResource\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeePersonalInfoTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				// create a new Select object for the table IS_INVESTOR_DETAILS
				$select = new Select('hrms_employee_personal_info');
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new EmployeePersonalInfo());
				// create a new pagination adapter object
				$paginatorAdapter = new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator = new Paginator($paginatorAdapter);
				return $paginator;
			}
			if (null === $select)
			$select 	= new Select();
			$select->from($this->table);
			$resultSet 	= $this->selectWith($select);
			$resultSet->buffer();
			return $resultSet;
		}
		
		public function fetchEmployeeName($input='') {
			if(!empty($input)){
				$getEmployeeSql   = "
								SELECT 
										EMPPERINFO.EMPLOYEE_ID			AS EMPLOYEE_ID,
										EMPPERINFO.EMPLOYEE_NAME		AS EMPLOYEE_NAME,
										EMPPERINFO.MOBILE_NUMBER		AS MOBILE_NUMBER,
										EMPPERINFO.EMPLOYEE_TYPE		AS EMPLOYEE_TYPE,
										EMPPERINFO.PERMANENT_ADDRESS	AS PERMANENT_ADDRESS,
										EMPPOSTINGINFO.DIVISION_NAME	AS DIVISION_NAME,
										DESG.DESIGNATION				AS DESIGNATION,
										GSCOA.COA_CODE					AS PAYABLE_COA_CODE,
										GSCOA.COA_NAME					AS PAYABLE_COA_NAME
								FROM 
										hrms_employee_personal_info EMPPERINFO,
										hrms_employee_posting_info	EMPPOSTINGINFO,
										gs_designation				DESG,	
										gs_coa						GSCOA									
								WHERE 	LOWER(EMPPERINFO.EMPLOYEE_NAME) = '".$input."'
								AND		EMPPERINFO.PAYABLE_COA			= GSCOA.COA_CODE
								AND		EMPPERINFO.EMPLOYEE_ID			= EMPPOSTINGINFO.EMPLOYEE_ID
								AND		EMPPOSTINGINFO.DESIGNATION_ID	= DESG.DESIGNATION_ID
								AND		EMPPOSTINGINFO.END_DATE IS NULL
								ORDER BY 
										LOWER(EMPPERINFO.EMPLOYEE_NAME) ASC
								";
			}else{
				$getEmployeeSql   = "
								SELECT 
										EMPPERINFO.EMPLOYEE_ID			AS EMPLOYEE_ID,
										EMPPERINFO.EMPLOYEE_NAME		AS EMPLOYEE_NAME,
										EMPPERINFO.MOBILE_NUMBER		AS MOBILE_NUMBER,
										EMPPERINFO.EMPLOYEE_TYPE		AS EMPLOYEE_TYPE,
										EMPPERINFO.PERMANENT_ADDRESS	AS PERMANENT_ADDRESS,
										EMPPOSTINGINFO.DIVISION_NAME	AS DIVISION_NAME,
										DESG.DESIGNATION				AS DESIGNATION,
										GSCOA.COA_CODE					AS PAYABLE_COA_CODE,
										GSCOA.COA_NAME					AS PAYABLE_COA_NAME
										
								FROM 
										hrms_employee_personal_info EMPPERINFO,
										hrms_employee_posting_info	EMPPOSTINGINFO,
										gs_designation				DESG,	
										gs_coa						GSCOA									
								WHERE 	
										EMPPERINFO.PAYABLE_COA			= GSCOA.COA_CODE
								AND		EMPPERINFO.EMPLOYEE_ID			= EMPPOSTINGINFO.EMPLOYEE_ID
								AND		EMPPOSTINGINFO.DESIGNATION_ID	= DESG.DESIGNATION_ID
								AND		EMPPOSTINGINFO.END_DATE IS NULL
								ORDER BY 
										LOWER(EMPPERINFO.EMPLOYEE_NAME) ASC
			";
			}
			
			$getEmployeeStatement 	= $this->tableGateway->getAdapter()->createStatement($getEmployeeSql);
			$getEmployeeStatement->prepare();
			$getEmployeeResult 		= $getEmployeeStatement->execute();
			
			if ($getEmployeeResult instanceof ResultInterface && $getEmployeeResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getEmployeeResult);
			}
			
			return $resultSet;
		}
		
		public function getEmployeePersonalInfo($EMPLOYEE_ID) {
			$COND		= '';
			if($EMPLOYEE_ID) {
				$COND	= "AND PERSONALINFO.EMPLOYEE_ID	= '".$EMPLOYEE_ID."'";	
			} else {
				$COND	= '';	
			}
			
			$getEmployeeDetailsSql	= "
										SELECT
												PERSONALINFO.EMPLOYEE_ID                			AS EMPLOYEE_ID,
												PERSONALINFO.EMPLOYEE_TYPE              			AS EMPLOYEE_TYPE,
												PERSONALINFO.EMPLOYEE_NAME              			AS EMPLOYEE_NAME,
												PERSONALINFO.FATHER_NAME                			AS FATHER_NAME, 
												PERSONALINFO.MOTHER_NAME                			AS MOTHER_NAME,
												PERSONALINFO.DATE_OF_BIRTH							AS DATE_OF_BIRTH,
												PERSONALINFO.PLACE_OF_BIRTH             			AS PLACE_OF_BIRTH,
												PERSONALINFO.GENDER                     			AS GENDER,
												PERSONALINFO.BLOOD_GROUP                			AS BLOOD_GROUP,
												PERSONALINFO.EMPLOYEE_PHOTO             			AS EMPLOYEE_PHOTO,
												PERSONALINFO.RELIGION                   			AS RELIGION,
												PERSONALINFO.MARITAL_STATUS             			AS MARITAL_STATUS,
												PERSONALINFO.NATIONALITY_ID             			AS NATIONALITY_ID,
												PERSONALINFO.NATIONAL_PHOTO             			AS NATIONAL_PHOTO,
												PERSONALINFO.COUNTRY_ID                 			AS COUNTRY_ID,
												PERSONALINFO.CITY_ID                    			AS CITY_ID,
												PERSONALINFO.PERMANENT_ADDRESS          			AS PERMANENT_ADDRESS,
												PERSONALINFO.POLICE_STATION             			AS POLICE_STATION,
												PERSONALINFO.MOBILE_NUMBER              			AS MOBILE_NUMBER,
												CONTACTINFO.EMPLOYEE_CONTACT_ID         			AS EMPLOYEE_CONTACT_ID,
												CONTACTINFO.CONTACT_ADDRESS_FIRST       			AS CONTACT_ADDRESS_FIRST,
												CONTACTINFO.CONTACT_ADDRESS_SECOND      			AS CONTACT_ADDRESS_SECOND,
												CONTACTINFO.TELEPHONE                   			AS TELEPHONE,
												CONTACTINFO.EMAIL_ADDRESS               			AS EMAIL_ADDRESS,
												EDUCATIONINFO.EMPLOYEE_EDUCATION_ID     			AS EMPLOYEE_EDUCATION_ID,
												EDUCATIONINFO.LAST_CERTIFICATE          			AS LAST_CERTIFICATE,
												EDUCATIONINFO.INSTITUTE_NAME            			AS INSTITUTE_NAME,
												EDUCATIONINFO.PASSING_YEAR              			AS PASSING_YEAR,
												EDUCATIONINFO.MARKS_OBTAIN              			AS MARKS_OBTAIN,
												EDUCATIONINFO.CLASS_DIVISION            			AS CLASS_DIVISION,
												EDUCATIONINFO.EDU_PHOTO		            			AS EDU_PHOTO,
												POSTINGINFO.EMPLOYEE_POSTING_ID         			AS EMPLOYEE_POSTING_ID,
												POSTINGINFO.DESIGNATION_ID              			AS DESIGNATION_ID,
												POSTINGINFO.BRANCH_ID                  				AS BRANCH_ID,
												BRANCH.BRANCH_ID									AS BRANCH_ID,
												BRANCH.BRANCH_NAME									AS BRANCH_NAME,
												POSTINGINFO.DIVISION_NAME               			AS DIVISION_NAME,
												SALARYINFO.EMPLOYEE_SALARY_ID           			AS EMPLOYEE_SALARY_ID,
												SALARYINFO.SALARY_AMOUNT                			AS SALARY_AMOUNT,
												SPAOUSEINFO.EMPLOYEE_SPOUSE_ID         				AS EMPLOYEE_SPOUSE_ID,
												SPAOUSEINFO.SPOUSE_NAME                 			AS SPOUSE_NAME,
												SPAOUSEINFO.OCCUPATION_ID               			AS OCCUPATION_ID,
												SPAOUSEINFO.SPOUSE_ADDRESS              			AS SPOUSE_ADDRESS,
												DESIGNATION.DESIGNATION_ID							AS DESIGNATION_ID,
												DESIGNATION.DESIGNATION								AS DESIGNATION
										FROM
												hrms_employee_contact_info              CONTACTINFO,
												hrms_employee_education_info            EDUCATIONINFO,
												hrms_employee_posting_info              POSTINGINFO,
												hrms_employee_salary_info               SALARYINFO,
												c_branch								BRANCH,
												gs_designation							DESIGNATION,
												hrms_employee_personal_info             PERSONALINFO
										LEFT JOIN
												hrms_employee_spouse_info               SPAOUSEINFO
										ON		PERSONALINFO.EMPLOYEE_ID            	= SPAOUSEINFO.EMPLOYEE_ID
										WHERE
												PERSONALINFO.EMPLOYEE_ID            	= CONTACTINFO.EMPLOYEE_ID
										AND 	PERSONALINFO.EMPLOYEE_ID            	= EDUCATIONINFO.EMPLOYEE_ID
										AND 	PERSONALINFO.EMPLOYEE_ID            	= POSTINGINFO.EMPLOYEE_ID
										AND		POSTINGINFO.END_DATE IS NULL
										AND 	PERSONALINFO.EMPLOYEE_ID            	= SALARYINFO.EMPLOYEE_ID
										AND		SALARYINFO.END_DATE IS NULL
										AND		POSTINGINFO.BRANCH_ID					= BRANCH.BRANCH_ID
										AND		POSTINGINFO.DESIGNATION_ID				= DESIGNATION.DESIGNATION_ID	
										{$COND}
										ORDER BY
												BRANCH.BRANCH_ID,
												POSTINGINFO.DIVISION_NAME,
												DESIGNATION.DESIGNATION
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
		public function getEmployeePersonalInfoDetails($EMPLOYEE_ID,$BRANCH_ID) {
			$COND		= '';
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
			
			$getEmployeeDetailsSql	= "
										SELECT
												PERSONALINFO.EMPLOYEE_ID                			AS EMPLOYEE_ID,
												PERSONALINFO.EMPLOYEE_TYPE              			AS EMPLOYEE_TYPE,
												PERSONALINFO.EMPLOYEE_NAME              			AS EMPLOYEE_NAME,
												PERSONALINFO.FATHER_NAME                			AS FATHER_NAME, 
												PERSONALINFO.MOTHER_NAME                			AS MOTHER_NAME,
												PERSONALINFO.DATE_OF_BIRTH							AS DATE_OF_BIRTH,
												PERSONALINFO.PLACE_OF_BIRTH             			AS PLACE_OF_BIRTH,
												PERSONALINFO.GENDER                     			AS GENDER,
												PERSONALINFO.BLOOD_GROUP                			AS BLOOD_GROUP,
												PERSONALINFO.EMPLOYEE_PHOTO             			AS EMPLOYEE_PHOTO,
												PERSONALINFO.NATIONAL_PHOTO             			AS NATIONAL_PHOTO,
												PERSONALINFO.RELIGION                   			AS RELIGION,
												PERSONALINFO.MARITAL_STATUS             			AS MARITAL_STATUS,
												PERSONALINFO.NATIONALITY_ID             			AS NATIONALITY_ID,
												PERSONALINFO.COUNTRY_ID                 			AS COUNTRY_ID,
												PERSONALINFO.CITY_ID                    			AS CITY_ID,
												PERSONALINFO.PERMANENT_ADDRESS          			AS PERMANENT_ADDRESS,
												PERSONALINFO.POLICE_STATION             			AS POLICE_STATION,
												PERSONALINFO.MOBILE_NUMBER              			AS MOBILE_NUMBER,
												CONTACTINFO.EMPLOYEE_CONTACT_ID         			AS EMPLOYEE_CONTACT_ID,
												CONTACTINFO.CONTACT_ADDRESS_FIRST       			AS CONTACT_ADDRESS_FIRST,
												CONTACTINFO.CONTACT_ADDRESS_SECOND      			AS CONTACT_ADDRESS_SECOND,
												CONTACTINFO.TELEPHONE                   			AS TELEPHONE,
												CONTACTINFO.EMAIL_ADDRESS               			AS EMAIL_ADDRESS,
												EDUCATIONINFO.EMPLOYEE_EDUCATION_ID     			AS EMPLOYEE_EDUCATION_ID,
												EDUCATIONINFO.LAST_CERTIFICATE          			AS LAST_CERTIFICATE,
												EDUCATIONINFO.INSTITUTE_NAME            			AS INSTITUTE_NAME,
												EDUCATIONINFO.PASSING_YEAR              			AS PASSING_YEAR,
												EDUCATIONINFO.MARKS_OBTAIN              			AS MARKS_OBTAIN,
												EDUCATIONINFO.CLASS_DIVISION            			AS CLASS_DIVISION,
												EDUCATIONINFO.EDU_PHOTO            					AS EDU_PHOTO,
												POSTINGINFO.EMPLOYEE_POSTING_ID         			AS EMPLOYEE_POSTING_ID,
												POSTINGINFO.DESIGNATION_ID              			AS DESIGNATION_ID,
												POSTINGINFO.BRANCH_ID                  				AS BRANCH_ID,
												BRANCH.BRANCH_ID									AS BRANCH_ID,
												BRANCH.BRANCH_NAME									AS BRANCH_NAME,
												POSTINGINFO.DIVISION_NAME               			AS DIVISION_NAME,
												SALARYINFO.EMPLOYEE_SALARY_ID           			AS EMPLOYEE_SALARY_ID,
												SALARYINFO.SALARY_AMOUNT                			AS SALARY_AMOUNT,
												SPAOUSEINFO.EMPLOYEE_SPOUSE_ID         				AS EMPLOYEE_SPOUSE_ID,
												SPAOUSEINFO.SPOUSE_NAME                 			AS SPOUSE_NAME,
												SPAOUSEINFO.OCCUPATION_ID               			AS OCCUPATION_ID,
												SPAOUSEINFO.SPOUSE_ADDRESS              			AS SPOUSE_ADDRESS,
												DESIGNATION.DESIGNATION_ID							AS DESIGNATION_ID,
												DESIGNATION.DESIGNATION								AS DESIGNATION
										FROM
												hrms_employee_contact_info              CONTACTINFO,
												hrms_employee_education_info            EDUCATIONINFO,
												hrms_employee_posting_info              POSTINGINFO,
												hrms_employee_salary_info               SALARYINFO,
												c_branch								BRANCH,
												gs_designation							DESIGNATION,
												hrms_employee_personal_info             PERSONALINFO
										LEFT JOIN
												hrms_employee_spouse_info               SPAOUSEINFO
										ON		PERSONALINFO.EMPLOYEE_ID            	= SPAOUSEINFO.EMPLOYEE_ID
										WHERE
												PERSONALINFO.EMPLOYEE_ID            	= CONTACTINFO.EMPLOYEE_ID
										AND 	PERSONALINFO.EMPLOYEE_ID            	= EDUCATIONINFO.EMPLOYEE_ID
										AND 	PERSONALINFO.EMPLOYEE_ID            	= POSTINGINFO.EMPLOYEE_ID
										AND		POSTINGINFO.END_DATE IS NULL
										AND 	PERSONALINFO.EMPLOYEE_ID            	= SALARYINFO.EMPLOYEE_ID
										AND		SALARYINFO.END_DATE IS NULL
										AND		POSTINGINFO.BRANCH_ID					= BRANCH.BRANCH_ID
										AND		POSTINGINFO.DESIGNATION_ID				= DESIGNATION.DESIGNATION_ID	
										{$BRANCH_COND}
										{$COND}
										ORDER BY
												BRANCH.BRANCH_ID,
												POSTINGINFO.DIVISION_NAME,
												DESIGNATION.DESIGNATION
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
		
		public function getEmployeePersonalInfoDetailsCount($EMPLOYEE_ID,$BRANCH_ID) {
			$COND		= '';
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
			
			$getEmployeeDetailsSql	= "
										SELECT
												DISTINCT BRANCH.BRANCH_ID				AS BRANCH_ID,
												BRANCH.BRANCH_NAME						AS BRANCH_NAME,
												(SELECT 
													DISTINCT hrms_employee_posting_info.DIVISION_NAME
												FROM 
													hrms_employee_posting_info
												WHERE
													hrms_employee_posting_info.BRANCH_ID	= BRANCH.BRANCH_ID
												AND	hrms_employee_posting_info.END_DATE	IS NULL
												) 										AS DIVISION_NAME,
												(SELECT 
													COUNT(hrms_employee_posting_info.EMPLOYEE_ID)
												FROM 
													hrms_employee_posting_info
												WHERE
													hrms_employee_posting_info.BRANCH_ID	= BRANCH.BRANCH_ID
												AND	hrms_employee_posting_info.END_DATE	IS NULL
												) 										AS TOTAL_EMPLOYEE
										FROM
												c_branch								BRANCH
										WHERE
												POSTINGINFO.BRANCH_ID					= BRANCH.BRANCH_ID
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
		
		public function getAllEmployeePersonalInfo($CONTROLLER_NAME) {
			$CONDITION	= '';
			if($CONTROLLER_NAME == 'Access Control') {
				$CONDITION	= "AND 	PERSONALINFO.LOGIN_STATUS IS NULL";	
			} else if($CONTROLLER_NAME == 'Joining') {
				$CONDITION	= "AND 	PERSONALINFO.LOGIN_STATUS IS NOT NULL";	
			} else {
				$CONDITION	= '';
			}
			
			$getEmployeeDetailsSql	= "
										SELECT
												PERSONALINFO.EMPLOYEE_ID                			AS EMPLOYEE_ID,
												PERSONALINFO.EMPLOYEE_TYPE              			AS EMPLOYEE_TYPE,
												PERSONALINFO.EMPLOYEE_NAME              			AS EMPLOYEE_NAME,
												PERSONALINFO.FATHER_NAME                			AS FATHER_NAME, 
												PERSONALINFO.MOTHER_NAME                			AS MOTHER_NAME,
												PERSONALINFO.DATE_OF_BIRTH							AS DATE_OF_BIRTH,
												PERSONALINFO.PLACE_OF_BIRTH             			AS PLACE_OF_BIRTH,
												PERSONALINFO.GENDER                     			AS GENDER,
												PERSONALINFO.BLOOD_GROUP                			AS BLOOD_GROUP,
												PERSONALINFO.EMPLOYEE_PHOTO             			AS EMPLOYEE_PHOTO,
												PERSONALINFO.RELIGION                   			AS RELIGION,
												PERSONALINFO.MARITAL_STATUS             			AS MARITAL_STATUS,
												PERSONALINFO.NATIONALITY_ID             			AS NATIONALITY_ID,
												PERSONALINFO.COUNTRY_ID                 			AS COUNTRY_ID,
												PERSONALINFO.CITY_ID                    			AS CITY_ID,
												PERSONALINFO.PERMANENT_ADDRESS          			AS PERMANENT_ADDRESS,
												PERSONALINFO.POLICE_STATION             			AS POLICE_STATION,
												PERSONALINFO.MOBILE_NUMBER              			AS MOBILE_NUMBER,
												PERSONALINFO.LOGIN_STATUS              				AS LOGIN_STATUS,
												PERSONALINFO.JOINING_DATE							AS JOINING_DATE,
												CONTACTINFO.EMPLOYEE_CONTACT_ID         			AS EMPLOYEE_CONTACT_ID,
												CONTACTINFO.CONTACT_ADDRESS_FIRST       			AS CONTACT_ADDRESS_FIRST,
												CONTACTINFO.CONTACT_ADDRESS_SECOND      			AS CONTACT_ADDRESS_SECOND,
												CONTACTINFO.TELEPHONE                   			AS TELEPHONE,
												CONTACTINFO.EMAIL_ADDRESS               			AS EMAIL_ADDRESS,
												EDUCATIONINFO.EMPLOYEE_EDUCATION_ID     			AS EMPLOYEE_EDUCATION_ID,
												EDUCATIONINFO.LAST_CERTIFICATE          			AS LAST_CERTIFICATE,
												EDUCATIONINFO.INSTITUTE_NAME            			AS INSTITUTE_NAME,
												EDUCATIONINFO.PASSING_YEAR              			AS PASSING_YEAR,
												EDUCATIONINFO.MARKS_OBTAIN              			AS MARKS_OBTAIN,
												EDUCATIONINFO.CLASS_DIVISION            			AS CLASS_DIVISION,
												POSTINGINFO.EMPLOYEE_POSTING_ID         			AS EMPLOYEE_POSTING_ID,
												POSTINGINFO.DESIGNATION_ID              			AS DESIGNATION_ID,
												POSTINGINFO.BRANCH_ID                  				AS BRANCH_ID,
												POSTINGINFO.DIVISION_NAME               			AS DIVISION_NAME,
												SALARYINFO.EMPLOYEE_SALARY_ID           			AS EMPLOYEE_SALARY_ID,
												SALARYINFO.SALARY_AMOUNT                			AS SALARY_AMOUNT,
												SPAOUSEINFO.EMPLOYEE_SPOUSE_ID         				AS EMPLOYEE_SPOUSE_ID,
												SPAOUSEINFO.SPOUSE_NAME                 			AS SPOUSE_NAME,
												SPAOUSEINFO.OCCUPATION_ID               			AS OCCUPATION_ID,
												SPAOUSEINFO.SPOUSE_ADDRESS              			AS SPOUSE_ADDRESS,
												DESIGNATION.DESIGNATION								AS DESIGNATION,
												BRANCH.BRANCH_NAME									AS BRANCH_NAME,
												GSCOA.COA_NAME										AS PAYABLE_COA_NAME,
												GSCOA.COA_CODE										AS PAYABLE_COA_CODE
										FROM
												hrms_employee_contact_info              CONTACTINFO,
												hrms_employee_education_info            EDUCATIONINFO,
												hrms_employee_posting_info              POSTINGINFO,
												hrms_employee_salary_info               SALARYINFO,
												gs_designation							DESIGNATION,
												c_branch								BRANCH,
												gs_coa									GSCOA,
												hrms_employee_personal_info             PERSONALINFO
										LEFT JOIN
												hrms_employee_spouse_info               SPAOUSEINFO
										ON		PERSONALINFO.EMPLOYEE_ID            	= SPAOUSEINFO.EMPLOYEE_ID		
										WHERE
												PERSONALINFO.EMPLOYEE_ID            	= CONTACTINFO.EMPLOYEE_ID
										AND 	PERSONALINFO.EMPLOYEE_ID            	= EDUCATIONINFO.EMPLOYEE_ID
										AND 	PERSONALINFO.EMPLOYEE_ID            	= POSTINGINFO.EMPLOYEE_ID
										AND		POSTINGINFO.END_DATE IS NULL
										AND 	PERSONALINFO.EMPLOYEE_ID            	= SALARYINFO.EMPLOYEE_ID
										AND		SALARYINFO.END_DATE IS NULL
										AND 	POSTINGINFO.DESIGNATION_ID				= DESIGNATION.DESIGNATION_ID
										AND 	POSTINGINFO.BRANCH_ID					= BRANCH.BRANCH_ID
										AND		PERSONALINFO.PAYABLE_COA				= GSCOA.COA_CODE
										{$CONDITION}
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
		
		public function saveEmployeePersonalInfo(EmployeePersonalInfo $employeePersonalInfo) {
			//$employeeId	= 4; return $employeeId;die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("d-M-Y", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			//Salary and Allowance Payable Chart of Account Generate Start By Akhand
			$maxSalaryPayableCOACode 	= '';
			$selectMaxSalaryPayableCOA	= "
											SELECT 
													COALESCE(MAX(substr(COA_CODE,1,9)),201002000)+1  AS MAX_SAL_PAY_COA_CODE
											FROM
													gs_coa 		
											WHERE
													substr(COA_CODE,1,9) BETWEEN '201002000' AND '201002999'
										";
			$maxSalaryPayableCOA 			= $this->tableGateway->getAdapter()->createStatement($selectMaxSalaryPayableCOA);
			$maxSalaryPayableCOA->prepare();
			$resultMaxSalaryPayableCOA 	= $maxSalaryPayableCOA->execute();
			
			if ($resultMaxSalaryPayableCOA instanceof ResultInterface && $resultMaxSalaryPayableCOA->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($resultMaxSalaryPayableCOA);
			}
			
			foreach($resultSet as $resultMaxSalaryPayableCOA) {
				$maxSalaryPayableCOACode	= $resultMaxSalaryPayableCOA->MAX_SAL_PAY_COA_CODE;
			}
			
			$COMPANY_SAL_PAY_COA_CODE 	= $maxSalaryPayableCOACode;
			$COMPANY_SAL_PAY_COA_NAME	= "Payable To Employee - ".$employeePersonalInfo->EMPLOYEE_NAME."";
			$COMPANY_SAL_PAY_AUTO_COA 	= 'y';
			
			$companyWiseSalaryPayableCOASql = "	
												SELECT 
														CN.COMPANY_ID 		AS COM_SAL_PAY_COMPANY_ID,
														C.COA_ID 			AS COM_SAL_PAY_COA_ID,
														C.CASH_FLOW_HEAD 	AS COM_SAL_PAY_CASH_FLOW_HEAD
												FROM 
														gs_coa C,
														c_company CN
												WHERE 
														C.COMPANY_ID  	= CN.COMPANY_ID     
												AND   	C.COA_CODE  	= '201002000'
												ORDER BY 
														C.RGT		
			";
			$companyWiseSalaryPayableCOA		= $this->tableGateway->getAdapter()->createStatement($companyWiseSalaryPayableCOASql);
			$companyWiseSalaryPayableCOA->prepare();
			$companyWiseSalaryPayableCOAResult = $companyWiseSalaryPayableCOA->execute();
			
			if ($companyWiseSalaryPayableCOAResult instanceof ResultInterface && $companyWiseSalaryPayableCOAResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($companyWiseSalaryPayableCOAResult);
			}
			
			foreach($resultSet as $companyWiseSalaryPayableCOA) {
				$COMPANY_SAL_PAY_COMPANY_ID			= $companyWiseSalaryPayableCOA->COM_SAL_PAY_COMPANY_ID;
				$COMPANY_SAL_PAY_COA_ID				= $companyWiseSalaryPayableCOA->COM_SAL_PAY_COA_ID;
				$COMPANY_SAL_PAY_CASH_FLOW_HEAD		= $companyWiseSalaryPayableCOA->COM_SAL_PAY_CASH_FLOW_HEAD;
			}
			//Salary and Allowance Payable Chart of Account Generate End By Akhand
			
			//Salary and Allowance Receivable Chart of Account Generate Start By Akhand
			$maxSalaryReceivableCOACode 	= '';
			$selectMaxSalaryReceivableCOA	= "
											SELECT 
													COALESCE(MAX(substr(COA_CODE,1,9)),302002000)+1  AS MAX_SAL_REC_COA_CODE
											FROM
													gs_coa 		
											WHERE
													substr(COA_CODE,1,9) BETWEEN '302002000' AND '302002999'
										";
			$maxSalaryReceivableCOA 			= $this->tableGateway->getAdapter()->createStatement($selectMaxSalaryReceivableCOA);
			$maxSalaryReceivableCOA->prepare();
			$resultMaxSalaryReceivableCOA 	= $maxSalaryReceivableCOA->execute();
			
			if ($resultMaxSalaryReceivableCOA instanceof ResultInterface && $resultMaxSalaryReceivableCOA->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($resultMaxSalaryReceivableCOA);
			}
			
			foreach($resultSet as $resultMaxSalaryReceivableCOA) {
				$maxSalaryReceivableCOACode	= $resultMaxSalaryReceivableCOA->MAX_SAL_REC_COA_CODE;
			}
			
			$COMPANY_SAL_REC_COA_CODE 	= $maxSalaryReceivableCOACode;
			$COMPANY_SAL_REC_COA_NAME	= "Receivable From Employee - ".$employeePersonalInfo->EMPLOYEE_NAME."";
			$COMPANY_SAL_REC_AUTO_COA 	= 'y';
			
			$companyWiseSalaryReceivableCOASql = "	
												SELECT 
														CN.COMPANY_ID 		AS COM_SAL_REC_COMPANY_ID,
														C.COA_ID 			AS COM_SAL_REC_COA_ID,
														C.CASH_FLOW_HEAD 	AS COM_SAL_REC_CASH_FLOW_HEAD
												FROM 
														gs_coa C,
														c_company CN
												WHERE 
														C.COMPANY_ID  	= CN.COMPANY_ID     
												AND   	C.COA_CODE  	= '302002000'
												ORDER BY 
														C.RGT		
			";
			$companyWiseSalaryReceivableCOA		= $this->tableGateway->getAdapter()->createStatement($companyWiseSalaryReceivableCOASql);
			$companyWiseSalaryReceivableCOA->prepare();
			$companyWiseSalaryReceivableCOAResult = $companyWiseSalaryReceivableCOA->execute();
			
			if ($companyWiseSalaryReceivableCOAResult instanceof ResultInterface && $companyWiseSalaryReceivableCOAResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($companyWiseSalaryReceivableCOAResult);
			}
			
			foreach($resultSet as $companyWiseSalaryReceivableCOA) {
				$COMPANY_SAL_REC_COMPANY_ID			= $companyWiseSalaryReceivableCOA->COM_SAL_REC_COMPANY_ID;
				$COMPANY_SAL_REC_COA_ID				= $companyWiseSalaryReceivableCOA->COM_SAL_REC_COA_ID;
				$COMPANY_SAL_REC_CASH_FLOW_HEAD		= $companyWiseSalaryReceivableCOA->COM_SAL_REC_CASH_FLOW_HEAD;
			}
			//Salary and Allowance Receivable Chart of Account Generate End By Akhand

			$companyCoaData = array();
			$companyCoaData = array(
				"COMPANY_ID"=>array(
										$COMPANY_SAL_PAY_COMPANY_ID,
										$COMPANY_SAL_REC_COMPANY_ID,
									),
				"PARENT_COA"=>array(
										$COMPANY_SAL_PAY_COA_ID,
										$COMPANY_SAL_REC_COA_ID,
									),
				"CASH_FLOW_HEAD"=>array(
										$COMPANY_SAL_PAY_CASH_FLOW_HEAD,
										$COMPANY_SAL_REC_CASH_FLOW_HEAD,
									),
				"COA_CODE"=>array(
										$COMPANY_SAL_PAY_COA_CODE,
										$COMPANY_SAL_REC_COA_CODE,
									),
				"COA_NAME"=>array(
										$COMPANY_SAL_PAY_COA_NAME,
										$COMPANY_SAL_REC_COA_NAME,
									),
				"AUTO_COA"=>array(
										$COMPANY_SAL_PAY_AUTO_COA,
										$COMPANY_SAL_REC_AUTO_COA,
									),
			);			
			
			$id 	= (int) $employeePersonalInfo->EMPLOYEE_ID;
			$data 	= array(
				'EMPLOYEE_TYPE' 	=> $employeePersonalInfo->EMPLOYEE_TYPE,	
				'EMPLOYEE_NAME' 	=> $employeePersonalInfo->EMPLOYEE_NAME,
				'FATHER_NAME' 		=> $employeePersonalInfo->FATHER_NAME,
				'MOTHER_NAME' 		=> $employeePersonalInfo->MOTHER_NAME,
				'DATE_OF_BIRTH' 	=> date("Y-m-d", strtotime($employeePersonalInfo->DATE_OF_BIRTH)),
				'PLACE_OF_BIRTH' 	=> $employeePersonalInfo->PLACE_OF_BIRTH,
				'GENDER' 			=> $employeePersonalInfo->GENDER,
				'BLOOD_GROUP' 		=> $employeePersonalInfo->BLOOD_GROUP,
				'EMPLOYEE_PHOTO' 	=> $employeePersonalInfo->EMPLOYEE_PHOTO,
				'NATIONAL_PHOTO' 	=> $employeePersonalInfo->NATIONAL_PHOTO,
				'RELIGION' 			=> $employeePersonalInfo->RELIGION,
				'MARITAL_STATUS' 	=> $employeePersonalInfo->MARITAL_STATUS,
				'NATIONALITY_ID' 	=> $employeePersonalInfo->NATIONALITY_ID,
				'COUNTRY_ID' 		=> $employeePersonalInfo->COUNTRY_ID,
				'CITY_ID' 			=> $employeePersonalInfo->CITY_ID,
				'PERMANENT_ADDRESS' => $employeePersonalInfo->PERMANENT_ADDRESS,
				'POLICE_STATION' 	=> $employeePersonalInfo->POLICE_STATION,
				'MOBILE_NUMBER' 	=> $employeePersonalInfo->MOBILE_NUMBER,
				'MOBILE_NUMBER' 	=> $employeePersonalInfo->MOBILE_NUMBER,
				'PAYABLE_COA' 		=> $COMPANY_SAL_PAY_COA_CODE,
				'RECEIVABLE_COA' 	=> $COMPANY_SAL_REC_COA_CODE,
				'BUSINESS_DATE' 	=> date("Y-m-d", strtotime($businessDate)),
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
			);
			//echo "<pre>"; print_r($data); die();
			
			if($id == 0) {
				if($this->tableGateway->insert($data)) {
					// Get Max Id Start By Akhand
					$employeeId				= '';
					$getMaxSql 				= "SELECT MAX(EMPLOYEE_ID) AS EMPLOYEE_ID FROM hrms_employee_personal_info";
					$getMaxStatement		= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
					$getMaxStatement->prepare();
					$getMaxResult 			= $getMaxStatement->execute();
					
					if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
						$resultSet 	= new ResultSet();
						$resultSet->initialize($getMaxResult);
					}
					foreach($resultSet as $MAX_ID) {
						$employeeId = $MAX_ID->EMPLOYEE_ID;
					}
					$returnData	= array(
						"EMPLOYEE_ID" 			=> $employeeId,
						"COMPANY_COA_DATA" 		=> $companyCoaData,
					);
					
					return $returnData;
					// Get Max Id End By Akhand
				} else {
					return false;	
				}
			} else {
				$updateData 	= array(
					'EMPLOYEE_TYPE' 	=> $employeePersonalInfo->EMPLOYEE_TYPE,	
					'EMPLOYEE_NAME' 	=> $employeePersonalInfo->EMPLOYEE_NAME,
					'FATHER_NAME' 		=> $employeePersonalInfo->FATHER_NAME,
					'MOTHER_NAME' 		=> $employeePersonalInfo->MOTHER_NAME,
					'DATE_OF_BIRTH' 	=> date("Y-m-d", strtotime($employeePersonalInfo->DATE_OF_BIRTH)),
					'PLACE_OF_BIRTH' 	=> $employeePersonalInfo->PLACE_OF_BIRTH,
					'GENDER' 			=> $employeePersonalInfo->GENDER,
					'BLOOD_GROUP' 		=> $employeePersonalInfo->BLOOD_GROUP,
					'EMPLOYEE_PHOTO' 	=> $employeePersonalInfo->EMPLOYEE_PHOTO,
					'NATIONAL_PHOTO' 	=> $employeePersonalInfo->NATIONAL_PHOTO,
					'RELIGION' 			=> $employeePersonalInfo->RELIGION,
					'MARITAL_STATUS' 	=> $employeePersonalInfo->MARITAL_STATUS,
					'NATIONALITY_ID' 	=> $employeePersonalInfo->NATIONALITY_ID,
					'COUNTRY_ID' 		=> $employeePersonalInfo->COUNTRY_ID,
					'CITY_ID' 			=> $employeePersonalInfo->CITY_ID,
					'PERMANENT_ADDRESS' => $employeePersonalInfo->PERMANENT_ADDRESS,
					'POLICE_STATION' 	=> $employeePersonalInfo->POLICE_STATION,
					'MOBILE_NUMBER' 	=> $employeePersonalInfo->MOBILE_NUMBER,
					'MOBILE_NUMBER' 	=> $employeePersonalInfo->MOBILE_NUMBER,
					'BUSINESS_DATE' 	=> date("Y-m-d", strtotime($businessDate)),
					'RECORD_DATE' 		=> $recDate,
					'OPERATE_BY' 		=> $userId,
				);
				//echo "<pre>"; print_r($updateData); die();
				$BUSINESS_DATE			= date("Y-m-d", strtotime($businessDate));
				$DATE_OF_BIRTH			= date("Y-m-d", strtotime($employeePersonalInfo->DATE_OF_BIRTH));
				
				$updateEmployeeDetailsSql   = "
												UPDATE
														hrms_employee_personal_info
												SET
														EMPLOYEE_TYPE 		= '".$employeePersonalInfo->EMPLOYEE_TYPE."',	
														EMPLOYEE_NAME 		= '".$employeePersonalInfo->EMPLOYEE_NAME."',
														FATHER_NAME 		= '".$employeePersonalInfo->FATHER_NAME."',
														MOTHER_NAME 		= '".$employeePersonalInfo->MOTHER_NAME."',
														DATE_OF_BIRTH 		= '".$DATE_OF_BIRTH."',
														PLACE_OF_BIRTH 		= '".$employeePersonalInfo->PLACE_OF_BIRTH."',
														GENDER 				= '".$employeePersonalInfo->GENDER."',
														BLOOD_GROUP 		= '".$employeePersonalInfo->BLOOD_GROUP."',
														EMPLOYEE_PHOTO 		= '".$employeePersonalInfo->EMPLOYEE_PHOTO."',
														NATIONAL_PHOTO 		= '".$employeePersonalInfo->NATIONAL_PHOTO."',
														RELIGION 			= '".$employeePersonalInfo->RELIGION."',
														MARITAL_STATUS 		= '".$employeePersonalInfo->MARITAL_STATUS."',
														NATIONALITY_ID 		= ".$employeePersonalInfo->NATIONALITY_ID.",
														COUNTRY_ID 			= ".$employeePersonalInfo->COUNTRY_ID.",
														CITY_ID 			= ".$employeePersonalInfo->CITY_ID.",
														PERMANENT_ADDRESS 	= '".$employeePersonalInfo->PERMANENT_ADDRESS."',
														POLICE_STATION 		= '".$employeePersonalInfo->POLICE_STATION."',
														MOBILE_NUMBER 		= ".$employeePersonalInfo->MOBILE_NUMBER.",
														BUSINESS_DATE 		= '".$BUSINESS_DATE."',
														RECORD_DATE 		= '".$recDate."',
														OPERATE_BY 			= ".$userId."
												WHERE
														EMPLOYEE_ID			= ".$id."
				";
				$updateEmployeeDetailsStatement = $this->tableGateway->getAdapter()->createStatement($updateEmployeeDetailsSql);
				$updateEmployeeDetailsStatement->prepare();
				if($updateEmployeeDetailsStatement->execute()) {
					$returnData	= array(
						"EMPLOYEE_ID"	=> $id,
					);
					return $returnData;
				} else {
					return false;	
				}
			}
		}
		
		public function updateEmployeeLoginStatus($EMPLOYEE_ID) {
			$getUpdateSql	= "
								UPDATE
										hrms_employee_personal_info
								SET
										LOGIN_STATUS = 'Y'
								WHERE
										LOGIN_STATUS IS NULL
								AND		EMPLOYEE_ID			= '".$EMPLOYEE_ID."'
			";
			$getUpdateStatement		= $this->tableGateway->getAdapter()->createStatement($getUpdateSql);
			$getUpdateStatement->prepare();
			if($getUpdateStatement->execute()) {
				return true;		
			} else {
				return false;	
			}
		}
		
		public function updateEmployeeJoiningDate($EMPLOYEE_ID,$JOINING_DATE) {
			$JOINING_DATE 		= date("Y-m-d", strtotime($JOINING_DATE));
			$getUpdateSql	= "
								UPDATE
										hrms_employee_personal_info
								SET
										JOINING_DATE = '".$JOINING_DATE."'
								WHERE
										LOGIN_STATUS IS NOT NULL
								AND		EMPLOYEE_ID			= '".$EMPLOYEE_ID."'
			";
			$getUpdateStatement		= $this->tableGateway->getAdapter()->createStatement($getUpdateSql);
			$getUpdateStatement->prepare();
			if($getUpdateStatement->execute()) {
				return true;		
			} else {
				return false;	
			}
		}
		
		
		public function fetchViewSrInfoDetails($cond) {
			$select = "SELECT 
						  empInfo.EMPLOYEE_TYPE,
						  empInfo.EMPLOYEE_NAME,
						  empInfo.FATHER_NAME,
						  empInfo.MOTHER_NAME,
						  empInfo.GENDER,
						  empInfo.BLOOD_GROUP,
						  empInfo.RELIGION,
						  empInfo.BUSINESS_DATE
					
						FROM 
							 hrms_employee_personal_info empInfo, hrms_employee_posting_info empPosting, gs_designation desg
						   WHERE {$cond }
						   AND empInfo.EMPLOYEE_ID = empPosting.EMPLOYEE_ID
						   AND empPosting.DESIGNATION_ID = desg.DESIGNATION_ID
						   AND desg.DESIGNATION_ID = 2
						ORDER BY 
						empInfo.EMPLOYEE_NAME ASC
			";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		
		//Employee Wise Ledger Generate Start By Akhand
		public function getEmployeeLedgerBlance($pDate1,$EMPLOYEE_ID) {
			//echo $pDate1;die();
			$totalLadgerBalance 	= 0.00;
			$date					= date('Y-m-d',strtotime($pDate1));
			
			$totalPayableBalance 	= 0.00;
			$totalReceivableBalance = 0.00;
			$getTblDataSql   		= "
										SELECT 
												COALESCE(SUM(PC.AMOUNT),0)	AS AMOUNT
										FROM 
												hrms_employee_personal_info   	BD,
												a_transaction_master 			PM,
												a_transaction_child  			PC,
												gs_coa    						COA
										WHERE 
												BD.EMPLOYEE_ID 					= '".$EMPLOYEE_ID."'
										AND 	SUBSTR(BD.PAYABLE_COA, 1, 6) 	= '201002'
										AND 	BD.PAYABLE_COA 					= PC.AC_CODE
										AND 	PC.AC_CODE 						= COA.COA_CODE
										AND 	PC.TRAN_NO 						= PM.TRAN_NO
										AND 	LOWER(PC.CBJT) NOT IN ('j')
										AND 	PM.TRAN_DATE 					<= '".$date."'
			";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			
			foreach ($resultSet as $openingBalanceInfoData) {
				$totalPayableBalance = $openingBalanceInfoData['AMOUNT'];
			}
			
			$totalAdjustmentExpense = 0.00;
			$getAdjstExpDataSql   	= " 
										SELECT 
												COALESCE(SUM(PC.AMOUNT),0)	AS EXPENSE
										FROM 
												hrms_employee_personal_info   	BD,
												a_transaction_master 			PM,
												a_transaction_child  			PC,
												gs_coa    						COA
										WHERE 
												BD.EMPLOYEE_ID 					= '".$EMPLOYEE_ID."'
										AND 	SUBSTR(BD.PAYABLE_COA, 1, 6) 	= '201002'
										AND 	BD.PAYABLE_COA 					= PC.AC_CODE
										AND 	PC.AC_CODE 						= COA.COA_CODE
										AND 	PC.TRAN_NO 						= PM.TRAN_NO
										AND 	LOWER(PC.CBJT) IN ('j')
										AND 	PM.TRAN_DATE 					<= '".$date."'
			";
			$getAdjstExpDataStatement = $this->tableGateway->getAdapter()->createStatement($getAdjstExpDataSql);
			$getAdjstExpDataStatement->prepare();
			$getAdjstExpDataResult 	= $getAdjstExpDataStatement->execute();			
			
			if ($getAdjstExpDataResult instanceof ResultInterface && $getAdjstExpDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getAdjstExpDataResult);
			}
			
			foreach ($resultSet as $totalAdjustmentExpenseData) {
				$totalAdjustmentExpense = $totalAdjustmentExpenseData['EXPENSE'];
			}
			// Set $tbsAmount = 0;
			
			$totalLadgerBalance = $totalPayableBalance - $totalAdjustmentExpense;
			return $totalLadgerBalance;
		}
		
		public function fetchEmployeeLedgerPaidAmountDate($EMPLOYEE_ID,$LEDGER_DATE_FORM,$LEDGER_DATE_TO) {	
			$LEDGER_DATE_FORM	= date('Y-m-d',strtotime($LEDGER_DATE_FORM));
			$LEDGER_DATE_TO		= date('Y-m-d',strtotime($LEDGER_DATE_TO));
			
			$getTblDataSql   = "
								SELECT 
										PM.TRAN_DATE
								FROM 
										hrms_employee_personal_info   	BD,
										a_transaction_master 			PM,
										a_transaction_child  			PC,
										gs_coa    						COA
								WHERE 
										BD.EMPLOYEE_ID 					= '".$EMPLOYEE_ID."'
								AND 	SUBSTR(BD.PAYABLE_COA, 1, 6) 	= '201002'
								AND 	BD.PAYABLE_COA 					= PC.AC_CODE
								AND 	PC.AC_CODE 						= COA.COA_CODE
								AND 	PC.TRAN_NO 						= PM.TRAN_NO
								AND 	LOWER(PC.CBJT) NOT IN ('j')
								AND PM.TRAN_DATE BETWEEN '".$LEDGER_DATE_FORM."' AND '".$LEDGER_DATE_TO."'
								
								UNION
								SELECT 
										PM.TRAN_DATE
								FROM 
										hrms_employee_personal_info   	BD,
										a_transaction_master 			PM,
										a_transaction_child  			PC,
										gs_coa    						COA
								WHERE 
										BD.EMPLOYEE_ID 					= '".$EMPLOYEE_ID."'
								AND 	SUBSTR(BD.PAYABLE_COA, 1, 6) 	= '201002'
								AND 	BD.PAYABLE_COA 					= PC.AC_CODE
								AND 	PC.AC_CODE 						= COA.COA_CODE
								AND 	PC.TRAN_NO 						= PM.TRAN_NO
								AND 	LOWER(PC.CBJT) IN ('j')
								AND PM.TRAN_DATE BETWEEN '".$LEDGER_DATE_FORM."' AND '".$LEDGER_DATE_TO."'
			";
			$getTblDataStatement	= $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 		= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function fetchEmployeeLedgerPaidAmount($EMPLOYEE_ID,$individualDate) {	
			$getTblDataSql   = "
								SELECT 
										BD.EMPLOYEE_NAME,
										BD.PAYABLE_COA,
										PC.AMOUNT,
										PM.TRAN_DATE,
										PC.NTR,
										PC.NARRATION
								FROM 
										hrms_employee_personal_info   	BD,
										a_transaction_master 			PM,
										a_transaction_child  			PC,
										gs_coa    						COA
								WHERE 
										BD.EMPLOYEE_ID 					= '".$EMPLOYEE_ID."'
								AND 	SUBSTR(BD.PAYABLE_COA, 1, 6) 	= '201002'
								AND 	BD.PAYABLE_COA 					= PC.AC_CODE
								AND 	PC.AC_CODE 						= COA.COA_CODE
								AND 	PC.TRAN_NO 						= PM.TRAN_NO
								AND 	PC.CBJT NOT IN ('J')
								AND 	PM.TRAN_DATE					= '".$individualDate."'
								
								UNION
								SELECT 
										BD.EMPLOYEE_NAME,
										BD.PAYABLE_COA,
										PC.AMOUNT,
										PM.TRAN_DATE,
										PC.NTR,
										PC.NARRATION
								FROM 
										hrms_employee_personal_info   	BD,
										a_transaction_master 			PM,
										a_transaction_child  			PC,
										gs_coa    						COA
								WHERE 
										BD.EMPLOYEE_ID 					= '".$EMPLOYEE_ID."'
								AND 	SUBSTR(BD.PAYABLE_COA, 1, 6) 	= '201002'
								AND 	BD.PAYABLE_COA 					= PC.AC_CODE
								AND 	PC.AC_CODE 						= COA.COA_CODE
								AND 	PC.TRAN_NO 						= PM.TRAN_NO
								AND 	PC.CBJT IN ('J')
								AND 	PM.TRAN_DATE					= '".$individualDate."'
			";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		//Employee Wise Ledger Generate End By Akhand
		public function fetchSRListforLedger($input,$cond) {
			$getTblDataSql   = "SELECT 
										empInfo.EMPLOYEE_ID,
										empInfo.EMPLOYEE_NAME AS NAME
								FROM 
										hrms_employee_personal_info empInfo
								WHERE	LOWER(empInfo.EMPLOYEE_NAME) like '".$input."%' {$cond}
								ORDER BY LOWER(empInfo.EMPLOYEE_NAME) ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchSRListforLedgerList($input='') {
			if(!empty($input)){
				$getTblDataSql   = "SELECT 
										empInfo.EMPLOYEE_ID,
										empInfo.EMPLOYEE_NAME AS NAME
								FROM 
										hrms_employee_personal_info empInfo
								WHERE	LOWER(empInfo.EMPLOYEE_NAME) = '".$input."'
								ORDER BY LOWER(empInfo.EMPLOYEE_NAME) ASC";
			}else{
				$getTblDataSql   = "SELECT 
										empInfo.EMPLOYEE_ID,
										empInfo.EMPLOYEE_NAME AS NAME
								FROM 
										hrms_employee_personal_info empInfo
							
								ORDER BY LOWER(empInfo.EMPLOYEE_NAME) ASC";
			}
			
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchSRInfoForLedger($empID) {
			if(empty($empID)){
				$em ='';
			}else{
				$em = 'WHERE empInfo.EMPLOYEE_ID = '.$empID.'';
			}
			$select = "SELECT 
						  empInfo.EMPLOYEE_TYPE,
						  empInfo.PAYABLE_COA,
						  empInfo.EMPLOYEE_NAME,
						  empInfo.EMPLOYEE_ID,
						  empInfo.RECEIVABLE_COA,					
						  empInfo.MOBILE_NUMBER					
						FROM 
							 hrms_employee_personal_info empInfo
							 {$em}
			";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		public function fetchSRInfoRetailerEdit($retailerID) {
			$select = "SELECT 
						  empPosting.DESIGNATION_ID,
						  desg.DESIGNATION AS DESIGNATION_NAME,
						  empInfo.EMPLOYEE_NAME,
						  empInfo.EMPLOYEE_ID			
						FROM 
							 ls_retailer_info retInfo, hrms_employee_personal_info empInfo,hrms_employee_posting_info empPosting, gs_designation desg
						WHERE retInfo.RETAILER_ID = {$retailerID}
						AND	empInfo.EMPLOYEE_ID = retInfo.EMPLOYEE_ID
						AND empPosting.EMPLOYEE_ID = retInfo.EMPLOYEE_ID
						AND desg.DESIGNATION_ID = empPosting.DESIGNATION_ID
						AND empPosting.END_DATE is NULL
						";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			$data = array();
			foreach($resultSet as $row) {
				$data[] = array(
								'DESIGNATION_ID' 	=> $row['DESIGNATION_ID'],
								'DESIGNATION_NAME' 	=> $row['DESIGNATION_NAME'],
								'EMPLOYEE_NAME' 	=> $row['EMPLOYEE_NAME'],
								'EMPLOYEE_ID'		=> $row['EMPLOYEE_ID'],
							);
			}
			return $data;
		}
		public function getSRWiseRETList($id) {
			$select = "		
						SELECT 
								ls_sr_retailer_map.SR_RETAILER_MAP_ID,
								ls_sr_retailer_map.RETAILER_ID,
								ls_retailer_info.SHOP_NAME,
								ls_retailer_info.PAYABLE_COA,
								ls_retailer_info.RECEIVABLE_COA,
								ls_retailer_info.MOBILE,
								ls_retailer_info.NAME,
								ls_retailer_info.ADDRESS,
								ls_zone_info.SHORT_NAME AS ZONESHORTNAME
						FROM 
								ls_retailer_info,ls_sr_retailer_map,ls_zone_info
						WHERE	ls_sr_retailer_map.EMPLOYEE_ID= '".$id."'
						AND		ls_retailer_info.RETAILER_ID = ls_sr_retailer_map.RETAILER_ID
						AND		ls_sr_retailer_map.END_DATE = '0000-00-00'
						AND		ls_zone_info.ZONE_ID = ls_retailer_info.ZONE_ID
						ORDER BY 
								ls_retailer_info.RETAILER_ID ASC
			";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		public function transectionStart() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
		}
		
		public function transectionEnd() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->commit();
		}
		
		public function transectionInterrupted() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->rollback();
		}
	}
?>	