<?php
	//filename : module/Ibcia/src/Ibcia/Controller/HolidayController.php
	namespace Ibcia\Controller;
	 
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\ServiceManager\ServiceLocatorInterface;
	use Zend\View\Model\ViewModel;
	use Ibcia\Model\BusinessDate;
	use Ibcia\Form\HolidayForm;
	 
	class HolidayController extends AbstractActionController {
		protected $holidayTable;
		
		public function indexAction() {
			$viewModel = new ViewModel();
			/*//redirect to index controller...
			if ($this->getServiceLocator()->get('IdentityManager')->hasIdentity() &&
				null !== $this->getBusinessDateTable()->currentBusinessDate &&
				null !== $this->getBusinessDateTable()->isSODBkupFinished &&
				null !== $this->getBusinessDateTable()->isSODFinished) {
				return $this->redirect()->toRoute('index');
			}
			
			$this->layout('layout/LoginLayout');
			$viewModel = new ViewModel();
			//set businessdate...
			$viewModel->setVariable('businessdate', $this->getBusinessDateTable()->currentBusinessDate);
			//set error...
			$viewModel->setVariable('error', '');
			//business date initialize block...
			if ($this->getServiceLocator()->get('IdentityManager')->hasIdentity() &&
				null == $this->getBusinessDateTable()->currentBusinessDate) {
				$form = new BusinessDateSetupForm();
				$this->addBusinessDate($form, $viewModel);
			}
			//SOD db backup execution block...
			if($this->getServiceLocator()->get('IdentityManager')->hasIdentity() &&
			   null !== $this->getBusinessDateTable()->currentBusinessDate &&
			   null == $this->getBusinessDateTable()->isSODBkupFinished) {
				$viewModel->setTemplate('ibcia/sod/sod-backup');
				$form = new SODForm();
				$form->get('btnSOD')->setValue('Backup Database');
				$this->dbBackup($form, $viewModel);
			}
			//SOD execution block...
			if ($this->getServiceLocator()->get('IdentityManager')->hasIdentity() &&
				null !== $this->getBusinessDateTable()->currentBusinessDate &&
				null !== $this->getBusinessDateTable()->isSODBkupFinished &&
				null == $this->getBusinessDateTable()->isSODFinished) {
				$viewModel->setTemplate('ibcia/sod/sod');
				$form = new SODForm();
				$form->get('btnSOD')->setValue('Run SOD');
				$this->runSoDProcess($form, $viewModel);
			}
			
			$viewModel->setVariable('form', $form);*/
			return $viewModel;
		}
		 
		/** this function called by indexAction to reduce complexity of function */
		protected function addBusinessDate($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$businessDate = new BusinessDate();
				$form->setInputFilter($businessDate->getInputFilter());
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $form->getData();
					$data = array(
								'BUSINESS_DATE' => $formData['BUSINESS_DATE'],
								'SOD_BKUP'		=> null,
								'SOD_FLAG' 		=> null,
								'EOD_BKUP'		=> null,
								'EOD_FLAG' 		=> null,
								'DATE_CLOSE' 	=> null,
							);
					$businessDate->exchangeArray($data);
					if($this->getBusinessDateTable()->saveBusinessDate($businessDate)) {
						return $this->redirect()->toRoute('businessdate');
					}
					
					//$viewModel->setVariable('error', 'Access denied : Unauthorized user information');
				}
			}
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function dbBackup($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$businessDate = new BusinessDate();
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $form->getData();
					$data = array(
								'BUSINESS_DATE' => $this->getBusinessDateTable()->currentBusinessDate,
								'SOD_BKUP'		=> (isset($formData['btnSOD']) && (strtolower($formData['btnSOD']) == 'backup database')) ? 'y' : null,
								'SOD_FLAG' 		=> null,
								'EOD_BKUP'		=> null,
								'EOD_FLAG' 		=> null,
								'DATE_CLOSE' 	=> null,
							);
					$businessDate->exchangeArray($data);
					if($this->getBusinessDateTable()->saveBusinessDate($businessDate)) {
						return $this->redirect()->toRoute('businessdate');
					}
					
					//$viewModel->setVariable('error', 'Access denied : Unauthorized user information');
				}
			}
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function runSoDProcess($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$businessDate = new BusinessDate();
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $form->getData();
					
					$data = array(
								'BUSINESS_DATE' => $this->getBusinessDateTable()->currentBusinessDate,
								'SOD_BKUP'		=> $this->getBusinessDateTable()->isSODBkupFinished,
								'SOD_FLAG' 		=> (isset($formData['btnSOD']) && (strtolower($formData['btnSOD']) == 'run sod')) ? 'y' : null,
								'EOD_BKUP'		=> null,
								'EOD_FLAG' 		=> null,
								'DATE_CLOSE' 	=> null,
							);
					$businessDate->exchangeArray($data);
					if($this->getBusinessDateTable()->saveBusinessDate($businessDate)) {
						return $this->redirect()->toRoute('index');
					}
					
					//$viewModel->setVariable('error', 'Access denied : Unauthorized user information');
				}
			}
		}
		
		private function getHolidayTable() {
			if(!$this->holidayTable) {
				$sm = $this->getServiceLocator();
				$this->holidayTable = $sm->get('HolidayTable');
			}
			return $this->holidayTable;
		}
	}
?>	