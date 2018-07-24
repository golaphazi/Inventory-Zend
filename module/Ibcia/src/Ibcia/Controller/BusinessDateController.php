<?php
	//filename : module/Ibcia/src/Ibcia/Controller/BusinessDateController.php
	namespace Ibcia\Controller;
	 
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\ServiceManager\ServiceLocatorInterface;
	use Zend\View\Model\ViewModel;
	use Ibcia\Model\BusinessDate;
	use Ibcia\Form\BusinessDateSetupForm;
	use Ibcia\Form\MarketPriceFileUploadForm;
	use Ibcia\Form\SODForm;
	use Ibcia\Form\EODForm;
	use Zend\Session\Container as SessionContainer;
	
	class BusinessDateController extends AbstractActionController {
		protected $businessDateTable;
		protected $holidayTable;
		protected $marketPriceHistoryTable;
		protected $trialBalanceTable;
		
		public function indexAction() {
			
			$userInfo = \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			
			//redirect to index controller...
			if ($userInfo->id &&
				strtolower($userInfo->role) !== 'administrator' &&
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
			if ($userInfo->id &&
				null == $this->getBusinessDateTable()->currentBusinessDate) {
					
				$lastBusinessDate = $this->getBusinessDateTable()->getLastBusinessDate();
				//$lastBusinessDate = '01-01-2015';
				
				$holidayList = $this->getHolidayTable()->getHolidays($lastBusinessDate);
				$isHolidayCalendarSet = $holidayList;
				$holidays = array();
				foreach($holidayList as $row) {
					$holidays['HD'][] = $row->HOLIDAY_DATE;
					$holidays['JSHD'][] = $row->JSHD;
				}
				
				if(!empty($holidays)) { //If holiday calender set
					
					$jshd 					= $holidays['JSHD'];
					$hd 					= $holidays['HD'];
					
					$totalNonTradingDays = 0;
					do {
						$totalNonTradingDays++;
						$newBusinessDate = date('d-m-Y', strtotime("$lastBusinessDate +$totalNonTradingDays Days"));
					} while(in_array($newBusinessDate,$hd));
					
					$jshd 		= "['".implode("','",$jshd)."']";
					$jBMinDate 	= date('m/d/Y', strtotime("$newBusinessDate"));
					$jBMaxDate 	= '12/31/' . date('Y', strtotime("$newBusinessDate"));
					
					$form = new BusinessDateSetupForm($newBusinessDate, $jBMinDate);
					
					$viewModel->setVariable('jshd', $jshd);
					$viewModel->setVariable('jBMinDate', $jBMinDate);
					$viewModel->setVariable('jBMaxDate', $jBMaxDate);
					$this->addBusinessDate($form, $viewModel);
				} else {
					$viewModel->setTemplate('ibcia/holiday/index');
					$form = new \Ibcia\Form\HolidayForm();
					$this->addHoliday($form, $viewModel);
				}
			}
			
			//SOD db backup execution block...
			if($userInfo->id &&
			   null !== $this->getBusinessDateTable()->currentBusinessDate &&
			   null == $this->getBusinessDateTable()->isSODBkupFinished) {
				$viewModel->setTemplate('ibcia/sod/sod-backup');
				$form = new SODForm();
				$form->get('btnSOD')->setValue('Backup Database');
				$this->sodDbBackup($form, $viewModel);
			}
			
			//SOD execution block...
			if ($userInfo->id &&
				null !== $this->getBusinessDateTable()->currentBusinessDate &&
				null !== $this->getBusinessDateTable()->isSODBkupFinished &&
				null == $this->getBusinessDateTable()->isSODFinished) {
				$viewModel->setTemplate('ibcia/sod/sod');
				$form = new SODForm();
				$form->get('btnSOD')->setValue('Run SOD');
				$this->runSODProcess($form, $viewModel);
			}
			
			//EOD db backup execution block...
			if ($userInfo->id &&
				null !== $this->getBusinessDateTable()->currentBusinessDate &&
				null !== $this->getBusinessDateTable()->isSODBkupFinished &&
				null !== $this->getBusinessDateTable()->isSODFinished &&
				null == $this->getBusinessDateTable()->isEODBkupFinished) {
				
				$viewModel->setTemplate('ibcia/eod/eod-backup');
				$form = new EODForm();
				$form->get('btnEOD')->setValue('Backup Database');
				$this->eodDbBackup($form, $viewModel);
			}
			
			//EOD execution block...
			if ($userInfo->id &&
				null !== $this->getBusinessDateTable()->currentBusinessDate &&
				null !== $this->getBusinessDateTable()->isSODBkupFinished &&
				null !== $this->getBusinessDateTable()->isSODFinished &&
				null !== $this->getBusinessDateTable()->isEODBkupFinished &&
				null == $this->getBusinessDateTable()->isEODFinished) {
					
				$viewModel->setTemplate('ibcia/eod/eod');
				$form = new EODForm();
				$form->get('btnEOD')->setValue('Run EOD');
				$this->runEODProcess($form, $viewModel);
			}
			
			$viewModel->setVariable('form', $form);
			return $viewModel;
		}
		
		public function eodAction() {
			return $this->redirect()->toRoute('businessdate');
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function addHoliday($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$holiday = new \Ibcia\Model\Holiday();
				$form->setInputFilter($holiday->getInputFilter());
				$form->setData($request->getPost());
				
				if ($form->isValid()) {
					$formData = $form->getData();
					print_r($formData);
					$types = (isset($formData['hType'])) ? $formData['hType'] : array();
					$dates = (isset($formData['hDate'])) ? $formData['hDate'] : array();
					$descs = (isset($formData['hDesc'])) ? $formData['hDesc'] : array();
					echo '<pre>';
					print_r($types);
					print_r($dates);
					print_r($descs);die();
					foreach($dates as $key => $hd) {
						if(preg_match("/\d{1,2}-\d{1,2}-\d{4}/", $hd)) {///(\d{4})-(\d{1,2})-(\d{1,2})/
							$data = array(
								'HOLIDAY_DATE' 			=> 	$hd,
								'HOLIDAY_TYPE' 			=>	(isset($types[$key])) ? $types[$key] : '',
								'HOLIDAY_DESCRIPTION'	=>	(isset($descs[$key])) ? $descs[$key] : ''
							);
							$holiday->exchangeArray($data);
							
							$this->getHolidayTable()->saveHoliday($holiday);
						}
					}
					
					return $this->redirect()->toRoute('businessdate');
				}
			}
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
					
					if(isset($formData['btnLogout']) && (strtolower($formData['btnLogout']) == 'logout')) {
						return $this->redirect()->toRoute('login', array('action' => 'logout'));
					} else {
						$data = array(
									'BUSINESS_DATE' 	=> $formData['BUSINESS_DATE'],
									'SOD_BKUP'			=> 'y',
									'SOD_FLAG' 			=> null,
									'EOD_BKUP'			=> 'y',
									'EOD_FLAG' 			=> null,
									'DATE_CLOSE' 		=> null,
								);
						$businessDate->exchangeArray($data);
						if($this->getBusinessDateTable()->saveBusinessDate($businessDate)) {
							
							// Session Business Date Start
							$this->session = new SessionContainer('post_supply');
							$this->session->businessdate	= '';
							$this->session->businessdate 	= date("d-M-Y", strtotime($businessDate->BUSINESS_DATE));
							// Session Business Date End
							 
							$uEHolidays = explode(',', $formData['uEHDays']);
							foreach($uEHolidays as $key => $ueh) {
								if(preg_match("/\d{1,2}-\d{1,2}-\d{4}/", $ueh)) {
									$holiday = new \Ibcia\Model\Holiday();
									$uehData = array(
										'HOLIDAY_DATE' 			=> 	$ueh,
										'HOLIDAY_TYPE' 			=>	'Unexpected Holiday',
										'HOLIDAY_DESCRIPTION'	=>	'Unexpected holiday declaration'
									);
									$holiday->exchangeArray($uehData);
									//echo $ueh;
									$this->getHolidayTable()->saveHoliday($holiday);
								}
							}
							return $this->redirect()->toRoute('businessdate');
						}
					}
				}
			}
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function sodDbBackup($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$businessDate = new BusinessDate();
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $form->getData();
					
					if(isset($formData['btnLogout']) && (strtolower($formData['btnLogout']) == 'logout')) {
						return $this->redirect()->toRoute('login', array('action' => 'logout'));
					} else {
						$data = array(
									'BUSINESS_DATE' 	=> $this->getBusinessDateTable()->currentBusinessDate,
									'SOD_BKUP'			=> (isset($formData['btnSOD']) && (strtolower($formData['btnSOD']) == 'backup database')) ? 'y' : null,
									'SOD_FLAG' 			=> null,
									'EOD_BKUP'			=> null,
									'EOD_FLAG' 			=> null,
									'DATE_CLOSE' 		=> null,
								);
						$businessDate->exchangeArray($data);
						if($this->getBusinessDateTable()->runSODDbBackup($businessDate)) {
							return $this->redirect()->toRoute('businessdate');
						}
					}
				}
			}
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function runSODProcess($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$businessDate = new BusinessDate();
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $form->getData();
					
					if(isset($formData['btnLogout']) && (strtolower($formData['btnLogout']) == 'logout')) {
						return $this->redirect()->toRoute('login', array('action' => 'logout'));
					} else {
						$uHDays = $this->getHolidayTable()->sodUnprocessedHolidays($this->getBusinessDateTable()->currentBusinessDate);
						//print_r($uHDays);die();
						$sodFlag = false;
						$this->getBusinessDateTable()->transectionStart();
						for($uhd = 0; $uhd < sizeof($uHDays); $uhd++) {
							$data = array(
										'BUSINESS_DATE' 	=> $uHDays[$uhd],
										'SOD_BKUP'			=> $this->getBusinessDateTable()->isSODBkupFinished,
										'SOD_FLAG' 			=> (isset($formData['btnSOD']) && (strtolower($formData['btnSOD']) == 'run sod')) ? 'y' : null,
										'EOD_BKUP'			=> 'y',
										'EOD_FLAG' 			=> null,
										'DATE_CLOSE' 		=> null,
									);
							$businessDate->exchangeArray($data);
							
							if($this->getBusinessDateTable()->runSOD($businessDate)) {
								if($this->getTrialBalanceTable()->updateTrialBalance($businessDate->BUSINESS_DATE)) {
									$sodFlag = true;
								} else {
									$sodFlag = false;
								}
							} else {
								$sodFlag = false;
								break;
							}
						}
						
						if($sodFlag) {
							$this->getBusinessDateTable()->transectionEnd();
							return $this->redirect()->toRoute('index');
						} else {
							$this->getBusinessDateTable()->transectionInterrupted();
							return $this->redirect()->toRoute('businessdate');
						}
						//$viewModel->setVariable('error', 'Access denied : Unauthorized user information');
					}
				}
			}
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function marketPriceImport($form, $viewModel) {
			$userInfo = \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			
			$request = $this->getRequest();
			if ($request->isPost()) {
				$marketPriceHistory = new \Ibcia\Model\MarketPriceHistory();
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $request->getPost();//$form->getData();
					$fileName = 'market_price_'.str_replace('-','_',$this->getBusinessDateTable()->currentBusinessDate).'.csv';
					if(isset($formData['btnBack']) && (strtolower($formData['btnBack']) == 'back')) {
						
						/*if(file_exists('public/uploaddir/marketprice/'.$fileName)) {
							unlink(getenv('ZF2_PATH').'/public/uploaddir/marketprice/'.$fileName);
						}*/
						
						return $this->redirect()->toRoute('index', array('action' => 'index'));
						
					} else if(isset($formData['btnLogout']) && (strtolower($formData['btnLogout']) == 'logout')) {
						
						/*if(file_exists('public/uploaddir/marketprice/'.$fileName)) {
							unlink(getenv('ZF2_PATH').'/public/uploaddir/marketprice/'.$fileName);
						}*/
						
						return $this->redirect()->toRoute('login', array('action' => 'logout'));
						
					} else {
						$lInstrumentDetailsId = $formData['evenLotScrip'];
						$lOpenPrice = $formData['evenLotOpen'];
						$lHighPrice = $formData['evenLotHigh'];
						$lLowPrice = $formData['evenLotLow'];
						$lClosePrice = $formData['evenLotClose'];
						$lTotalTrade = $formData['evenLotTrade'];
						$lVolume = $formData['evenLotVolume'];
						$lValueInMn = $formData['evenLotValue'];
						
						
						$nlInstrumentDetailsId = isset($formData['NLScrip']) ? $formData['NLScrip'] : array();
						$nlClosePrice = isset($formData['NLClose']) ? $formData['NLClose'] : array();
						$this->getMarketPriceHistoryTable()->transectionStart();
						$isMktPriceImportedSuccessfully = true;
						if(sizeof($lInstrumentDetailsId) > 0) {
							for($lInst = 0; $lInst < sizeof($lInstrumentDetailsId); $lInst++) {
								$marketPriceData = array(
									'INSTRUMENT_DETAILS_ID' 	=> $lInstrumentDetailsId[$lInst],
									'OPEN_PRICE' 				=> $lOpenPrice[$lInst],
									'HIGH_PRICE' 				=> $lHighPrice[$lInst],
									'LOW_PRICE' 				=> $lLowPrice[$lInst],
									'CLOSE_PRICE' 				=> $lClosePrice[$lInst],
									'TOTAL_TRADE' 				=> $lTotalTrade[$lInst],
									'VOLUME' 					=> $lVolume[$lInst],
									'VALUE_IN_MN' 				=> $lValueInMn[$lInst],
									'BUSINESS_DATE' 			=> $this->getBusinessDateTable()->currentBusinessDate,
									'RECORD_DATE' 				=> $this->getBusinessDateTable()->currentBusinessDate,
									'OPERATE_BY' 				=> $userInfo->id,
								);
								$marketPriceHistory->exchangeArray($marketPriceData);
								if($this->getMarketPriceHistoryTable()->saveMarketPriceHistory($marketPriceHistory)) {
									$isMktPriceImportedSuccessfully = true;
								} else {
									$isMktPriceImportedSuccessfully = false;
									break;
								}
							}
						}
						
						if((sizeof($nlInstrumentDetailsId) > 0) && $isMktPriceImportedSuccessfully) {
							for($nlInst = 0; $nlInst < sizeof($nlInstrumentDetailsId); $nlInst++) {
								$marketPriceData = array(
									'INSTRUMENT_DETAILS_ID' 	=> $nlInstrumentDetailsId[$nlInst],
									'CLOSE_PRICE' 				=> $nlClosePrice[$nlInst],
									'BUSINESS_DATE' 			=> $this->getBusinessDateTable()->currentBusinessDate,
									'RECORD_DATE' 				=> $this->getBusinessDateTable()->currentBusinessDate,
									'OPERATE_BY' 				=> $userInfo->id,
								);
								$marketPriceHistory->exchangeArray($marketPriceData);
								if($this->getMarketPriceHistoryTable()->saveMarketPriceHistory($marketPriceHistory)) {
									$isMktPriceImportedSuccessfully = true;
								} else {
									$isMktPriceImportedSuccessfully = false;
									break;
								}
							}
						}
						
						if($isMktPriceImportedSuccessfully){
							$businessDate = new BusinessDate();
							$data = array(
										'BUSINESS_DATE' 	=> $this->getBusinessDateTable()->currentBusinessDate,
										'SOD_BKUP'			=> $this->getBusinessDateTable()->isSODBkupFinished,
										'SOD_FLAG' 			=> $this->getBusinessDateTable()->isSODFinished,
										'EOD_BKUP'			=> null,
										'EOD_FLAG' 			=> null,
										'DATE_CLOSE' 		=> null,
									);
							$businessDate->exchangeArray($data);
							if($this->getBusinessDateTable()->saveBusinessDate($businessDate)) {
								$isMktPriceImportedSuccessfully = true;
							} else {
								$isMktPriceImportedSuccessfully = false;
							}
						}
						
						if($isMktPriceImportedSuccessfully) {
							$this->getMarketPriceHistoryTable()->transectionEnd();
							
							/*if(file_exists('public/uploaddir/marketprice/'.$fileName)) {
								unlink(getenv('ZF2_PATH').'/public/uploaddir/marketprice/'.$fileName);
							}*/
							
							return $this->redirect()->toRoute('businessdate');
							
						} else {
							$this->getMarketPriceHistoryTable()->transectionInterrupted();
							
							/*if(file_exists('public/uploaddir/marketprice/'.$fileName)) {
								unlink(getenv('ZF2_PATH').'/public/uploaddir/marketprice/'.$fileName);
							}*/
							
							return $viewModel->setVariable('error', 'Sorry, market price import interrupted!');
						}
					}
				}
			}
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function eodDbBackup($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$businessDate = new BusinessDate();
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $form->getData();
					
					if(isset($formData['btnBack']) && (strtolower($formData['btnBack']) == 'back')) {
						return $this->redirect()->toRoute('index', array('action' => 'index'));
					} else if(isset($formData['btnLogout']) && (strtolower($formData['btnLogout']) == 'logout')) {
						return $this->redirect()->toRoute('login', array('action' => 'logout'));
					} else {
						$data = array(
									'BUSINESS_DATE' 	=> $this->getBusinessDateTable()->currentBusinessDate,
									'SOD_BKUP'			=> $this->getBusinessDateTable()->isSODBkupFinished,
									'SOD_FLAG' 			=> $this->getBusinessDateTable()->isSODFinished,
									'EOD_BKUP'			=> (isset($formData['btnEOD']) && (strtolower($formData['btnEOD']) == 'backup database')) ? 'y' : null,
									'EOD_FLAG' 			=> null,
									'DATE_CLOSE' 		=> null,
								);
						$businessDate->exchangeArray($data);
						if($this->getBusinessDateTable()->runEODDbBackup($businessDate)) {
							return $this->redirect()->toRoute('businessdate/eod');
						}
					}
				}
			}
		}
		
		/** this function called by indexAction to reduce complexity of function */
		protected function runEODProcess($form, $viewModel) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$businessDate = new BusinessDate();
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$formData = $form->getData();
					
					if(isset($formData['btnBack']) && (strtolower($formData['btnBack']) == 'back')) {
						return $this->redirect()->toRoute('index', array('action' => 'index'));
					} else if(isset($formData['btnLogout']) && (strtolower($formData['btnLogout']) == 'logout')) {
						return $this->redirect()->toRoute('login', array('action' => 'logout'));
					} else {
						$data = array(
									'BUSINESS_DATE' 	=> $this->getBusinessDateTable()->currentBusinessDate,
									'SOD_BKUP'			=> $this->getBusinessDateTable()->isSODBkupFinished,
									'SOD_FLAG' 			=> $this->getBusinessDateTable()->isSODFinished,
									'EOD_BKUP'			=> $this->getBusinessDateTable()->isEODBkupFinished,
									'EOD_FLAG' 			=> (isset($formData['btnEOD']) && (strtolower($formData['btnEOD']) == 'run eod')) ? 'y' : null,
									'DATE_CLOSE' 		=> (isset($formData['btnEOD']) && (strtolower($formData['btnEOD']) == 'run eod')) ? 'y' : null,
								);
						$businessDate->exchangeArray($data);
						//echo "<pre>"; print_r($businessDate); die();
						if($this->getBusinessDateTable()->runEOD($businessDate)) {
							if($this->getTrialBalanceTable()->updateTrialBalance($businessDate->BUSINESS_DATE)) {
								return $this->redirect()->toRoute('login', array('action' => 'logout'));	
							} else {
								return $this->redirect()->toRoute('login', array('action' => 'logout'));
							}
							
						}
					}
				}
			}
		}
		
		public function getTrialBalanceTable() {
			if(!$this->trialBalanceTable) {
				$sm = $this->getServiceLocator();
				$this->trialBalanceTable = $sm->get('Accounts\Model\TrialBalanceTable');
			}
			return $this->trialBalanceTable;
		}
		
		private function getBusinessDateTable() {
			if(!$this->businessDateTable) {
				$sm = $this->getServiceLocator();
				$this->businessDateTable = $sm->get('BusinessDateTable');
			}
			return $this->businessDateTable;
		}
		
		private function getHolidayTable() {
			if(!$this->holidayTable) {
				$sm = $this->getServiceLocator();
				$this->holidayTable = $sm->get('HolidayTable');
			}
			return $this->holidayTable;
		}
		
		private function getMarketPriceHistoryTable() {
			if(!$this->marketPriceHistoryTable) {
				$sm = $this->getServiceLocator();
				$this->marketPriceHistoryTable = $sm->get('MarketPriceHistoryTable');
			}
			return $this->marketPriceHistoryTable;
		}
	}
?>	