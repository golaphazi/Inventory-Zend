<?php	
	namespace HumanResource\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use HumanResource\Model\EmployeePersonalInfo;
	use HumanResource\Form\EmployeeJoiningForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class EmployeeJoiningController extends AbstractActionController {
		protected $employeePersonalInfoTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$CONTROLLER_NAME	= 'Joining';
			$EMPLOYEE_DATA 		= $this->getEmployeePersonalInfoTable()->getAllEmployeePersonalInfo($CONTROLLER_NAME);
						
			$select 	= new Select();
			$order_by 	= $this->params()->fromRoute('order_by') ? $this->params()->fromRoute('order_by') : 'id'; 
			$order 		= $this->params()->fromRoute('order') ? $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
			$select->order($order_by . ' ' . $order);
			
			$request 			= $this->getRequest();			
			$form 				= new EmployeeJoiningForm('employeejoiningform', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			
			// Employee Joining Date Start By Akhand
			if($request->isPost()) {
				$form->setData($request->getPost());
				
				// Set All Valid Data Start By Akhand
				if($form->isValid()) {
					$postedData 				= $request->getPost();
					//echo "<pre>"; print_r($postedData); die();
					$this->getEmployeePersonalInfoTable()->transectionStart();
					
					$EMPLOYEE_IDS	= $postedData['EMPLOYEE_ID'];
					$success		= 0;
					foreach($EMPLOYEE_IDS as $EMPLOYEE_ID) {
						$EMPLOYEE_ID	= $EMPLOYEE_ID;
						$JOINING_DATE	= $postedData["JOINING_DATE_{$EMPLOYEE_ID}"];
						
						if($this->getEmployeePersonalInfoTable()->updateEmployeeJoiningDate($EMPLOYEE_ID,$JOINING_DATE)) {
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
																		<td colspan='3' style='text-align:center;'><h4>Joining Date Saved Successfully!</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('employeejoining');	
					
					} else {
						$this->getEmployeePersonalInfoTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
																	<tr class='valid_msg'>
																		<td colspan='3' style='text-align:center;'><h4>Sorry! There is System Error.</h4></td>
																	</tr>
																</table>");
						return $this->redirect()->toRoute('employeejoining');	
					}
				}
				// Set All Valid Data End By Akhand
			}
			//  Employee Joining Date End By Akhand
			
			return new ViewModel(array(
				'form' 				=> $form,
				'investorprofiles' 	=> $EMPLOYEE_DATA,
				'order_by' 			=> $order_by,
				'order' 			=> $order,
				'flashMessages' 	=> $this->flashMessenger()->getMessages(),
			));
		}
		
		public function getEmployeePersonalInfoTable() {
			if(!$this->employeePersonalInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeePersonalInfoTable 	= $sm->get('HumanResource\Model\EmployeePersonalInfoTable');
			}
			return $this->employeePersonalInfoTable;
		}
	}
?>