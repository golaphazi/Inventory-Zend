<?php	
	namespace HumanResource\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use HumanResource\Model\employeeAttendanceInfo;
	use HumanResource\Form\EmployeeAttendanceInForm;
	use HumanResource\Form\EmployeeAttendanceOutForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	use Zend\Session\Container as SessionContainer;
	
	class EmployeeAttendanceController extends AbstractActionController {
		protected $employeePersonalInfoTable;
		protected $employeeAttendanceInfoTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$CONTROLLER_NAME	= 'Attendance';
			$EMPLOYEE_DATA 		= $this->getEmployeePersonalInfoTable()->getAllEmployeePersonalInfo($CONTROLLER_NAME);
						
			$select 	= new Select();
			$order_by 	= $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id'; 
			$order 		= $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
			$select->order($order_by . ' ' . $order);
			
			return new ViewModel(array(
				'investorprofiles' 	=> $EMPLOYEE_DATA,
				'order_by' 			=> $order_by,
				'order' 			=> $order,
				'flashMessages' 	=> $this->flashMessenger()->getMessages(),
			));
		}
		
		public function attendanceInAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 			= $this->getRequest();			
			$form 				= new EmployeeAttendanceInForm('employeeattendancein', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			
			$CONTROLLER_NAME	= 'In';
			$EMPLOYEE_DATA 		= $this->getEmployeeAttendanceInfoTable()->getAllEmployeeAttendanceInfo($CONTROLLER_NAME);
			
			// Employee Attendance Start By Akhand
			if($request->isPost()) {
				$form->setData($request->getPost());
				
				// Set All Valid Data Start By Akhand
				if($form->isValid()) {
					$postedData 				= $request->getPost();
					//echo "<pre>"; print_r($postedData); die();
					$this->getEmployeePersonalInfoTable()->transectionStart();
					
					$employeeAttendanceInfo		= new EmployeeAttendanceInfo();
					$employeeAttendanceData		= array();
					
					$EMPLOYEE_IDS	= $postedData['EMPLOYEE_ID'];
					$success		= 0;
					foreach($EMPLOYEE_IDS as $EMPLOYEE_ID) {
						$EMPLOYEE_ID	= $EMPLOYEE_ID;
						$IN_TIME_HOUR	= $postedData["IN_TIME_HOUR_{$EMPLOYEE_ID}"];
						if($postedData["IN_TIME_AM_PM_{$EMPLOYEE_ID}"] == 'PM') {
							$IN_TIME_HOUR	= $IN_TIME_HOUR+12;
						} else {
							$IN_TIME_HOUR	= $IN_TIME_HOUR;
						}
						$IN_TIME		= $IN_TIME_HOUR.":".$postedData["IN_TIME_MINUTE_{$EMPLOYEE_ID}"];
						
						$employeeAttendanceData		= array(
							'EMPLOYEE_ATTENDANCE_ID' 	=> $postedData["EMPLOYEE_ATTENDANCE_ID_{$EMPLOYEE_ID}"],		
							'EMPLOYEE_ID' 				=> $EMPLOYEE_ID,
							'ATTENDANCE_DATE' 			=> $postedData["ATTENDANCE_DATE"],
							'IN_TIME' 					=> $IN_TIME,
						);
						$employeeAttendanceInfo->exchangeArray($employeeAttendanceData);
						//echo "<pre>"; print_r($employeeAttendanceInfo); die();
						if($this->getEmployeeAttendanceInfoTable()->saveEmployeeAttendanceInfo($employeeAttendanceInfo)) {
							$success	= 1;
							continue;		
						} else {
							$success	= 0;
							break;
						}
					}
					
					if($success) {
						$this->getEmployeePersonalInfoTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																	<tr class='valid_msg'>
																		<td colspan='3' style='text-align:center;'><h4>[".$postedData["ATTENDANCE_DATE"]."] Attendance In Saved Successfully !</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('employeeattendance');	
					
					} else {
						$this->getEmployeePersonalInfoTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																	<tr class='valid_msg'>
																		<td colspan='3' style='text-align:center;'><h4>Sorry! There is System Error.</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('employeeattendance');	
					}
				}
				// Set All Valid Data End By Akhand
			}
			// Employee Attendance End By Akhand
			
			return new ViewModel(array(
				'investorprofiles' 	=> $EMPLOYEE_DATA,
				'form' 				=> $form,
				'flashMessages' 	=> $this->flashMessenger()->getMessages(),
			));
		}
		
		public function attendanceOutAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$this->session 				= new SessionContainer('post_supply');
			$businessDate 				= $this->session->businessdate;
			
			$request 			= $this->getRequest();			
			$form 				= new EmployeeAttendanceOutForm('employeeattendanceout', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			
			$CONTROLLER_NAME	= 'Out';
			$EMPLOYEE_DATA 		= $this->getEmployeeAttendanceInfoTable()->getAllEmployeeAttendanceInfo($CONTROLLER_NAME);
			
			// Employee Attendance Start By Akhand
			if($request->isPost()) {
				$form->setData($request->getPost());
				
				// Set All Valid Data Start By Akhand
				if($form->isValid()) {
					$postedData 				= $request->getPost();
					//echo "<pre>"; print_r($postedData); die();
					$this->getEmployeePersonalInfoTable()->transectionStart();
					
					$employeeAttendanceInfo		= new EmployeeAttendanceInfo();
					$employeeAttendanceData		= array();
					
					$EMPLOYEE_IDS	= $postedData['EMPLOYEE_ID'];
					$success		= 0;
					foreach($EMPLOYEE_IDS as $EMPLOYEE_ID) {
						$EMPLOYEE_ID	= $EMPLOYEE_ID;
						$IN_TIME		= $postedData["IN_TIME_{$EMPLOYEE_ID}"];
						
						$OUT_TIME_HOUR	= $postedData["OUT_TIME_HOUR_{$EMPLOYEE_ID}"];
						if($postedData["OUT_TIME_AM_PM_{$EMPLOYEE_ID}"] == 'PM') {
							$OUT_TIME_HOUR	= $OUT_TIME_HOUR+12;
						} else {
							$OUT_TIME_HOUR	= $OUT_TIME_HOUR;
						}
						$OUT_TIME		= $OUT_TIME_HOUR.":".$postedData["OUT_TIME_MINUTE_{$EMPLOYEE_ID}"];
						
						$WORKING_HOUR	= (strtotime($OUT_TIME) - strtotime($IN_TIME));
 						$WORKING_HOUR	= gmdate("G:i", $WORKING_HOUR);
						
						$OFFICE_HOUR	= "10:00";
						$OVER_HOUR		= "00:00";
						$LATE_HOUR		= "00:00";
						
						if(strtotime($OFFICE_HOUR)>strtotime($WORKING_HOUR)) {
							$LATE_HOUR	= round(abs(strtotime($OFFICE_HOUR) - strtotime($WORKING_HOUR)));
 							$LATE_HOUR	= gmdate("G:i", $LATE_HOUR);
							$STATUS		= 'Late';	
						} else if (strtotime($OFFICE_HOUR)<strtotime($WORKING_HOUR)){
							$OVER_HOUR	= round(abs(strtotime($OFFICE_HOUR) - strtotime($WORKING_HOUR)));
 							$OVER_HOUR	= gmdate("G:i", $OVER_HOUR);
							$STATUS		= 'Over';
						} else {
							$OVER_HOUR	= "00:00";
							$LATE_HOUR	= "00:00";
							$STATUS		= 'Ok';	
						}
						
						$employeeAttendanceData		= array(
							'EMPLOYEE_ATTENDANCE_ID' 	=> $postedData["EMPLOYEE_ATTENDANCE_ID_{$EMPLOYEE_ID}"],		
							'EMPLOYEE_ID' 				=> $EMPLOYEE_ID,
							'ATTENDANCE_DATE' 			=> $postedData["ATTENDANCE_DATE"],
							'IN_TIME' 					=> $IN_TIME,
							'OUT_TIME' 					=> $OUT_TIME,
							'WORKING_HOUR' 				=> $WORKING_HOUR,
							'LATE_HOUR' 				=> $LATE_HOUR,
							'OVER_HOUR' 				=> $OVER_HOUR,
							'STATUS' 					=> $STATUS,
						);
						$employeeAttendanceInfo->exchangeArray($employeeAttendanceData);
						//echo "<pre>"; print_r($employeeAttendanceInfo); die();
						if($this->getEmployeeAttendanceInfoTable()->saveEmployeeAttendanceInfo($employeeAttendanceInfo)) {
							$success	= 1;
							continue;		
						} else {
							$success	= 0;
							break;
						}
					}
					
					if($success) {
						if($this->getEmployeeAttendanceInfoTable()->updateAttendanceFlag()) {
							$success	= 1;
						} else {
							$success	= 1;
						}
					}
					
					if($success) {
						$this->getEmployeePersonalInfoTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																	<tr class='valid_msg'>
																		<td colspan='3' style='text-align:center;'><h4>[".$postedData["ATTENDANCE_DATE"]."] Attendance Out Saved Successfully !</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('employeeattendance');	
					
					} else {
						$this->getEmployeePersonalInfoTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																	<tr class='valid_msg'>
																		<td colspan='3' style='text-align:center;'><h4>Sorry! There is System Error.</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('employeeattendance');	
					}
				}
				// Set All Valid Data End By Akhand
			}
			// Employee Attendance End By Akhand
			
			return new ViewModel(array(
				'investorprofiles' 	=> $EMPLOYEE_DATA,
				'form' 				=> $form,
				'flashMessages' 	=> $this->flashMessenger()->getMessages(),
				'businessDate' 		=> $businessDate,
			));
		}
		
		public function getEmployeePersonalInfoTable() {
			if(!$this->employeePersonalInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeePersonalInfoTable 	= $sm->get('HumanResource\Model\EmployeePersonalInfoTable');
			}
			return $this->employeePersonalInfoTable;
		}
		
		public function getEmployeeAttendanceInfoTable() {
			if(!$this->employeeAttendanceInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeeAttendanceInfoTable 	= $sm->get('HumanResource\Model\EmployeeAttendanceInfoTable');
			}
			return $this->employeeAttendanceInfoTable;
		}
	}
?>