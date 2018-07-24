<?php
	namespace CompanyInformation\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use CompanyInformation\Model\Company;
	use CompanyInformation\Model\Branch;
	use CompanyInformation\Form\BranchForm;
	
	class BranchController extends AbstractActionController {
		protected $companyTable;
		protected $branchTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Company Information',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			return new ViewModel(array(
				'branches' => $this->getBranchTable()->fetchAll(),
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Company Information',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new BranchForm('branch', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			$request = $this->getRequest();
			if($request->isPost()) {
				$branch = new Branch();
				$form->setInputFilter($branch->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$branch->exchangeArray($request->getPost());
					$this->getBranchTable()->saveBranch($branch);
					$companyInfo	= $this->getCompanyTable()->getCompany($branch->COMPANY_ID);
					
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Company [ ".$companyInfo->COMPANY_NAME." ] under [ ".$branch->BRANCH_NAME." ] added successfully!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('branch');
				}
			}
			
			return array('form' => $form);
		}
	
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Company Information',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		   	$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('branch',array('action' => 'add'));
			}
			
			try {
				$branch = $this->getBranchTable()->getBranch($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('branch', array('action' => 'index'));
			}
			
			$form = new BranchForm('branch', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($branch);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					
					$branch->exchangeArray($request->getPost());
					$this->getBranchTable()->saveBranch($branch);
					$companyInfo	= $this->getCompanyTable()->getCompany($branch->COMPANY_ID);
				
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Company [ ".$companyInfo->COMPANY_NAME." ] under [ ".$branch->BRANCH_NAME." ] edit successfully!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('branch');
				}
			}
			
			return array(
				'id' => $id,
				'form' => $form,
			);
		}
		
		public function activeDeactiveBranchAction() {
			$companyId 	=  $this->params()->fromQuery('companyId', 0);
			$branchId 	=  $this->params()->fromQuery('branchId', 0);
			$ststus 	=  $this->params()->fromQuery('ststus');
			
			$success	= '';
			$action		= '';
			if($branchId == 0) {
				throw new \Exception("Invalid id");
			} else {
				$companyInfo	= $this->getCompanyTable()->getCompany($companyId);
				$branchInfo		= $this->getBranchTable()->getBranch($branchId);
				$success		= $this->getBranchTable()->activeDeactiveBranch($companyId,$branchId,$ststus);
				if(strtolower($ststus) == 'y') {
					$action = "active";	
				} else {
					$action = "deactive";	
				}
				
				$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Company [ ".$companyInfo->COMPANY_NAME." ] under [ ".$branchInfo->BRANCH_NAME." ] ".$action." successfully!</h4></td>
															</tr>
														</table>");
				echo json_encode($success);
				exit;
			}
		}
		
		public function getCompanyTable() {
			if(!$this->companyTable) {
				$sm = $this->getServiceLocator();
				$this->companyTable = $sm->get('CompanyInformation\Model\CompanyTable');
			}
			return $this->companyTable;
		}
		
		public function getBranchTable() {
			if(!$this->branchTable) {
				$sm = $this->getServiceLocator();
				$this->branchTable = $sm->get('CompanyInformation\Model\BranchTable');
			}
			return $this->branchTable;
		}
	}
?>