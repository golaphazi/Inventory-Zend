<?php
	namespace Inventory\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	//use Inventory\Model\StockOrder;
	//use Inventory\Model\StockDetails;
	
	use Inventory\Model\StockOrder;
	use Inventory\Model\StockEntry;
	
	use Inventory\Form\StockInformationEditForm;
	
	use Zend\Session\Container as SessionContainer;
	
	class StockInformationEditController extends AbstractActionController {		
		protected $dbAdapter;
		protected $portfolioTrialBalanceTable;
		protected $branchTable;
		protected $coaTable;
		protected $trialBalanceTable;
		protected $stockOrderTable;
		protected $stockEntryTable;
		protected $supplierInformationTable;
				
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Inventory',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$request = $this->getRequest();
			$form = new StockInformationEditForm('stockinformationedit', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$stockentry = new StockEntry();
				$form->setInputFilter($stockentry->getInputFilter());
				$form->setData($request->getPost());
				$postedData = $request->getPost();
				//echo '<pre>';print_r($request->getPost());die();
				
				for($i = 0; $i < $postedData->NumberOfRows; $i++) {
					$cOACodeData = $postedData["CATEGORY_ID{$i}"];
					if(!empty($cOACodeData)) {
						$j	= $i-1;
						$stockData['STOCK_DETAILS_ID'][$i]			= $postedData["STOCK_DETAILS_ID{$i}"];
						$stockData['SUPPLIER_INFO_ID'][$i]			= $postedData["SUPPLIER_INFO_ID{$i}"];
						$stockData['CATEGORY_ID'][$i]				= $postedData["CATEGORY_ID{$i}"];
						$stockData['QUANTITY'][$i]					= $postedData["QUANTITY{$i}"];
						$stockData['RATE'][$i]						= $postedData["RATE{$i}"];
						$stockData['TOTAL_AMOUNT'][$i]				= $postedData["TOTAL_AMOUNT{$i}"];
						$stockData['DISCOUNT'][$i]					= $postedData["DISCOUNT{$i}"];
						$stockData['AVG_RATE'][$i]					= $postedData["AVG_RATE{$i}"];
						$stockData['NET_AMOUNT'][$i]				= $postedData["NET_AMOUNT{$i}"];
					}
				}
				
				$stockData['BRANCH_ID'] 							= $postedData["BRANCH_ID"];
				$stockData['tranDateTo']							= $postedData["tranDateTo"];
				$stockData['insertJournal']							= $postedData["insertJournal"];
				$stockData['NumberOfRows']							= $postedData["NumberOfRows"];
				$stockData['DUE']									= $postedData["DUE"];
				$stockData['NET_PAYMENT']							= $postedData["NET_PAYMENT"];
				
				
				
				$stockorder = new StockOrder();
				$stockorderData = array();
				$stockorderData['COND'] 							= $postedData["cond"];
				$stockorderData['STOCK_ORDER_ID'] 					= $postedData["STOCK_ORDER_ID"];
				$stockorderData['TOTAL_AMOUNT']						= str_replace(",", "", $postedData["TOTALAMOUNT_HIDDEN"]);
				$stockorderData['DISCOUNT_AMOUNT']					= str_replace(",", "", $postedData["TOTALDISCOUNT_HIDDEN"]);
				$stockorderData['NET_AMOUNT']						= str_replace(",", "", $postedData["NETTOTAL_HIDDEN"]);
				$stockorderData['PAYMENT_AMOUNT']					= str_replace(",", "", $postedData["NET_PAYMENT"]);
				$stockorderData['REMAINING_AMOUNT']					= str_replace(",", "", $postedData["DUE"]);
				$stockorder->exchangeArray($stockorderData);
				//echo "<pre>"; print_r($stockorder); die();
				$stockOrderID = $this->getStockOrderTable()->updateStockOrder($stockorder);
				if($stockOrderID > 0){
					$stockData['STOCK_ORDER_ID'] = $postedData["STOCK_ORDER_ID"];
					$stockentry->exchangeArray($stockData);
					//echo "<pre>"; print_r($stockentry); die();
					if($msg = $this->getStockEntryTable()->updateStockEntry($stockentry)) {
						//echo 'hi there';die();
						$success = true;
						$msg = "Successfull";
					} else {
						//echo 'cugpa';die();
						$success = false;
						$msg = "System Error";
						throw new \Exception("Error: Cannot update stock information into system!");
					}
				} else {
					$success = false;
					throw new \Exception("Error: Cannot update stock order into system!");
				}
				if($success) {
					//$this->getInvestorFundTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:75%;'>
															<tr class='valid_msg'>
																<td width='100%' style='text-align:center;'>Update Successfully!</td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('stockinformationedit');
				} else {
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:75%;'>
															<tr class='error_msg'>
																<td width='100%' style='text-align:center;'>Couldn't update properly!</td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('stockinformationedit');
				}
			}		
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		public function getSuggestInstNameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$marketId = $_REQUEST['marketId'];
			$instrumentGroupId = $_REQUEST['instrumentGroupId'];
			$cond = '';
			if (!empty($marketId)) {
				$cond .= " AND LS_MARKET_WISE_INSTRUMENT.MARKET_DETAILS_ID = '".$marketId."'";
			}
			if (!empty($instrumentGroupId)) {
				$cond .= " AND INSTD.GROUP_ID ='".$instrumentGroupId."'";
			}
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getInstrumentDetailsTable()->fetchInstrumentName($input,$cond);
			$str .= "<select multiple='multiple' align='center' style='width:300px;height:200px; font-family:Tahoma, Geneva, sans-serif;' >";
			foreach ($IPAData as $selectOption) {
				$instrumentID = $selectOption['INST_ID'];
				$instrumentName = $selectOption['INST_NAME'];
				$groupName = $selectOption['GROUP_NAME'];
				$symbol = $selectOption['SYMBOL'];
				$marketName = $selectOption['MARKET_SHORT_NAME'];
				$str .= "<option value='' style='text-align:left;cursor:pointer;' onClick=\"fill_id_code('".ucwords($symbol)."','".$instrumentID."');\"  >
					<b>".ucwords($symbol).'- '.$marketName."</b>
				 </option>";
			}
			$str .= "</select>";
			echo json_encode($str);
			//echo $str;
			exit;
		}
		public function getStockInformationListAction() {
			$this->session = new SessionContainer('post_supply');
			$portfolioName = '';
			$businessDate = $this->session->businessdate;	
			$supplierId 			= $_REQUEST['supplierId'];
			$date 	= date('Y-m-d',strtotime($_REQUEST['date']));
			$condRateUpdate	= $_REQUEST['cond'];
			$status = 'b';
			if($status == 'b'){
				$statusShow = 'Buy';
			}else if ($status == 's'){
				$statusShow = 'Sale';
			} else if ($status == 'all'){
				$statusShow = 'All';
			} else {
				$statusShow = 'Buy';
			}
			$cond = '';
			if ((!empty($date))) {
				$cond .= " WHERE SO.BUSINESS_DATE = '".$date."' ";
			} else {
				$cond .= '';
			}
			
			$cond1 = '';
			if ($condRateUpdate != 'all') {
				$cond1 .= " AND SD.RATE = '0.00'";
			}
			if ((!empty($status)) && ($status != 'all')) {
				$cond1 .= " AND SD.STATUS = '".$status."'";
			}
			if ((!empty($supplierId))) {
				$cond1 .= " AND SD.SUPPLIER_INFO_ID ='".$supplierId."'";
			}
			//echo 'hi ther';die();
			$table = '';
			$table .= "<table border='0' cellpadding='5' cellspacing='5' width='100%' style='font-size:85%;' >";
			$table .= "<tr class=''>
								<td valign='top' colspan='1'> 
										<table border='0' cellpadding='5' cellspacing='5' width='100%'>
											<tr style='border:1px dotted #888;font-size:120%;'>
												<td valign='top' width='20%' align='left'>
													&nbsp;
												</td>
												<td valign='top' colspan='3' align='center'>
													Stock Status : ".$statusShow."
												</td>
												<td valign='top' width='20%' align='right'>
													&nbsp;
												</td>
											</tr>
										</table>
								 </td>
						</tr>
						</table>";
			$instrumentCategoryName = '';
			$instrumentCategoryId = '';
			//$cond1 = '';
			$serialNo = 0;
			$stockOrderDetails  = $this->getStockOrderTable()->getStockOrder($cond);
			$foundFlag = 0;
			$totalFound = 0;
			//echo 'hi therdddddd';die();
			foreach($stockOrderDetails as $stockOrderData) {
				//echo 'hi ther';die();
				$stockOrderId 	= $stockOrderData["STOCK_ORDER_ID"];
				$orderNo  	 	= $stockOrderData["ORDER_NO"];				
				$totalAmount 	= $stockOrderData["TOTAL_AMOUNT"];
				$discountAmount	= $stockOrderData["DISCOUNT_AMOUNT"];
				$netAmount 	 	= $stockOrderData["NET_AMOUNT"];
				$paymentAmount 	= $stockOrderData["PAYMENT_AMOUNT"];
				$remainingAmount = $stockOrderData["REMAINING_AMOUNT"];
				
				if (!empty($stockOrderId)) {
					$cond1 .= " AND SD.STOCK_ORDER_ID = '".$stockOrderId."'";	
				}
				$stockDataDetails = array();
				$stockDataDetails  = $this->getStockEntryTable()->getStockDetails($cond1);	
				
				if(sizeof($stockDataDetails) > 0) {
					$foundFlag = 1;
					$table .= "<fieldset style='width:97%;border:0px;border-top:1px solid #2b2d93;'>
								<legend style='border:0px;'>".$orderNo."</legend>";
									$table .= "
											  <table border='0' cellpadding='5' cellspacing='10' width='100%' style='font-size:75%;border:1px dotted #888;'>
												<tr valign='top' style='font-weight:bold; text-align:center; background:#E8E1E1;'>
													<td width='3%' align='center' style='padding-right:10px;'>SL#</td>
													<td width='22%' align='center' style='padding-right:10px;'>SUPPLIER</td>
													<td width='25%' align='center' style='padding-right:10px;'>MODEL</td>
													<td width='5%' align='right' style='padding-right:10px;'>QTY</td>
													<td width='5%' align='right' style='padding-right:10px;'>RATE</td>
													<td width='15%' align='right' style='padding-right:10px;'>TOTAL</td>
													<td width='5%' align='right' style='padding-right:10px;'>DISCOUNT</td>
													<td width='5%' align='right' style='padding-right:10px;'>AVG PRICE</td>
													<td width='15%' align='right' style='padding-right:10px;'>NET TOTAL</td>											
												</tr>
											  ";
												$serialNo = 1;
												$totalRowsReturn = 0;
												if(sizeof($stockDataDetails) > 0) {
													for($i = 0;$i<sizeof($stockDataDetails['MODEL_ID']);$i++){
														$j = $i;
														if($i%2==0) {
															$class="evenRow";
														}
														else {
															$class="oddRow";
														}
														$table .= "
															<tr valign='top' class='{$class}'>
																<td valign='top'  align='center'>
																	".$serialNo.'.&nbsp;'."
																	<input type='hidden' name='STOCK_DETAILS_ID{$i}' id='STOCK_DETAILS_ID{$i}' value='".$stockDataDetails['STOCK_DETAILS_ID'][$i]."' />
																	<input type='hidden' name='CATEGORY_ID{$i}' id='CATEGORY_ID{$i}' value='".$instrumentCategoryName."' />
																</td>
																<td  align='left'>																	 
																	 <select name='SUPPLIER_INFO_ID{$i}' id='SUPPLIER_INFO_ID_".$j."' class='FormSelectTypeInput' style='width:150px;font-family:Tahoma, Geneva, sans-serif;font-size:120%;'>
															<option value=''>Select</option>
															<option value='".$stockDataDetails['SUPPID'][$i]."' selected='selected'>".$stockDataDetails['NAME'][$i]."</option>
																	 </select>
																</td>
																<td  align='left' class=''>
																	<input name='CATEGORY{$i}' type='text'  id='CATEGORY{$i}'  style='width:150px;font-family:Tahoma, Geneva, sans-serif;' value='".$stockDataDetails['MODEL_NAME'][$i]."' autocomplete='off' onkeyup='coa_code_suggest(this.value,{$i}); removeNumber(this);' readonly='readonly'/>
																		<input name='CATEGORY_ID{$i}' type='hidden'  id='CATEGORY_ID{$i}' value='".$stockDataDetails['MODEL_ID'][$i]."' />
																</td>
																<td  align='left'>
																	 <input type='text' name='QUANTITY{$i}' id='QUANTITY{$i}' onkeyup='removeChar(this);' class='FormNumericTypeInput' style='width:80px;' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"QUANTITY{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\";calculateTotal(this.id,this.value,{$i}); ' value='".number_format($stockDataDetails['QUANTITY'][$i],2)."' maxlength='10' autocomplete='off'/>
																</td>
																<td  align='left' class='FormSelectTypeInput'>
																	 <input type='text' name='RATE{$i}' id='RATE{$i}' onkeyup='removeChar(this);calculateTotal(this.id,this.value,{$i});' class='FormNumericTypeInput' style='width:80px;' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"RATE{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\";calculateTotal(this.id,this.value,{$i}); ' value='".number_format($stockDataDetails['RATE'][$i],2)."' maxlength='10' autocomplete='off'/>
																</td>
																<td  align='left' class='FormSelectTypeInput'>
																	 <input type='text' name='TOTAL_AMOUNT{$i}' id='TOTAL_AMOUNT{$i}' onkeyup='removeChar(this);' class='FormNumericTypeInput' style='width:100px;' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"TOTAL_AMOUNT{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\"; ' value='".number_format($stockDataDetails['TOTAL_AMOUNT'][$i],2)."' maxlength='15' readonly='readonly' autocomplete='off'/>
																</td>
																<td  align='left' class='FormSelectTypeInput'>
																	 <input type='text' name='DISCOUNT{$i}' id='DISCOUNT{$i}' onkeyup='removeChar(this);' class='FormNumericTypeInput' style='width:80px;' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"DISCOUNT{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\"; calculateTotal(this.id,this.value,{$i}); ' value='".number_format($stockDataDetails['DISCOUNT'][$i],2)."' maxlength='10' autocomplete='off'/>
																</td>
																<td  align='left' class='FormSelectTypeInput'>
																	 <input type='text' name='AVG_RATE{$i}' id='AVG_RATE{$i}' onkeyup='removeChar(this);' class='FormNumericTypeInput' style='width:80px;' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"AVG_RATE{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\"; ' value='".number_format($stockDataDetails['AVG_RATE'][$i],2)."' maxlength='10' readonly='readonly' autocomplete='off'/>
																</td>
																<td  align='right' class=''><input type='text' name='NET_AMOUNT{$i}' id='NET_AMOUNT{$i}' onkeyup='removeChar(this);' class='FormNumericTypeInput' style='width:100px;' onfocus='if(this.value==\"0.00\") this.value=\"\";' onblur='numberFormat(\"amount{$i}\",this.value,2,\",\",\".\"); if((this.value==\"\") || (this.value==0)) this.value=\"0.00\"; AddTabRow(); totalDRCRShow();' value='".number_format($stockDataDetails['NET_AMOUNT'][$i],2)."' maxlength='15' readonly='readonly' autocomplete='off'/></td>
															</tr>";
														$serialNo++;
													}
												}
									$table .= '
											<tr valign="top" style="font-weight:bold;">
												<td colspan="3"  align="center" class="">&nbsp;</td>
												<td align="right" width="5%" style="padding-right:5px;"><span id="QTYTOTAL">0.00</span></td>
												<td align="right" width="5%" style="padding-right:10px;">-</td>
												<td align="right" width="15%" style="padding-right:5px;">
													<input type="hidden" name="TOTALAMOUNT_HIDDEN" id="TOTALAMOUNT_HIDDEN" value="'.number_format($totalAmount,2).'"/>
													<span id="TOTALAMOUNT">'.number_format($totalAmount,2).'</span>
												</td>
												<td align="right" width="5%" style="padding-right:5px;">
													<input type="hidden" name="TOTALDISCOUNT_HIDDEN" id="TOTALDISCOUNT_HIDDEN" value="'.number_format($discountAmount,2).'"/>
													<span id="TOTALDISCOUNT">'.number_format($discountAmount,2).'</span>
												</td>
												<td align="right" width="5%" style="padding-right:10px;">-</td>
												<td align="right" width="15%" style="padding-right:10px;">
													<input type="hidden" name="NETTOTAL_HIDDEN" id="NETTOTAL_HIDDEN" value="'.number_format($netAmount,2).'"/>
													<span id="NETTOTAL">'.number_format($netAmount,2).'</span>
												</td>
											</tr>
											<tr valign="top" style="font-weight:bold;">
												<td colspan="8"  align="right" class="">Net Payment</td>												
												<td align="right" style="padding-right:10px;"><input type="text" name="NET_PAYMENT" id="NET_PAYMENT" onkeyup="removeChar(this);" class="FormNumericTypeInput" style="width:100px;" onfocus="if(this.value==\'0.00\') this.value=\'\';" onblur="numberFormat(\'NET_PAYMENT\',this.value,2,\',\',\'.\');if((this.value==\'\') || (this.value==0)) this.value=0.00;calculateDue();" value="'.number_format($paymentAmount,2).'" maxlength="20" autocomplete="off"/></td>
											</tr>
											<tr valign="top" style="font-weight:bold;">
												<td colspan="8"  align="right" class="">Due</td>
												<td align="right" style="padding-right:10px;"><input type="text" name="DUE" id="DUE" onkeyup="removeChar(this);" class="FormNumericTypeInput" style="width:100px;" onfocus="if(this.value=="0.00") this.value="";" onblur="numberFormat("DUE",this.value,2,",","."); if((this.value=="") || (this.value==0)) this.value=0.00;" value="'.number_format($remainingAmount,2).'" maxlength="20" autocomplete="off" readonly="readonly"/></td>
											</tr>
											<tr valign="top">
												 <td align="center" colspan="9"><input type="hidden" name="STOCK_ORDER_ID" id="STOCK_ORDER_ID" value="'.$stockOrderId.'" /></td>
											</tr>
											';
									$table .= "</table>";
					$table .= "</fieldset>";
				}
				$totalFound += $serialNo - 1;
			}
			//echo $foundFlag;die();
			if($foundFlag == 0){
				$table .= "
							  <table border='0' cellpadding='5' cellspacing='10' width='100%' style='font-size:85%;border:1px dotted #888;'>
								<tr valign='top' style='font-weight:bold; text-align:center; background:#E8E1E1;'>
									<td width='3%' align='center' style='padding-right:10px;'>SL#</td>
									<td width='22%' align='center' style='padding-right:10px;'>SUPPLIER</td>
									<td width='25%' align='center' style='padding-right:10px;'>MODEL</td>
									<td width='5%' align='right' style='padding-right:10px;'>QTY</td>
									<td width='5%' align='right' style='padding-right:10px;'>RATE</td>
									<td width='15%' align='right' style='padding-right:10px;'>TOTAL</td>
									<td width='5%' align='right' style='padding-right:10px;'>DISCOUNT</td>
									<td width='5%' align='right' style='padding-right:10px;'>AVG PRICE</td>
									<td width='15%' align='right' style='padding-right:10px;'>NET TOTAL</td>											
								</tr>
								<tr valign='top' height='35px;' style='font-weight:bold;' class='errorMsg' >
									<td  align='center' colspan='9'  style='border-bottom:1px dotted #888;'>Not Found</td>
								</tr>
							  ";
			}
			$table .= "<table border='0' cellpadding='5' cellspacing='5' width='100%' style='font-size:85%;' >
						  <tr valign='top'>
							<td colspan='9' align='center'>
								<input type='hidden' name='NumberOfRows' id='NumberOfRows' value='{$i}'/>
								<input type='hidden' name='num_of_total_scrip' id='num_of_total_scrip' value='".$totalFound."'/>
								<input type='submit' name='submit' id='submit' value='Submit' />&nbsp;<input type='reset' name='reset' id='reset' value='Reset' onclick='setReset();' />
							</td>
						  </tr>
			 			";
			$table .= "</table>";
			//echo $table;die();
			echo json_encode($table);exit;
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
		public function getSupplierInformationTable() {
			
			if(!$this->supplierInformationTable) {
				$sm = $this->getServiceLocator();
				$this->supplierInformationTable = $sm->get('LocalSetting\Model\SupplierInformationTable');
			}
			return $this->supplierInformationTable;
		}
		
	}
?>