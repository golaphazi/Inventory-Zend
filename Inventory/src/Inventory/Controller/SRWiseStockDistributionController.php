<?php
	namespace Inventory\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Inventory\Form\SRWiseStockDistributionForm;
	use Accounts\Model\TrialBalanceTable;
	
	use Inventory\Model\SRStockOrder;
	use Inventory\Model\SRStockDetails;
	
	use Zend\Session\Container as SessionContainer;
	
	class SRWiseStockDistributionController extends AbstractActionController {		
		protected $dbAdapter;
		protected $portfolioTrialBalanceTable;
		protected $branchTable;
		protected $coaTable;
		protected $trialBalanceTable;
		protected $srstockOrderTable;
		protected $srstockDetailsTable;
		protected $supplierInformationTable;
		protected $stockEntryTable;
				
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Inventory',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new SRWiseStockDistributionForm('srstockentry', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$srstockdetails = new SRStockDetails();
				$form->setInputFilter($srstockdetails->getInputFilter());
				$form->setData($request->getPost());
				//echo '<pre>';print_r($request->getPost());die();
				//if($form->isValid()) {
					$msg = '';
					$srstockData	= array();
					$postedData = $request->getPost();
					for($i = 1; $i < $postedData->NumberOfRows; $i++) {
						$cOACodeData = $postedData["CATEGORY_ID{$i}"];
						if(!empty($cOACodeData)) {
							$j	= $i-1;
							$srstockData['CATEGORY_ID'][$j]				= $postedData["CATEGORY_ID{$i}"];
							$srstockData['CAT_PRICE_ID'][$j]			= $postedData["CAT_PRICE_ID{$i}"];
							$srstockData['SR_QUANTITY'][$j]				= $postedData["QUANTITY{$i}"];
							$srstockData['SR_BUY_PRICE'][$j]			= $postedData["BUY_PRICE{$i}"];
							$srstockData['SR_TOTAL_AMOUNT'][$j]			= $postedData["TOTAL_AMOUNT{$i}"];
							$srstockData['SR_DISCOUNT'][$j]				= $postedData["DISCOUNT{$i}"];
							$srstockData['SR_AVG_RATE'][$j]				= $postedData["AVG_RATE{$i}"];
							$srstockData['SR_NET_AMOUNT'][$j]			= $postedData["NET_AMOUNT{$i}"];
						}
					}
					
					$srstockData['BRANCH_ID'] 							= $postedData["BRANCH_ID"];
					$srstockData['tranDateTo']							= $postedData["tranDateTo"];
					$srstockData['insertJournal']						= $postedData["insertJournal"];
					$srstockData['NumberOfRows']						= $postedData["NumberOfRows"];
					$srstockData['DUE']									= $postedData["DUE"];
					$srstockData['NET_PAYMENT']							= $postedData["NET_PAYMENT"];
					$srstockorder = new SRStockOrder();
					$srstockorderData = array();
					$srstockorderData['ORDER_NO'] 							= $postedData["ORDER_NO"];
					$srstockorderData['EMPLOYEE_ID'] 						= $postedData["EMPLOYEE_ID"];
					$srstockorderData['SR_TOTAL_AMOUNT']					= str_replace(",", "", $postedData["TOTALAMOUNT_HIDDEN"]);
					$srstockorderData['SR_DISCOUNT_AMOUNT']					= str_replace(",", "", $postedData["TOTALDISCOUNT_HIDDEN"]);
					$srstockorder->exchangeArray($srstockorderData);
					$srStockOrderID = $this->getSRStockOrderTable()->saveSRStockOrder($srstockorder);
					if($srStockOrderID > 0){
						$srstockData['SR_STOCK_DIST_ID']					= $srStockOrderID;
						$srstockdetails->exchangeArray($srstockData);
						if($msg = $this->getSRStockDetailsTable()->saveSRStockDetails($srstockdetails)) {
							$success = true;
							$msg = "Successfull";
						} else {
							$success = false;
							$msg = "System Error";
							throw new \Exception("Error: Cannot add stock information into system!");
						}
					} else {
						$success = false;
						throw new \Exception("Error: Cannot add stock order into system!");
					}
					if($success) {
						//$this->getStockEntryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																<tr class='valid_msg'>
																	<td width='100%' style='text-align:center;'>{$msg}</td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('srstockentry');
					} else {
						//$this->getStockEntryTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																<tr class='error_msg'>
																	<td width='100%' style='text-align:center;'>{$msg}</td>
																</tr>
															</table>");
					}
			}			
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		public function getJournalDrCrSelectAction() {
			$coaCode  = strtolower( $_REQUEST['coaCode'] );
			$motherHead      ='';
			$motherHeadRadio ='';
			if($coaCode) {
				$IPAData = $this->getCoaTable()->fetchMotherAccount($coaCode);			
				foreach ($IPAData as $selectOption) {
					$motherHead = $selectOption['MOTHER_ACCOUNT'];
				}
			} else {
				$getTBAmountSql = '';
			}
			//echo json_encode($motherHead);
			echo $motherHead;
			exit;
		}
		public function getCBCOACodeAction() {
			$cbCode = $_REQUEST['cbCode'];
			$cbCOAList = '';
			$cbCOAList_array = array();
			$IPAData = $this->getCoaTable()->fetchCOABankOrCash($cbCode);			
			foreach ($IPAData as $selectOption) {
				$coa_code = $selectOption['COA_CODE'];
				$coa_name = $selectOption['COA_NAME'];
				$cbCOAList_array[] = array('optionValue'=>$coa_code,'optionDisplay'=>$coa_code."-".$coa_name);
			}
			echo json_encode($cbCOAList_array);
			//echo $cbCOAList_array;
			exit;
		}
		
		public function getSupllierListAction() {
			$cbCOAList_array = array();
			$IPAData = $this->getSupplierInformationTable()->getSuppInfo();			
			foreach ($IPAData as $selectOption) {
				$suppId = $selectOption['SUPPLIER_INFO_ID'];
				$suppName = $selectOption['NAME'];
				$cbCOAList_array[] = array('optionValue'=>$suppId,'optionDisplay'=>$suppName);
			}
			echo json_encode($cbCOAList_array);
			//echo '<pre>';print_r($cbCOAList_array);die();
			exit;
		}
		
		
		public function getSuggestRefCOANameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower($_REQUEST['no']);
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getStockEntryTable()->fetchStockWiseModelDetails($input);	
			$coaCode = '';		
			foreach ($IPAData as $selectOption) {
				$coaCode = $selectOption['CATEGORY_ID'];
				$coaHead = $selectOption['CATEGORY_NAME'];				
				$coaCodeHead = $coaCode.",".$coaHead;
				$sumOfQuantity = $this->getStockEntryTable()->fetchSumofStockQuantity($coaCode);
				$totalSumOfQuantity = '';
				foreach ($sumOfQuantity as $sumOfQuantityData) {
					$totalSumOfQuantity = $sumOfQuantityData['QUANTITY'];
					$sellPrice 			= $sumOfQuantityData['SALE_PRICE'];
					$catPriceID 		= $sumOfQuantityData['CAT_PRICE_ID'];
				}
				$str .= "<div align='left' onClick=\"fill_id('".$coaCodeHead."','".$no."','".$sellPrice."','".$catPriceID."','".$totalSumOfQuantity."');\"><b>".$coaHead."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		public function getSuggestRefCOACodeAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );			
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getCoaTable()->fetchCOACode($input);			
			foreach ($IPAData as $selectOption) {
				$coaCode = $selectOption['COA_CODE'];
				$coaHead = $selectOption['COA_NAME'];
				$coaCodeHead = $coaCode.",".$coaHead;
				$str .= "<div align='left' onClick=\"fill_id_code('".$coaCodeHead."','".$no."');\"><b>".$coaCode."-".$coaHead."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		
		public function getPortfolioTrialBalanceTable() {
			if(!$this->portfolioTrialBalanceTable) {
				$sm = $this->getServiceLocator();
				$this->portfolioTrialBalanceTable = $sm->get('Accounts\Model\PortfolioTrialBalanceTable');
			}
			return $this->portfolioTrialBalanceTable;
		}
		public function getBranchTable() {
			if(!$this->branchTable) {
				$sm = $this->getServiceLocator();
				$this->branchTable = $sm->get('Company\Model\BranchTable');
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
		public function getTrialBalanceTable() {
			if(!$this->trialBalanceTable) {
				$sm = $this->getServiceLocator();
				$this->trialBalanceTable = $sm->get('Accounts\Model\TrialBalanceTable');
			}
			return $this->trialBalanceTable;
		}
		public function getSRStockOrderTable() {
			if(!$this->srstockOrderTable) {
				$sm = $this->getServiceLocator();
				$this->srstockOrderTable = $sm->get('Inventory\Model\SRStockOrderTable');
			}
			return $this->srstockOrderTable;
		}
		public function getSRStockDetailsTable() {
			if(!$this->srstockDetailsTable) {
				$sm = $this->getServiceLocator();
				$this->srstockDetailsTable = $sm->get('Inventory\Model\SRStockDetailsTable');
			}
			return $this->srstockDetailsTable;
		}
		public function getSupplierInformationTable() {
			
			if(!$this->supplierInformationTable) {
				$sm = $this->getServiceLocator();
				$this->supplierInformationTable = $sm->get('LocalSetting\Model\SupplierInformationTable');
			}
			return $this->supplierInformationTable;
		}
		public function getStockEntryTable() {
			if(!$this->stockEntryTable) {
				$sm = $this->getServiceLocator();
				$this->stockEntryTable = $sm->get('Inventory\Model\StockEntryTable');
			}
			return $this->stockEntryTable;
		}
	}
?>