<?php
	namespace Inventory\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Inventory\Form\ConsumptionEntryForm;
	use Accounts\Model\TrialBalanceTable;
	
	use Inventory\Model\ConsumptionEntry;
	
	use Accounts\Model\Master;
	use Accounts\Model\Child;
	use Accounts\Model\Voucher;
	
	
	use Zend\Session\Container as SessionContainer;
	
	class ConsumptionEntryController extends AbstractActionController {		
		protected $dbAdapter;
		protected $portfolioTrialBalanceTable;
		protected $branchTable;
		protected $coaTable;
		protected $trialBalanceTable;
		protected $stockOrderTable;
		protected $stockEntryTable;
		protected $supplierInformationTable;
		protected $voucherTable;
		protected $masterTable;
		protected $childTable;
		protected $consumptionEntryTable;
				
		public function indexAction() {
			//echo 'hi there';die();
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Inventory',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new ConsumptionEntryForm('consumptionentry', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$consumptionentry = new ConsumptionEntry();
				$form->setInputFilter($consumptionentry->getInputFilter());
				$form->setData($request->getPost());
				//echo '<pre>';print_r($request->getPost());die();
				//if($form->isValid()) {
					$msg = '';
					$consumptionData	= array();
					$postedData = $request->getPost();
					$this->session 		= new SessionContainer('post_supply');
					$businessDate 		= $this->session->businessdate;
					$BRANCH_ID			= 1;//$this->session->branchid;
					for($i = 1; $i < $postedData->NumberOfRows; $i++) {
						$categoryID = $postedData["CATEGORY_ID{$i}"];
						if(!empty($categoryID)) {
							$j	= $i-1;
							$consumptionData['CATEGORY_ID'][$j]					= $postedData["CATEGORY_ID{$i}"];
							$consumptionData['CATEGORY'][$j]					= $postedData["CATEGORY{$i}"];
							$consumptionData['QUANTITY'][$j]					= str_replace(",","",$postedData["QUANTITY{$i}"]);
							$consumptionData['RATE'][$j]						= str_replace(",","",$postedData["RATE{$i}"]);
							$consumptionData['TOTAL_AMOUNT'][$j]				= str_replace(",","",$postedData["TOTAL_AMOUNT{$i}"]);						
							$consumptionData['CAT_PRICE_ID'][$j]				= $postedData["CAT_PRICE_ID{$i}"];
							$consumptionData['COA_CODE'][$j]					= $postedData["COA_CODE{$i}"];
							$consumptionData['COA_NAME'][$j]					= $postedData["COA_NAME{$i}"];
						}
					}					
					$consumptionData['BRANCH_ID'] 								= $BRANCH_ID;
					$consumptionData['NumberOfRows']							= $postedData["NumberOfRows"];					
					$consumptionData['CONSUMPTION_NO']							= $postedData["CONSUMPTION_NO"];
					$consumptionData['NET_PAYMENT']								= str_replace(",","",$postedData["NET_PAYMENT"]);
					
					//echo '<pre>';print_r($consumptionData);die();
					if($postedData["isProduction"] == 'yes'){
						$supplierInfoID = '';
						$productedCategoryID = '';					
						for($k = 1; $k < $postedData->ManufecturedNumberOfRows; $k++) {
							$productedCategoryID = $postedData["P_CATEGORY_ID{$k}"];
							$msg = 'Supplier not found for Production Item!';							
							//echo $supplierInfoID;die();
							if(!empty($productedCategoryID)) {	
								$supplierInfoID = $this->getConsumptionEntryTable()->getCatWiseSupplierInfo($productedCategoryID);							
								if(!empty($supplierInfoID)) {
										//echo 'ekhane dhukce';die();
										$l	= $k-1;
										$consumptionData['P_CATEGORY_ID'][$l]				= $postedData["P_CATEGORY_ID{$k}"];
										$consumptionData['P_CATEGORY'][$l]					= $postedData["P_CATEGORY{$k}"];
										$consumptionData['P_QUANTITY'][$l]					= str_replace(",","",$postedData["P_QUANTITY{$k}"]);
										$consumptionData['P_RATE'][$l]						= str_replace(",","",$postedData["P_RATE{$k}"]);
										$consumptionData['P_TOTAL_AMOUNT'][$l]				= str_replace(",","",$postedData["P_TOTAL_AMOUNT{$k}"]);							
										$consumptionData['P_CAT_PRICE_ID'][$l]				= $postedData["P_CAT_PRICE_ID{$k}"];
										$consumptionData['P_COA_CODE'][$l]					= $postedData["P_COA_CODE{$k}"];
										$consumptionData['P_COA_NAME'][$l]					= $postedData["P_COA_NAME{$k}"];
									//}
								} else {
									//echo 'okhane dhukce';die();
									$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																			<tr class='error_msg'>
																				<td width='100%' style='text-align:center;'>{$msg}</td>
																			</tr>
																		</table>");
									return $this->redirect()->toRoute('consumptionentry');
								}
							}
						}
					}
					$consumptionData['ManufecturedNumberOfRows']				= $postedData["ManufecturedNumberOfRows"];					
					$consumptionData['isProduction']							= $postedData["isProduction"];
					$consumptionData['P_NET_PAYMENT']							= str_replace(",","",$postedData["P_NET_PAYMENT"]);
					$consumptionentry->exchangeArray($consumptionData);					
					//echo '<pre>';print_r($consumptionData);die();
					if($stockOrderID = $this->getConsumptionEntryTable()->saveConsumption($consumptionentry)) {
						//$stockData['STOCK_ORDER_ID'] = $stockOrderID;
						//echo $stockOrderID;die();
						$success = true;
						$msg = "Successfull";
					} else {
						$success = false;
						$msg = "System Error";
						throw new \Exception("Error: Cannot add stock consumption into system!");
					}
					if($success) {
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																<tr class='valid_msg'>
																	<td width='100%' style='text-align:center;'>{$msg}</td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('consumptionentry');
					} else {
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
		
		
		public function getSuggConsumptionProductAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower($_REQUEST['no']);
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getStockEntryTable()->fetchStockWiseModelDetails($input);	
			$coaCode = '';		
			foreach ($IPAData as $selectOption) {
				$catId = $selectOption['CATEGORY_ID'];
				$catName = $selectOption['CATEGORY_NAME'];
				$catIDName = $catId.",".$catName;
				$sumOfQuantity = $this->getStockEntryTable()->fetchSumofStockQuantity($catId);
				$totalSumOfQuantity = '';
				foreach ($sumOfQuantity as $sumOfQuantityData) {
					$totalSumOfQuantity = str_replace(",","",$sumOfQuantityData['QUANTITY']);
					$sellPrice 			= str_replace(",","",$sumOfQuantityData['SALE_PRICE']);
					$catPriceID 		= $sumOfQuantityData['CAT_PRICE_ID'];
					$coaCode 			= $sumOfQuantityData['COA_CODE'];
					$coaName 			= $sumOfQuantityData['COA_NAME'];
					$stockBuyPrice		= str_replace(",","",$sumOfQuantityData['BUY_PRICE']);
					$unitCalIn			= strtoupper($sumOfQuantityData['UNIT_CAL_IN']);
				}
				$str .= "<div align='left' onClick=\"fill_id('".$catIDName."','".$no."','".$stockBuyPrice."','".$catPriceID."','".$totalSumOfQuantity."','".$coaCode."','".$coaName."', '".$unitCalIn."');\"><b>".$catName."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		public function getSuggManufecturedProductAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getCoaTable()->fetchManufecturedProduct($input);
			foreach ($IPAData as $selectOption) {
				$catID = $selectOption['CATEGORY_ID'];
				$catName = $selectOption['CATEGORY_NAME'];
				$buyPrice = $selectOption['BUY_PRICE'];
				$catPriceID = $selectOption['CAT_PRICE_ID'];
				$coaCode = $selectOption['COA_CODE'];
				$coaName = $selectOption['COA_NAME'];
				$salePrice = $selectOption['SALE_PRICE'];
				$catIDName = $catID.",".$catName;
				$str .= "<div align='left' onClick=\"fillManufecturedProduct('".$catIDName."','".$no."','".$salePrice."','".$catPriceID."','".$coaCode."','".$coaName."');\"><b>".$catName."</b></div>";
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
		public function getSuppCOACodeAction() {
			$suppID = $_REQUEST['suppID'];
			$payableCoa='';
			$suppDetails_array = array();
			$IPAData = $this->getSupplierInformationTable()->getSuppCOACode($suppID);			
			foreach ($IPAData as $selectOption) {
				$payableCoa = $selectOption['PAYABLE_COA'];
				$payableCoaName = $selectOption['COA_NAME'];
				$suppShortName = $selectOption['SHORT_NAME'];
				$suppDetails_array[] = array('payableCoa'=>$payableCoa,'payableCoaName'=>$payableCoaName,'suppShortName'=>$suppShortName);
			}
			echo json_encode($suppDetails_array);
			//echo $payableCoa;
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
		public function getStockOrderTable() {
			if(!$this->stockOrderTable) {
				$sm = $this->getServiceLocator();
				$this->stockOrderTable = $sm->get('Inventory\Model\StockOrderTable');
			}
			return $this->stockOrderTable;
		}
		public function getStockEntryTable() {
			if(!$this->stockEntryTable) {
				$sm = $this->getServiceLocator();
				$this->stockEntryTable = $sm->get('Inventory\Model\StockEntryTable');
			}
			return $this->stockEntryTable;
		}
		public function getConsumptionEntryTable() {
			if(!$this->consumptionEntryTable) {
				$sm = $this->getServiceLocator();
				$this->consumptionEntryTable = $sm->get('Inventory\Model\ConsumptionEntryTable');
			}
			return $this->consumptionEntryTable;
		}
		public function getSupplierInformationTable() {
			
			if(!$this->supplierInformationTable) {
				$sm = $this->getServiceLocator();
				$this->supplierInformationTable = $sm->get('LocalSetting\Model\SupplierInformationTable');
			}
			return $this->supplierInformationTable;
		}
		public function getVoucherTable() {
			if(!$this->voucherTable) {
				$sm = $this->getServiceLocator();
				$this->voucherTable = $sm->get('Accounts\Model\VoucherTable');
			}
			return $this->voucherTable;
		}
		public function getMasterTable() {
			if(!$this->masterTable) {
				$sm = $this->getServiceLocator();
				$this->masterTable = $sm->get('Accounts\Model\MasterTable');
			}
			return $this->masterTable;
		}
		public function getChildTable() {
			if(!$this->childTable) {
				$sm = $this->getServiceLocator();
				$this->childTable = $sm->get('Accounts\Model\ChildTable');
			}
			return $this->childTable;
		}
	}
?>