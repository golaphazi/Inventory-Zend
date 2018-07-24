<?php
	//filename : module/Ibcia/src/Ibcia/Controller/LoginController.php
	namespace Ibcia\Controller;
	 
	use Zend\Mvc\Controller\AbstractActionController;
	use Ibcia\Storage\IdentityManagerInterface;
	use Zend\View\Model\ViewModel;
	use Zend\Session\Container as SessionContainer;
	class LoginController extends AbstractActionController
	{
		protected $identityManager;
		
		//we will inject identityManager via factory
		public function __construct(IdentityManagerInterface $identityManager)
		{
			$this->identityManager = $identityManager;
		}
		
		public function indexAction()
		{
			/*if ($this->identityManager->hasIdentity() &&
				null !== $this->getServiceLocator()->get('BusinessDateTable')->currentBusinessDate &&
				null !== $this->getServiceLocator()->get('BusinessDateTable')->isSODFinished) {
				//redirect to index controller...
				return $this->redirect()->toRoute('index');
			}*/
			
			/*$this->identityManager->storeIdentity(//authenticate user
				 array('id'          => 11,
						'username'   => 'Sumon',
						'rid'   	 => 15,
						'role'   	 => 'Super Admin',
						'forwardto'  => '',
						'ip_address' => '',
						'user_agent' => '')
			);
			return $this->_redirect()->toRoute('index');*/
			//echo "<pre>";print_r($dataform);die();
			
			$this->layout('layout/LoginLayout');
			$form = $this->getServiceLocator()
						 ->get('FormElementManager')
						 ->get('Ibcia\Form\LoginForm');   
			$viewModel = new ViewModel();
			 
			//initialize error...
			$viewModel->setVariable('error', '');
			//authentication block...
			$this->authenticate($form, $viewModel);
			 
			$viewModel->setVariable('form', $form);
			return $viewModel;
		}
		 
		/** this function called by indexAction to reduce complexity of function */
		protected function authenticate($form, $viewModel)
		{
			$request = $this->getRequest();
			if ($request->isPost()) {
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$dataform = $form->getData();
					$identityRow = $this->identityManager->login($dataform['OPERATOR_NAME'], $dataform['OPERATOR_PASSWORD'], (strtolower($dataform['REMEMBER_ME']) == 'y' ? 1 : 0));
					//echo '<pre>';print_r($identityRow);die();
					// Test By Akhand Start
					/*$this->identityManager->storeIdentity(//authenticate user
						 array('id'          => 11,
								'username'   => 'Sumon',
								'rid'   	 => 15,
								'role'   	 => 'Super Admin',
								'forwardto'  => '',
								'ip_address' => '',
								'user_agent' => '')
					);
					return $this->redirect()->toRoute('index');
					echo "<pre>";print_r($dataform);die();*/
					// Test By Akhand End
					
					if ($identityRow) {//validate identity
						$businessCalMgr = $this->getServiceLocator()->get('BusinessDateTable');
						
						if(null !== $businessCalMgr->currentBusinessDate && null !== $businessCalMgr->isSODFinished) {//check businessdate set & SoD executed
							
							$this->identityManager->storeIdentity(//authenticate user
								 array('id'          => $identityRow->USER_ID,
										'username'   => $dataform['OPERATOR_NAME'],
										'rid'   	 => $identityRow->ROLE_ID,
										'role'   	 => $identityRow->ROLE_NAME,
										'forwardto'  => $identityRow->FORWARD_TO,
										'ip_address' => $this->getRequest()->getServer('REMOTE_ADDR'),
										'user_agent' => $request->getServer('HTTP_USER_AGENT'),
										'branchid'   => $identityRow->BRANCH_ID,
										)
							);
							
							$this->session = new SessionContainer('post_supply');	
							$this->session->username 			= $dataform['OPERATOR_NAME'];
							$this->session->userid 				= $identityRow->USER_ID;
							$this->session->companyId 			= '';
							$this->session->operatorId			= $identityRow->USER_ID;
							$this->session->operatorName		= $dataform['OPERATOR_NAME'];
							$this->session->ip_address			= $this->getRequest()->getServer('REMOTE_ADDR');
							$this->session->forwardto			= $identityRow->FORWARD_TO;
							$this->session->roleid				= $identityRow->ROLE_ID;
							$this->session->rolename			= $identityRow->ROLE_NAME;
							$this->session->recdate 			= date('Y-m-d H:i:s');
							$this->session->businessdate 		= date("d-M-Y", strtotime($businessCalMgr->currentBusinessDate));
							$this->session->branchid			= $identityRow->BRANCH_ID;
							return $this->redirect()->toRoute('index');
							//return $this->redirect()->toRoute('category');
						}
						//echo "<pre>";print_r($identityRow);die();
						if(strtoupper($identityRow->ROLE_NAME) != 'ADMINISTRATOR'){//check rule if businessdate not set
							return $viewModel->setVariable('error', 'Sorry, system is not initialized yet!');
						}
						
						$this->identityManager->storeIdentity(//authenticate administrator
							 array('id'          => $identityRow->USER_ID,
									'username'   => $dataform['OPERATOR_NAME'],
									'rid'   	 => $identityRow->ROLE_ID,
									'role'   	 => $identityRow->ROLE_NAME,
									'forwardto'  => $identityRow->FORWARD_TO,
									'ip_address' => $this->getRequest()->getServer('REMOTE_ADDR'),
									'user_agent' => $request->getServer('HTTP_USER_AGENT')
									)
						);
						
						return $this->redirect()->toRoute('businessdate');
						/*$form = $this->getServiceLocator()
						 ->get('FormElementManager')
						 ->get('Ibcia\Form\BusinessDateSetupForm');*/
					}
					
					return $viewModel->setVariable('error', 'Access denied : Unauthorized user information');
				}
			}
		}
		
		public function logoutAction()
		{
			$this->identityManager->logout();
			return $this->redirect()->toRoute('login');
		}
	}
?>	