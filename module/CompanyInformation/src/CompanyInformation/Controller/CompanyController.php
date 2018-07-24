<?php
	namespace CompanyInformation\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use CompanyInformation\Model\Company;
	use CompanyInformation\Form\CompanyForm;
	
	use Zend\Session\Container as SessionContainer;
	
	use Zend\Mail\Message;
	use Zend\Mail\Transport\Smtp as SmtpTransport;
	use Zend\Mime\Message as MimeMessage;
	use Zend\Mime\Part as MimePart;
	use Zend\Mail\Transport\SmtpOptions;
	
	
	class CompanyController extends AbstractActionController {
		protected $companyTable;
			
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Company Information',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			return new ViewModel(array(
				'companys' => $this->getCompanyTable()->fetchAll(),
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));	
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Company Information',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new CompanyForm();
			$form->get('submit')->setValue('Add');
			$request = $this->getRequest();
			if($request->isPost()) {
				$company = new Company();
				$form->setInputFilter($company->getInputFilter());
				$form->setData($request->getPost());
				
				if($form->isValid()) {
					$company->exchangeArray($request->getPost());
					$this->getCompanyTable()->saveCompany($company);
					//mail sending Start ///	
					/*$config = array('ssl' => 'tls',
									'auth' => 'login',
									'port' => 25,
									'username' => 'mohsin@capstonefintech.com',
									'password' => 'Attunes@ft3214');
					$message = new Message();
					$message->addFrom('mail@capstonefintech.com', 'Some Sender');
					$message->addTo('romellikeyou@yahoo.com', 'Some Recipient');
					$message->setSubject('TestSubject');					
					$content = 'This is the text of the mail';
					$body = new MimeMessage();
					$htmlPart = new MimePart($content);
					$htmlPart->type = 'text/html';
					$body->setParts(array($htmlPart));
					
					$message->setBody($body);
					$message->setEncoding('UTF-8');
					
					 // Setup SMTP transport using LOGIN authentication
					$transport = new SmtpTransport();
					$options   = new SmtpOptions(array(
						'name'              => 'www.capstonefintech.com',
						'host'              => 'www.capstonefintech.com',
						'connection_class'  => 'login',
						'connection_config' => array(
							'username' => $config['username'],
							'password' => $config['password'],
						),
					));
					$transport->setOptions($options);
					mail('romellikeyou@yahoo.com','subject','body');
					try {
						//$transport->send($message);
					} catch (\Exception $e) {
						//error_log($e->getMessage());
						throw new \Exception($e->getMessage());
					}*/
					//mail sending end ///
					
					
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Company [ ".$company->COMPANY_NAME." ] added successfully!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('company');
				}
			}
		
			return array('form' => $form);
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Company Information',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
		   	$id 	= (int) $this->params()->fromRoute('id',0);
			
			if(!$id) {
				return $this->redirect()->toRoute('company',array('action' => 'add'));
			}
			
			try {
				$company	= $this->getCompanyTable()->getCompany($id);
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('company', array('action' => 'index'));
			}
			
			$form 	= new CompanyForm('company', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->bind($company);
			$form->get('submit')->setAttribute('value','Edit');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$form->setData($request->getPost());
				if($form->isValid()) {
					$company->exchangeArray($request->getPost());
					$this->getCompanyTable()->saveCompany($company);
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Company [ ".$company->COMPANY_NAME." ] edit successfully!</h4></td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('company');
				}
			}
			
			return array(
				'id' => $id,
				'form' => $form,
			);
		}
		
		public function activeDeactiveCompanyAction() {
			$companyId 	=  $this->params()->fromQuery('companyId', 0);
			$ststus 	=  $this->params()->fromQuery('ststus');
			
			$success	= '';
			$action		= '';
			if($companyId == 0) {
				throw new \Exception("Invalid id");
			} else {
				$companyInfo	= $this->getCompanyTable()->getCompany($companyId);
				$success		= $this->getCompanyTable()->activeDeactiveCompany($companyId,$ststus);
				if(strtolower($ststus) == 'y') {
					$action = "active";	
				} else {
					$action = "deactive";	
				}
				
				$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Company [ ".$companyInfo->COMPANY_NAME." ] ".$action." successfully!</h4></td>
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
	}
?>