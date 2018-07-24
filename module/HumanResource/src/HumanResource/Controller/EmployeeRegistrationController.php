<?php	
	namespace HumanResource\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use HumanResource\Model\EmployeePersonalInfo;
	use HumanResource\Model\EmployeeSpouseInfo;
	use HumanResource\Model\EmployeeContactInfo;
	use HumanResource\Model\EmployeeEducationInfo;
	use HumanResource\Model\EmployeePostingInfo;
	use HumanResource\Model\EmployeeSalaryInfo;
	use GlobalSetting\Model\Coa;
	
	use HumanResource\Form\EmployeeRegistrationForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class EmployeeRegistrationController extends AbstractActionController {
		protected $employeePersonalInfoTable;
		protected $employeeSpouseInfoTable;
		protected $employeeContactInfoTable;
		protected $employeeEducationInfoTable;
		protected $employeePostingInfoTable;
		protected $employeeSalaryInfoTable;
		protected $nationalityTable;
		protected $countryTable;
		protected $cityTable;
		protected $occupationTable;
		protected $designationTable;
		protected $branchTable;
		protected $coaTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getEmployeePersonalInfoTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			$select 	= new Select();
			$order_by 	= $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id'; 
			$order 		= $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
			$select->order($order_by . ' ' . $order);
			
			return new ViewModel(array(
				'investorprofiles' 	=> $paginator,
				'order_by' 			=> $order_by,
				'order' 			=> $order,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 			= $this->getRequest();			
			$form 				= new EmployeeRegistrationForm('employeeregistration', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			
			if($request->isPost()) {
				$postedData		= $request->getPost();
				//echo "<pre>"; print_r($postedData); die();
				
				$this->getEmployeePersonalInfoTable()->transectionStart();
				// Employee Personal Info Data Insert Start By Akhand
				$EMPLOYEE_PHOTO 	= $_FILES['EMPLOYEE_PHOTO']['name'];
				$NATIONAL_PHOTO 	= $_FILES['NATIONAL_PHOTO']['name'];
				$EDU_PHOTO 			= $_FILES['EDU_PHOTO']['name'];
				$success		= 0;
				
				$size 		= new \Zend\Validator\File\Size(array('max'=>100485760));
				$adapter 	= new \Zend\File\Transfer\Adapter\Http();
				$adapter1 	= new \Zend\File\Transfer\Adapter\Http();
				$adapter2 	= new \Zend\File\Transfer\Adapter\Http();
				$adapter->setValidators(array($size), $EMPLOYEE_PHOTO);
				$adapter1->setValidators(array($size), $NATIONAL_PHOTO);
				$adapter2->setValidators(array($size), $EDU_PHOTO);
				$adapter->setDestination('public/uploaddir/empphoto/');
				$adapter1->setDestination('public/uploaddir/document/');
				$adapter2->setDestination('public/uploaddir/education/');
				if($adapter->receive($EMPLOYEE_PHOTO) AND $adapter1->receive($NATIONAL_PHOTO)  AND $adapter2->receive($EDU_PHOTO)) {
					$success	= 1;
				} else {
					$success	= 0;
				}
				
				$employeePersonalInfo		= new EmployeePersonalInfo();
				$employeeSpouseInfo			= new EmployeeSpouseInfo();
				$employeeContactInfo		= new EmployeeContactInfo();
				$employeeEducationInfo		= new EmployeeEducationInfo();
				$employeePostingInfo		= new EmployeePostingInfo();
				$employeeSalaryInfo			= new EmployeeSalaryInfo();
				
				$employeePersonalData		= array();
				$employeeSpouseData			= array();
				$employeeContactData		= array();
				$employeeEducationData		= array();
				$employeePostingData		= array();
				$employeeSalaryData			= array();
				
				$success					= false;
				
				$DATE_OF_BIRTH 	= date("d-M-Y", strtotime($postedData["DATE_OF_BIRTH_D"]."-".$postedData["DATE_OF_BIRTH_M"]."-".$postedData["DATE_OF_BIRTH_Y"]));
			
				$employeePersonalData		= array(
					'EMPLOYEE_TYPE' 	=> $postedData["EMPLOYEE_TYPE"],		
					'EMPLOYEE_NAME' 	=> $postedData["EMPLOYEE_NAME"],
					'FATHER_NAME' 		=> $postedData["FATHER_NAME"],
					'MOTHER_NAME' 		=> $postedData["MOTHER_NAME"],
					'DATE_OF_BIRTH' 	=> $DATE_OF_BIRTH,
					'PLACE_OF_BIRTH' 	=> $postedData["PLACE_OF_BIRTH"],
					'GENDER' 			=> $postedData["GENDER"],
					'BLOOD_GROUP' 		=> $postedData["BLOOD_GROUP"],
					'EMPLOYEE_PHOTO' 	=> $EMPLOYEE_PHOTO,
					'NATIONAL_PHOTO' 	=> $NATIONAL_PHOTO,
					'RELIGION' 			=> $postedData["RELIGION"],
					'MARITAL_STATUS' 	=> $postedData["MARITAL_STATUS"],
					'NATIONALITY_ID' 	=> $postedData["NATIONALITY_ID"],
					'COUNTRY_ID' 		=> $postedData["COUNTRY_ID"],
					'CITY_ID' 			=> $postedData["CITY_ID"],
					'PERMANENT_ADDRESS' => $postedData["PERMANENT_ADDRESS"],
					'POLICE_STATION' 	=> $postedData["POLICE_STATION"],
					'MOBILE_NUMBER' 	=> $postedData["MOBILE_NUMBER"],
				);
				$employeePersonalInfo->exchangeArray($employeePersonalData);
				if($returnData	= $this->getEmployeePersonalInfoTable()->saveEmployeePersonalInfo($employeePersonalInfo)) {
					$EMPLOYEE_ID	= $returnData['EMPLOYEE_ID'];
					
					// Employee Spouse Info Data Insert Start By Akhand
					if($postedData["MARITAL_STATUS"] == 'Married') {
						$employeeSpouseData		= array(
							'EMPLOYEE_ID' 		=> $EMPLOYEE_ID,		
							'SPOUSE_NAME' 		=> $postedData["SPOUSE_NAME"],
							'OCCUPATION_ID' 	=> $postedData["OCCUPATION_ID"],
							'SPOUSE_ADDRESS' 	=> $postedData["SPOUSE_ADDRESS"],
						);
						$employeeSpouseInfo->exchangeArray($employeeSpouseData);
						if($this->getEmployeeSpouseInfoTable()->saveEmployeeSpouseInfo($employeeSpouseInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Spouse Info Data Insert End By Akhand
					
					// Employee Contact Info Data Insert Start By Akhand
					if($success) {
						$employeeContactData		= array(
							'EMPLOYEE_ID' 				=> $EMPLOYEE_ID,		
							'CONTACT_ADDRESS_FIRST' 	=> $postedData["CONTACT_ADDRESS_FIRST"],
							'CONTACT_ADDRESS_SECOND' 	=> $postedData["CONTACT_ADDRESS_SECOND"],
							'TELEPHONE' 				=> $postedData["TELEPHONE"],
							'EMAIL_ADDRESS' 			=> $postedData["EMAIL_ADDRESS"],
						);
						$employeeContactInfo->exchangeArray($employeeContactData);
						if($this->getEmployeeContactInfoTable()->saveEmployeeContactInfo($employeeContactInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Contact Info Data Insert End By Akhand
					
					// Employee Education Info Data Insert Start By Akhand
					if($success) {
						$employeeEducationData		= array(
							'EMPLOYEE_ID' 			=> $EMPLOYEE_ID,		
							'LAST_CERTIFICATE' 		=> $postedData["LAST_CERTIFICATE"],
							'INSTITUTE_NAME' 		=> $postedData["INSTITUTE_NAME"],
							'PASSING_YEAR' 			=> $postedData["PASSING_YEAR"],
							'MARKS_OBTAIN' 			=> $postedData["MARKS_OBTAIN"],
							'CLASS_DIVISION' 		=> $postedData["CLASS_DIVISION"],
							'EDU_PHOTO' 			=> $EDU_PHOTO,
						);
						$employeeEducationInfo->exchangeArray($employeeEducationData);
						if($this->getEmployeeEducationInfoTable()->saveEmployeeEducationInfo($employeeEducationInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Education Info Data Insert End By Akhand
					
					// Employee Posting Info Data Insert Start By Akhand
					if($success) {
						$employeePostingData		= array(
							'EMPLOYEE_ID' 			=> $EMPLOYEE_ID,		
							'DESIGNATION_ID' 		=> $postedData["DESIGNATION_ID"],
							'BRANCH_ID' 			=> $postedData["BRANCH_ID"],
							'DIVISION_NAME' 		=> $postedData["DIVISION_NAME"],
						);
						$employeePostingInfo->exchangeArray($employeePostingData);
						if($this->getEmployeePostingInfoTable()->saveEmployeePostingInfo($employeePostingInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Posting Info Data Insert End By Akhand
					
					// Employee Salary Info Data Insert Start By Akhand
					if($success) {
						$employeeSalaryData		= array(
							'EMPLOYEE_ID' 			=> $EMPLOYEE_ID,		
							'SALARY_AMOUNT' 		=> $postedData["SALARY_AMOUNT"],
						);
						$employeeSalaryInfo->exchangeArray($employeeSalaryData);
						if($this->getEmployeeSalaryInfoTable()->saveEmployeeSalaryInfo($employeeSalaryInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Salary Info Data Insert End By Akhand
					
					// Employee Chart of Account Entry Start By Akhand
					if($success) {
						
						$coa		= new Coa();
						$success	= false;
						foreach($returnData['COMPANY_COA_DATA']['COA_CODE'] as $index=>$cCoaCode) {
							$instrumentCoaData = array(
														'COMPANY_ID' 		=> $returnData['COMPANY_COA_DATA']['COMPANY_ID'][$index],
														'PARENT_COA'		=> $returnData['COMPANY_COA_DATA']['PARENT_COA'][$index],
														'COA_NAME' 			=> $returnData['COMPANY_COA_DATA']['COA_NAME'][$index],
														'COA_CODE' 			=> $cCoaCode,
														'AUTO_COA' 			=> $returnData['COMPANY_COA_DATA']['AUTO_COA'][$index],
														'CASH_FLOW_HEAD' 	=> $returnData['COMPANY_COA_DATA']['CASH_FLOW_HEAD'][$index],
													);
							
							$coa->exchangeArray($instrumentCoaData);
							$success = $this->getCoaTable()->saveCoa($coa);
							if($success) {
								$success	= true;
								continue;
							} else {
								$success	= false;
								break;
							}
						}
					} 
					// Employee Chart of Account Entry End By Akhand
				} else {
					$success	= false;
					break;
				}
				// Employee Personal Info Data Insert End By Akhand
				
				// Finally Suuccess Message Start By Akhand
				if($success) {
					$this->getEmployeePersonalInfoTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Employee registration saved successfully!</h4></td>
																</tr>
															</table>");
					return $this->redirect()->toRoute('employeeregistration');	
				} else {
					$this->getEmployeePersonalInfoTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='error_msg'>
																<td colspan='3' style='text-align:center;'><h4>Employee registration couldn't save properly!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('employeeregistration');
				}
				// Finally Suuccess Message End By Akhand
			}
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request = $this->getRequest();
		   	$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('employeeregistration',array('action' => 'add'));
			}
			
			try {
				$NATIONALITY_DATA	= $this->getNationalityTable()->getNationalityForSelect();
				$NATIONALITY_ARRAY	= array();
				foreach($NATIONALITY_DATA as $NATIONALITY_VALUE) {
					$NATIONALITY_ARRAY [] = array(
						"NATIONALITY_ID"	=> $NATIONALITY_VALUE->NATIONALITY_ID,
						"NATIONALITY"		=> $NATIONALITY_VALUE->NATIONALITY,	
					);	
				}
				
				$COUNTRY_DATA	= $this->getCountryTable()->getCountryForSelect();
				$COUNTRY_ARRAY	= array();
				foreach($COUNTRY_DATA as $COUNTRY_VALUE) {
					$COUNTRY_ARRAY [] = array(
						"COUNTRY_ID"	=> $COUNTRY_VALUE->COUNTRY_ID,
						"COUNTRY"		=> $COUNTRY_VALUE->COUNTRY,	
					);	
				}
				
				$CITY_DATA	= $this->getCityTable()->getCityForSelect();
				$CITY_ARRAY	= array();
				foreach($CITY_DATA as $CITY_VALUE) {
					$CITY_ARRAY [] = array(
						"CITY_ID"	=> $CITY_VALUE->CITY_ID,
						"CITY"		=> $CITY_VALUE->CITY,	
					);	
				}
				
				$OCCUPATION_DATA	= $this->getOccupationTable()->getOccupationForSelect();
				$OCCUPATION_ARRAY	= array();
				foreach($OCCUPATION_DATA as $OCCUPATION_VALUE) {
					$OCCUPATION_ARRAY [] = array(
						"OCCUPATION_ID"	=> $OCCUPATION_VALUE->OCCUPATION_ID,
						"OCCUPATION"	=> $OCCUPATION_VALUE->OCCUPATION,	
					);	
				}
				
				$DESIGNATION_DATA	= $this->getDesignationTable()->getDesignationForSelect();
				$DESIGNATION_ARRAY	= array();
				foreach($DESIGNATION_DATA as $DESIGNATION_VALUE) {
					$DESIGNATION_ARRAY [] = array(
						"DESIGNATION_ID"	=> $DESIGNATION_VALUE->DESIGNATION_ID,
						"DESIGNATION"		=> $DESIGNATION_VALUE->DESIGNATION,	
					);	
				}
				
				$BRANCH_DATA	= $this->getBranchTable()->getBranchForSelect();
				$BRANCH_ARRAY	= array();
				foreach($BRANCH_DATA as $BRANCH_VALUE) {
					$BRANCH_ARRAY [] = array(
						"BRANCH_ID"		=> $BRANCH_VALUE->BRANCH_ID,
						"BRANCH_NAME"	=> $BRANCH_VALUE->BRANCH_NAME,	
					);	
				}				
				//echo '<pre>'; print_r($BRANCH_ARRAY); echo '</pre>'; die();
				
				$employeeProfile = $this->getEmployeePersonalInfoTable()->getEmployeePersonalInfo($id);
				//echo '<pre>'; print_r($employeeProfile); echo '</pre>'; die();
				foreach ($employeeProfile as $selectOption) {
					$data[] = array(
						'EMPLOYEE_ID' 				=> $selectOption->EMPLOYEE_ID,
						'EMPLOYEE_TYPE' 			=> $selectOption->EMPLOYEE_TYPE,	
						'EMPLOYEE_NAME' 			=> $selectOption->EMPLOYEE_NAME,
						'FATHER_NAME' 				=> $selectOption->FATHER_NAME,
						'MOTHER_NAME' 				=> $selectOption->MOTHER_NAME,
						'DATE_OF_BIRTH' 			=> $selectOption->DATE_OF_BIRTH,
						'PLACE_OF_BIRTH' 			=> $selectOption->PLACE_OF_BIRTH,
						'GENDER' 					=> $selectOption->GENDER,
						'BLOOD_GROUP' 				=> $selectOption->BLOOD_GROUP,
						'EMPLOYEE_PHOTO' 			=> $selectOption->EMPLOYEE_PHOTO,
						'NATIONAL_PHOTO' 			=> $selectOption->NATIONAL_PHOTO,
						'RELIGION' 					=> $selectOption->RELIGION,
						'MARITAL_STATUS' 			=> $selectOption->MARITAL_STATUS,
						'NATIONALITY_ID' 			=> $selectOption->NATIONALITY_ID,
						'COUNTRY_ID' 				=> $selectOption->COUNTRY_ID,
						'CITY_ID' 					=> $selectOption->CITY_ID,
						'PERMANENT_ADDRESS' 		=> $selectOption->PERMANENT_ADDRESS,
						'POLICE_STATION' 			=> $selectOption->POLICE_STATION,
						'MOBILE_NUMBER' 			=> $selectOption->MOBILE_NUMBER,
						'EMPLOYEE_CONTACT_ID' 		=> $selectOption->EMPLOYEE_CONTACT_ID,
						'CONTACT_ADDRESS_FIRST' 	=> $selectOption->CONTACT_ADDRESS_FIRST,
						'CONTACT_ADDRESS_SECOND' 	=> $selectOption->CONTACT_ADDRESS_SECOND,
						'TELEPHONE' 				=> $selectOption->TELEPHONE,
						'EMAIL_ADDRESS' 			=> $selectOption->EMAIL_ADDRESS,
						'EMPLOYEE_EDUCATION_ID' 	=> $selectOption->EMPLOYEE_EDUCATION_ID,
						'LAST_CERTIFICATE' 			=> $selectOption->LAST_CERTIFICATE,
						'INSTITUTE_NAME' 			=> $selectOption->INSTITUTE_NAME,
						'PASSING_YEAR' 				=> $selectOption->PASSING_YEAR,
						'MARKS_OBTAIN' 				=> $selectOption->MARKS_OBTAIN,
						'EDU_PHOTO' 				=> $selectOption->EDU_PHOTO,
						'CLASS_DIVISION' 			=> $selectOption->CLASS_DIVISION,
						'EMPLOYEE_POSTING_ID' 		=> $selectOption->EMPLOYEE_POSTING_ID,
						'DESIGNATION_ID' 			=> $selectOption->DESIGNATION_ID,
						'BRANCH_ID' 				=> $selectOption->BRANCH_ID,
						'DIVISION_NAME' 			=> $selectOption->DIVISION_NAME,
						'EMPLOYEE_SALARY_ID' 		=> $selectOption->EMPLOYEE_SALARY_ID,
						'SALARY_AMOUNT' 			=> $selectOption->SALARY_AMOUNT,
						'EMPLOYEE_SPOUSE_ID' 		=> $selectOption->EMPLOYEE_SPOUSE_ID,
						'SPOUSE_NAME' 				=> $selectOption->SPOUSE_NAME,
						'OCCUPATION_ID' 			=> $selectOption->OCCUPATION_ID,
						'SPOUSE_ADDRESS' 			=> $selectOption->SPOUSE_ADDRESS,
					);
				}
				//echo '<pre>'; print_r($data); echo '</pre>'; die();
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('employeeregistration', array('action' => 'index'));
			}
			
			$request 			= $this->getRequest();			
			$form 				= new EmployeeRegistrationForm('employeeregistration', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Edit');
			
			if($request->isPost()) {
				$postedData		= $request->getPost();
				//echo "<pre>"; print_r($postedData);  die();
				
				$this->getEmployeePersonalInfoTable()->transectionStart();
				
				$EMPLOYEE_PHOTO 	= $_FILES['EMPLOYEE_PHOTO']['name'];
				$NATIONAL_PHOTO 	= $_FILES['NATIONAL_PHOTO']['name'];
				$EDU_PHOTO 			= $_FILES['EDU_PHOTO']['name'];
				$success		= 0;
				$size 		= new \Zend\Validator\File\Size(array('max'=>100485760));
				if(!empty($EMPLOYEE_PHOTO)) {
					$adapter 	= new \Zend\File\Transfer\Adapter\Http();
					$adapter->setValidators(array($size), $EMPLOYEE_PHOTO);
					$adapter->setDestination('public/uploaddir/empphoto/');
					if($adapter->receive($EMPLOYEE_PHOTO)) {
						$success	= 1;
					} else {
						$success	= 0;
					}
				}else{
					$EMPLOYEE_PHOTO	= $postedData["PREVIOUS_EMPLOYEE_PHOTO"];
				}
				if(!empty($NATIONAL_PHOTO)) {
					$adapter 	= new \Zend\File\Transfer\Adapter\Http();
					$adapter1 	= new \Zend\File\Transfer\Adapter\Http();
					$adapter1->setValidators(array($size), $NATIONAL_PHOTO);
					$adapter1->setDestination('public/uploaddir/document/');
					
					if($adapter1->receive($NATIONAL_PHOTO)) {
						$success	= 1;
					} else {
						$success	= 0;
					}
				}else{
					$NATIONAL_PHOTO	= $postedData["PREVIOUS_NATIONAL_PHOTO"];
				}
				if(!empty($EDU_PHOTO)) {
					$adapter2 	= new \Zend\File\Transfer\Adapter\Http();
					$adapter2->setValidators(array($size), $EDU_PHOTO);
					$adapter2->setDestination('public/uploaddir/education/');
					if($adapter2->receive($EDU_PHOTO)) {
						$success	= 1;
					} else {
						$success	= 0;
					}
				}else{
					$EDU_PHOTO	= $postedData["PREVIOUS_EDU_PHOTO"];
				}
				$employeePersonalInfo		= new EmployeePersonalInfo();
				$employeeSpouseInfo			= new EmployeeSpouseInfo();
				$employeeContactInfo		= new EmployeeContactInfo();
				$employeeEducationInfo		= new EmployeeEducationInfo();
				$employeePostingInfo		= new EmployeePostingInfo();
				$employeeSalaryInfo			= new EmployeeSalaryInfo();
				
				$employeePersonalData		= array();
				$employeeSpouseData			= array();
				$employeeContactData		= array();
				$employeeEducationData		= array();
				$employeePostingData		= array();
				$employeeSalaryData			= array();
				
				$success					= false;
				
				$DATE_OF_BIRTH 	= date("d-M-Y", strtotime($postedData["DATE_OF_BIRTH_D"]."-".$postedData["DATE_OF_BIRTH_M"]."-".$postedData["DATE_OF_BIRTH_Y"]));
			
				$employeePersonalData		= array(
					'EMPLOYEE_ID' 		=> $postedData["EMPLOYEE_ID"],
					'EMPLOYEE_TYPE' 	=> $postedData["EMPLOYEE_TYPE"],		
					'EMPLOYEE_NAME' 	=> $postedData["EMPLOYEE_NAME"],
					'FATHER_NAME' 		=> $postedData["FATHER_NAME"],
					'MOTHER_NAME' 		=> $postedData["MOTHER_NAME"],
					'DATE_OF_BIRTH' 	=> $DATE_OF_BIRTH,
					'PLACE_OF_BIRTH' 	=> $postedData["PLACE_OF_BIRTH"],
					'GENDER' 			=> $postedData["GENDER"],
					'BLOOD_GROUP' 		=> $postedData["BLOOD_GROUP"],
					'EMPLOYEE_PHOTO' 	=> $EMPLOYEE_PHOTO,
					'NATIONAL_PHOTO' 	=> $NATIONAL_PHOTO,
					'RELIGION' 			=> $postedData["RELIGION"],
					'MARITAL_STATUS' 	=> $postedData["MARITAL_STATUS"],
					'NATIONALITY_ID' 	=> $postedData["NATIONALITY_ID"],
					'COUNTRY_ID' 		=> $postedData["COUNTRY_ID"],
					'CITY_ID' 			=> $postedData["CITY_ID"],
					'PERMANENT_ADDRESS' => $postedData["PERMANENT_ADDRESS"],
					'POLICE_STATION' 	=> $postedData["POLICE_STATION"],
					'MOBILE_NUMBER' 	=> $postedData["MOBILE_NUMBER"],
				);
				$employeePersonalInfo->exchangeArray($employeePersonalData);
				if($returnData	= $this->getEmployeePersonalInfoTable()->saveEmployeePersonalInfo($employeePersonalInfo)) {
					$EMPLOYEE_ID	= $returnData['EMPLOYEE_ID'];
					
					// Employee Spouse Info Data Insert Start By Akhand
					if($postedData["MARITAL_STATUS"] == 'Married') {
						$employeeSpouseData		= array(
							'EMPLOYEE_ID' 			=> $EMPLOYEE_ID,		
							'EMPLOYEE_SPOUSE_ID'	=> $postedData["EMPLOYEE_SPOUSE_ID"],
							'SPOUSE_NAME' 			=> $postedData["SPOUSE_NAME"],
							'OCCUPATION_ID' 		=> $postedData["OCCUPATION_ID"],
							'SPOUSE_ADDRESS' 		=> $postedData["SPOUSE_ADDRESS"],
						);
						$employeeSpouseInfo->exchangeArray($employeeSpouseData);
						if($this->getEmployeeSpouseInfoTable()->saveEmployeeSpouseInfo($employeeSpouseInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Spouse Info Data Insert End By Akhand
					
					// Employee Contact Info Data Insert Start By Akhand
					if($success) {
						$employeeContactData		= array(
							'EMPLOYEE_ID' 				=> $EMPLOYEE_ID,
							'EMPLOYEE_CONTACT_ID'		=> $postedData["EMPLOYEE_CONTACT_ID"],		
							'CONTACT_ADDRESS_FIRST' 	=> $postedData["CONTACT_ADDRESS_FIRST"],
							'CONTACT_ADDRESS_SECOND' 	=> $postedData["CONTACT_ADDRESS_SECOND"],
							'TELEPHONE' 				=> $postedData["TELEPHONE"],
							'EMAIL_ADDRESS' 			=> $postedData["EMAIL_ADDRESS"],
						);
						$employeeContactInfo->exchangeArray($employeeContactData);
						if($this->getEmployeeContactInfoTable()->saveEmployeeContactInfo($employeeContactInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Contact Info Data Insert End By Akhand
					
					// Employee Education Info Data Insert Start By Akhand
					if($success) {
						$employeeEducationData		= array(
							'EMPLOYEE_ID' 			=> $EMPLOYEE_ID,
							'EMPLOYEE_EDUCATION_ID'	=> $postedData["EMPLOYEE_EDUCATION_ID"],		
							'LAST_CERTIFICATE' 		=> $postedData["LAST_CERTIFICATE"],
							'INSTITUTE_NAME' 		=> $postedData["INSTITUTE_NAME"],
							'PASSING_YEAR' 			=> $postedData["PASSING_YEAR"],
							'MARKS_OBTAIN' 			=> $postedData["MARKS_OBTAIN"],
							'CLASS_DIVISION' 		=> $postedData["CLASS_DIVISION"],
							'EDU_PHOTO' 			=> $EDU_PHOTO,
						);
						$employeeEducationInfo->exchangeArray($employeeEducationData);
						if($this->getEmployeeEducationInfoTable()->saveEmployeeEducationInfo($employeeEducationInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Education Info Data Insert End By Akhand
					
					// Employee Posting Info Data Insert Start By Akhand
					if($success) {
						$employeePostingData		= array(
							'EMPLOYEE_ID' 			=> $EMPLOYEE_ID,
							'EMPLOYEE_POSTING_ID'	=> $postedData["EMPLOYEE_POSTING_ID"],		
							'DESIGNATION_ID' 		=> $postedData["DESIGNATION_ID"],
							'BRANCH_ID' 			=> $postedData["BRANCH_ID"],
							'DIVISION_NAME' 		=> $postedData["DIVISION_NAME"],
						);
						$employeePostingInfo->exchangeArray($employeePostingData);
						if($this->getEmployeePostingInfoTable()->saveEmployeePostingInfo($employeePostingInfo)) {
							$success	= true;
						} else {
							$success	= false;
							break;
						}	
					} else {
						$success	= true;	
					}
					// Employee Posting Info Data Insert End By Akhand
					
					// Employee Salary Info Data Insert Start By Akhand
					if($success) {
						$employeeSalaryData		= array(
							'EMPLOYEE_ID' 			=> $EMPLOYEE_ID,		
							'EMPLOYEE_SALARY_ID'	=> $postedData["EMPLOYEE_SALARY_ID"],
							'SALARY_AMOUNT' 		=> $postedData["SALARY_AMOUNT"],
						);
						$employeeSalaryInfo->exchangeArray($employeeSalaryData);
						if($this->getEmployeeSalaryInfoTable()->saveEmployeeSalaryInfo($employeeSalaryInfo)) {
							$success	= true;
						} else {
							$success	= false;
						}	
					} else {
						$success	= true;	
					}
					// Employee Salary Info Data Insert End By Akhand
					
				} else {
					$success	= false;
				}
				// Employee Personal Info Data Insert End By Akhand
				
				// Finally Suuccess Message Start By Akhand
				if($success) {
					$this->getEmployeePersonalInfoTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Employee registration edit successfully!</h4></td>
																</tr>
															</table>");
					return $this->redirect()->toRoute('employeeregistration');	
				} else {
					$this->getEmployeePersonalInfoTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='error_msg'>
																<td colspan='3' style='text-align:center;'><h4>Employee registration couldn't edit properly!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('employeeregistration');
				}
				// Finally Suuccess Message End By Akhand
			}
			return array(
				'id' 				=> $id,
				'form' 				=> $form,
				'employeeProfile' 	=> $data,
				'NATIONALITY_ARRAY'	=> $NATIONALITY_ARRAY,
				'COUNTRY_ARRAY'		=> $COUNTRY_ARRAY,
				'CITY_ARRAY'		=> $CITY_ARRAY,
				'OCCUPATION_ARRAY'	=> $OCCUPATION_ARRAY,
				'DESIGNATION_ARRAY'	=> $DESIGNATION_ARRAY,
				'BRANCH_ARRAY'		=> $BRANCH_ARRAY,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			);
		}
		
		############  employee view 
		public function viewAction(){
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request = $this->getRequest();
		   	$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('employeeregistration',array('action' => 'add'));
			}
			
			try {
				$NATIONALITY_DATA	= $this->getNationalityTable()->getNationalityForSelect();
				$NATIONALITY_ARRAY	= array();
				foreach($NATIONALITY_DATA as $NATIONALITY_VALUE) {
					$NATIONALITY_ARRAY [] = array(
						"NATIONALITY_ID"	=> $NATIONALITY_VALUE->NATIONALITY_ID,
						"NATIONALITY"		=> $NATIONALITY_VALUE->NATIONALITY,	
					);	
				}
				
				$COUNTRY_DATA	= $this->getCountryTable()->getCountryForSelect();
				$COUNTRY_ARRAY	= array();
				foreach($COUNTRY_DATA as $COUNTRY_VALUE) {
					$COUNTRY_ARRAY [] = array(
						"COUNTRY_ID"	=> $COUNTRY_VALUE->COUNTRY_ID,
						"COUNTRY"		=> $COUNTRY_VALUE->COUNTRY,	
					);	
				}
				
				$CITY_DATA	= $this->getCityTable()->getCityForSelect();
				$CITY_ARRAY	= array();
				foreach($CITY_DATA as $CITY_VALUE) {
					$CITY_ARRAY [] = array(
						"CITY_ID"	=> $CITY_VALUE->CITY_ID,
						"CITY"		=> $CITY_VALUE->CITY,	
					);	
				}
				
				$OCCUPATION_DATA	= $this->getOccupationTable()->getOccupationForSelect();
				$OCCUPATION_ARRAY	= array();
				foreach($OCCUPATION_DATA as $OCCUPATION_VALUE) {
					$OCCUPATION_ARRAY [] = array(
						"OCCUPATION_ID"	=> $OCCUPATION_VALUE->OCCUPATION_ID,
						"OCCUPATION"	=> $OCCUPATION_VALUE->OCCUPATION,	
					);	
				}
				
				$DESIGNATION_DATA	= $this->getDesignationTable()->getDesignationForSelect();
				$DESIGNATION_ARRAY	= array();
				foreach($DESIGNATION_DATA as $DESIGNATION_VALUE) {
					$DESIGNATION_ARRAY [] = array(
						"DESIGNATION_ID"	=> $DESIGNATION_VALUE->DESIGNATION_ID,
						"DESIGNATION"		=> $DESIGNATION_VALUE->DESIGNATION,	
					);	
				}
				
				$BRANCH_DATA	= $this->getBranchTable()->getBranchInfoforPortStatement('');
				$BRANCH_ARRAY	= array();
				foreach($BRANCH_DATA as $BRANCH_VALUE) {
					$BRANCH_ARRAY [] = array(
						
						"BRANCH_NAME"	=> $BRANCH_VALUE->BRANCH_NAME,	
						"COMPANY_NAME"	=> $BRANCH_VALUE->COMPANY_NAME,	
						"ADDRESS"		=> $BRANCH_VALUE->ADDRESS,	
						"WEB"			=> $BRANCH_VALUE->WEB,	
						"PHONE"			=> $BRANCH_VALUE->PHONE,	
						"FAX"			=> $BRANCH_VALUE->FAX,	
						"EMAIL"			=> $BRANCH_VALUE->EMAIL,	
					);	
				}				
				//echo '<pre>'; print_r($BRANCH_ARRAY); echo '</pre>'; die();
				
				
				$employeeProfile = $this->getEmployeePersonalInfoTable()->getEmployeePersonalInfo($id);
				//echo '<pre>'; print_r($employeeProfile); echo '</pre>'; die();
				foreach ($employeeProfile as $selectOption) {
					$data[] = array(
						'EMPLOYEE_ID' 				=> $selectOption->EMPLOYEE_ID,
						'EMPLOYEE_TYPE' 			=> $selectOption->EMPLOYEE_TYPE,	
						'EMPLOYEE_NAME' 			=> $selectOption->EMPLOYEE_NAME,
						'FATHER_NAME' 				=> $selectOption->FATHER_NAME,
						'MOTHER_NAME' 				=> $selectOption->MOTHER_NAME,
						'DATE_OF_BIRTH' 			=> $selectOption->DATE_OF_BIRTH,
						'PLACE_OF_BIRTH' 			=> $selectOption->PLACE_OF_BIRTH,
						'GENDER' 					=> $selectOption->GENDER,
						'BLOOD_GROUP' 				=> $selectOption->BLOOD_GROUP,
						'EMPLOYEE_PHOTO' 			=> $selectOption->EMPLOYEE_PHOTO,
						'RELIGION' 					=> $selectOption->RELIGION,
						'MARITAL_STATUS' 			=> $selectOption->MARITAL_STATUS,
						'NATIONALITY_ID' 			=> $selectOption->NATIONALITY_ID,
						'NATIONAL_PHOTO' 			=> $selectOption->NATIONAL_PHOTO,
						'COUNTRY_ID' 				=> $selectOption->COUNTRY_ID,
						'CITY_ID' 					=> $selectOption->CITY_ID,
						'PERMANENT_ADDRESS' 		=> $selectOption->PERMANENT_ADDRESS,
						'POLICE_STATION' 			=> $selectOption->POLICE_STATION,
						'MOBILE_NUMBER' 			=> $selectOption->MOBILE_NUMBER,
						'EMPLOYEE_CONTACT_ID' 		=> $selectOption->EMPLOYEE_CONTACT_ID,
						'CONTACT_ADDRESS_FIRST' 	=> $selectOption->CONTACT_ADDRESS_FIRST,
						'CONTACT_ADDRESS_SECOND' 	=> $selectOption->CONTACT_ADDRESS_SECOND,
						'TELEPHONE' 				=> $selectOption->TELEPHONE,
						'EMAIL_ADDRESS' 			=> $selectOption->EMAIL_ADDRESS,
						'EMPLOYEE_EDUCATION_ID' 	=> $selectOption->EMPLOYEE_EDUCATION_ID,
						'LAST_CERTIFICATE' 			=> $selectOption->LAST_CERTIFICATE,
						'INSTITUTE_NAME' 			=> $selectOption->INSTITUTE_NAME,
						'PASSING_YEAR' 				=> $selectOption->PASSING_YEAR,
						'MARKS_OBTAIN' 				=> $selectOption->MARKS_OBTAIN,
						'CLASS_DIVISION' 			=> $selectOption->CLASS_DIVISION,
						'EDU_PHOTO' 			=> $selectOption->EDU_PHOTO,
						'EMPLOYEE_POSTING_ID' 		=> $selectOption->EMPLOYEE_POSTING_ID,
						'DESIGNATION_ID' 			=> $selectOption->DESIGNATION_ID,
						'BRANCH_ID' 				=> $selectOption->BRANCH_ID,
						'DIVISION_NAME' 			=> $selectOption->DIVISION_NAME,
						'EMPLOYEE_SALARY_ID' 		=> $selectOption->EMPLOYEE_SALARY_ID,
						'SALARY_AMOUNT' 			=> $selectOption->SALARY_AMOUNT,
						'EMPLOYEE_SPOUSE_ID' 		=> $selectOption->EMPLOYEE_SPOUSE_ID,
						'SPOUSE_NAME' 				=> $selectOption->SPOUSE_NAME,
						'OCCUPATION_ID' 			=> $selectOption->OCCUPATION_ID,
						'SPOUSE_ADDRESS' 			=> $selectOption->SPOUSE_ADDRESS,
					);
				}
				//echo '<pre>'; print_r($data); echo '</pre>'; die();
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('employeeregistration', array('action' => 'index'));
			}
			
			return array(
				'id' 				=> $id,
				'employeeProfile' 	=> $data,
				'NATIONALITY_ARRAY'	=> $NATIONALITY_ARRAY,
				'COUNTRY_ARRAY'		=> $COUNTRY_ARRAY,
				'CITY_ARRAY'		=> $CITY_ARRAY,
				'OCCUPATION_ARRAY'	=> $OCCUPATION_ARRAY,
				'DESIGNATION_ARRAY'	=> $DESIGNATION_ARRAY,
				'BRANCH_ARRAY'		=> $BRANCH_ARRAY,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			); 
		}
		
		public function getEmployeePersonalInfoTable() {
			if(!$this->employeePersonalInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeePersonalInfoTable 	= $sm->get('HumanResource\Model\EmployeePersonalInfoTable');
			}
			return $this->employeePersonalInfoTable;
		}
		
		public function getEmployeeSpouseInfoTable() {
			if(!$this->employeeSpouseInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeeSpouseInfoTable 	= $sm->get('HumanResource\Model\EmployeeSpouseInfoTable');
			}
			return $this->employeeSpouseInfoTable;
		}
		
		public function getEmployeeContactInfoTable() {
			if(!$this->employeeContactInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeeContactInfoTable 	= $sm->get('HumanResource\Model\EmployeeContactInfoTable');
			}
			return $this->employeeContactInfoTable;
		}
		
		public function getEmployeeEducationInfoTable() {
			if(!$this->employeeEducationInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeeEducationInfoTable 	= $sm->get('HumanResource\Model\EmployeeEducationInfoTable');
			}
			return $this->employeeEducationInfoTable;
		}
		
		public function getEmployeePostingInfoTable() {
			if(!$this->employeePostingInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeePostingInfoTable 	= $sm->get('HumanResource\Model\EmployeePostingInfoTable');
			}
			return $this->employeePostingInfoTable;
		}
		
		public function getEmployeeSalaryInfoTable() {
			if(!$this->employeeSalaryInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeeSalaryInfoTable 		= $sm->get('HumanResource\Model\EmployeeSalaryInfoTable');
			}
			return $this->employeeSalaryInfoTable;
		}
		
		public function getNationalityTable() {
			if(!$this->nationalityTable) {
				$sm 						= $this->getServiceLocator();
				$this->nationalityTable 	= $sm->get('GlobalSetting\Model\NationalityTable');
			}
			return $this->nationalityTable;
		}
		
		public function getCountryTable() {
			if(!$this->countryTable) {
				$sm 					= $this->getServiceLocator();
				$this->countryTable 	= $sm->get('GlobalSetting\Model\CountryTable');
			}
			return $this->countryTable;
		}
		
		public function getCityTable() {
			if(!$this->cityTable) {
				$sm 				= $this->getServiceLocator();
				$this->cityTable 	= $sm->get('GlobalSetting\Model\CityTable');
			}
			return $this->cityTable;
		}
		
		public function getOccupationTable() {
			if(!$this->occupationTable) {
				$sm 					= $this->getServiceLocator();
				$this->occupationTable 	= $sm->get('GlobalSetting\Model\OccupationTable');
			}
			return $this->occupationTable;
		}
		
		public function getDesignationTable() {
			if(!$this->designationTable) {
				$sm 					= $this->getServiceLocator();
				$this->designationTable = $sm->get('GlobalSetting\Model\DesignationTable');
			}
			return $this->designationTable;
		}
		
		public function getBranchTable() {
			if(!$this->branchTable) {
				$sm 					= $this->getServiceLocator();
				$this->branchTable = $sm->get('CompanyInformation\Model\BranchTable');
			}
			return $this->branchTable;
		}
		
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
	}
?>