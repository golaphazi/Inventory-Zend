<?php	
	namespace HumanResource\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use HumanResource\Model\EmployeePersonalInfo;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class EmployeeAccessControlController extends AbstractActionController {
		protected $employeePersonalInfoTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Human Resource',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$CONTROLLER_NAME	= 'Access Control';
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
		
		public function getEmployeePersonalInfoTable() {
			if(!$this->employeePersonalInfoTable) {
				$sm 								= $this->getServiceLocator();
				$this->employeePersonalInfoTable 	= $sm->get('HumanResource\Model\EmployeePersonalInfoTable');
			}
			return $this->employeePersonalInfoTable;
		}
	}
?>