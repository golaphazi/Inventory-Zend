<?php
	namespace GlobalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use GlobalSetting\Model\ChequeBookDetails;
	use GlobalSetting\Form\ChequeBookDetailsForm;

	use GlobalSetting\Model\ChequeBookDetailsBkdn;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;
	
	class ChequeBookDetailsController extends AbstractActionController {
		protected $chequeBookDetailsTable;
		protected $chequeBookDetailsBkdnTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$paginator = $this->getChequeBookDetailsTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'chequebookdetailss' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new ChequeBookDetailsForm('chequebookdetails', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			$request = $this->getRequest();
			
			if($request->isPost()) {
				$chequebookdetails = new ChequeBookDetails();
				$form->setInputFilter($chequebookdetails->getInputFilter());
				$form->setData($request->getPost());
				if($form->isValid()) {
					$chequebookdetails->exchangeArray($request->getPost());
					if( $chequeBookDetailsId = $this->getChequeBookDetailsTable()->saveChequeBookDetails($chequebookdetails)) {
						//Cheque Book Details Entry Start
						$chequeBookDetailsBkdnData = array();
						$chequeBookDetailsBkdnData = array(
							'CHEQUE_BOOK_DETAILS_ID' => $chequeBookDetailsId,
							'CHEQUE_NO' => $chequebookdetails->CHEQUE_NO_RANGE_FROM.'-'.$chequebookdetails->CHEQUE_NO_RANGE_TO,
						);
						
						$chequeBookDetailsBkdn = new ChequeBookDetailsBkdn();
						$chequeBookDetailsBkdn->exchangeArray($chequeBookDetailsBkdnData);
						//echo "<pre>"; print_r($chequeBookDetailsBkdn); die();
					
						if($this->getChequeBookDetailsBkdnTable()->saveChequeBookDetailsBkdn($chequeBookDetailsBkdn)) {
							return $this->redirect()->toRoute('chequebookdetails');
						} else {
							throw new \Exception("Cheque book couldn't save properly!");
						}
						//Cheque Book Details Entry End
					}
					return $this->redirect()->toRoute('chequebookdetails');
				}
			}			
			return array('form' => $form);
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		   	$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('chequebookdetails',array('action' => 'add'));
			}
			
			try {
				$chequebookdetails = $this->getChequeBookDetailsTable()->getChequeBookDetails($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('chequebookdetails', array('action' => 'index'));
			}
			
			$form = new ChequeBookDetailsForm('chequebookdetails', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($chequebookdetails);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					
					$chequebookdetails->exchangeArray($request->getPost());					
					if( $investorDetailsID = $this->getChequeBookDetailsTable()->saveChequeBookDetails($chequebookdetails)){
						//Investor Wise Jointapplicant Set Start
						$jointApplicantData = array();
						$jointApplicantData = array(
							'APPLICANT_NAME' => $chequebookdetails->APPLICANT_NAME,
							'FATHER_NAME' => $chequebookdetails->JA_FATHER_NAME,
							'MOTHER_NAME' => $chequebookdetails->JA_MOTHER_NAME,
							'SPOUSE_NAME' => $chequebookdetails->JA_SPOUSE_NAME,							
							'RESIDENCY' => $chequebookdetails->JA_RESIDENCY,
							'NID' => $chequebookdetails->JA_NID,
							'BO_ACCOUNT_NO' => $chequebookdetails->JA_BO_ACCOUNT_NO,
							'PASSPORT_NO' => $chequebookdetails->JA_PASSPORT_NO,
							'MONTHLY_INCOME' => $chequebookdetails->JA_MONTHLY_INCOME,
							'BANK_ACCOUNT_NO' => $chequebookdetails->JA_BANK_ACC_NO,
							'MAILING_ADDRESS' => $chequebookdetails->JA_MAILING_ADDRESS,
							'PERMANENT_ADDRESS' => $chequebookdetails->JA_PERMANENT_ADDRESS,
							'TELEPHONE_OFFICE' => $chequebookdetails->JA_TELEPHONE_OFFICE,
							'TELEPHONE_RESIDENTIAL' => $chequebookdetails->JA_TELEPHONE_RESIDENTIAL,
							'MOBILE' => $chequebookdetails->JA_MOBILE,
							'FAX' => $chequebookdetails->JA_FAX,
							'EMAIL' => $chequebookdetails->JA_EMAIL,
							'JOINT_APPLICANT_SIGN' => $chequebookdetails->JA_SIGN,
							'JOINT_APPLICANT_PHOTO' => $chequebookdetails->JA_PHOTO,
							'INVESTOR_DETAILS_ID' => $investorDetailsID,
						);
						/*echo '<pre>';
						print_r($jointApplicantData);
						echo '</pre>';
						die();*/
						$investorJointApplicant = new InvestorJointApplicant();
						$investorJointApplicant->exchangeArray($jointApplicantData);
						if($this->getInvestorJointApplicantTable()->editJointApplicant($investorJointApplicant)) {
							//return $this->redirect()->toRoute('chequebookdetails');
						} else {
							throw new \Exception("Investor Joint Applicant couldn't save properly!");
						}
						//Investor Wise Jointapplicant Set End
					}
					return $this->redirect()->toRoute('chequebookdetails');
				}
			}
			
			return array(
				'id' => $id,
				'form' => $form,
			);
		}
		
		public function deleteAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Global Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			
			if(!$id) {
				return $this->redirect()->toRoute('chequebookdetails');
			}
			
			$request = $this->getRequest();
			//print_r($request);
			if($request->isPost()) {
				$del = $request->getPost('del','No');
				
				if($del == 'Yes') {
					$id = (int) $request->getPost('id');
					$this->getChequeBookDetailsTable()->deleteChequeBookDetails($id);
				}
				
				return $this->redirect()->toRoute('chequebookdetails');
			}
			
			return array(
				'id'	=> $id,
				'chequebookdetails'	=> $this->getChequeBookDetailsTable()->getChequeBookDetails($id),
			);
		}
		
		public function getChequeBookDetailsTable() {
			if(!$this->chequeBookDetailsTable) {
				$sm = $this->getServiceLocator();
				$this->chequeBookDetailsTable = $sm->get('GlobalSetting\Model\ChequeBookDetailsTable');
			}
			return $this->chequeBookDetailsTable;
		}
		
		public function getChequeBookDetailsBkdnTable() {
			if(!$this->chequeBookDetailsBkdnTable) {
				$sm = $this->getServiceLocator();
				$this->chequeBookDetailsBkdnTable = $sm->get('GlobalSetting\Model\ChequeBookDetailsBkdnTable');
			}
			return $this->chequeBookDetailsBkdnTable;
		}
	}
?>