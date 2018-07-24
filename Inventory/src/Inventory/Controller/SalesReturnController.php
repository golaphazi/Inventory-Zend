<?php
	namespace Inventory\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Inventory\Form\SalesReturnForm;
	use Accounts\Model\TrialBalanceTable;
	
	use Inventory\Model\RETStockOrder;
	use Inventory\Model\RETStockDetails;
	
	use Accounts\Model\Master;
	use Accounts\Model\Child;
	use Accounts\Model\Voucher;
	
	use Zend\Session\Container as SessionContainer;
	
	class SalesReturnController extends AbstractActionController {		
		protected $dbAdapter;
		protected $portfolioTrialBalanceTable;
		protected $branchTable;
		protected $coaTable;
		protected $trialBalanceTable;
		protected $retstockOrderTable;
		protected $retstockDetailsTable;
		protected $supplierInformationTable;
		protected $stockEntryTable;
		protected $retailerInformationTable;
		protected $srStockDetailsTable;
		protected $voucherTable;
		protected $masterTable;
		protected $childTable;
				
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Inventory',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new SalesReturnForm('salesreturn', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$retstockdetails = new RETStockDetails();
				$form->setInputFilter($retstockdetails->getInputFilter());
				$form->setData($request->getPost());
				//echo '<pre>';print_r($request->getPost());die();
				//if($form->isValid()) {
					$msg = '';
					$retstockData	= array();
					$postedData = $request->getPost();
					$this->session 		= new SessionContainer('post_supply');
					$businessDate 		= $this->session->businessdate;
					$BRANCH_ID			= 1;//$this->session->branchid;
					
					$stockDetails		= array();
					$stockWiseVoucher	= array();
					$oddClass			= "#EEE9E9";
					$evenClass			= "#F7F4F4";
					$PARTICULARS_ENCASHMENT = '';
					$CATEGORY_NAME = '';
					$RETAILER_ID = $postedData["RETAILER_ID1"];
					$RET_SHORT_NAME = $postedData["RET_SHORT_NAME1"];
					$VOUCHER_RETAILER_ID[]	= $RETAILER_ID;
					if(!isset($stockWiseVoucher[$RETAILER_ID])){
						$stockWiseVoucher[$RETAILER_ID]	= array();
					}
					for($i = 1; $i < $postedData->NumberOfRows; $i++) {
						$cOACodeData = $postedData["CATEGORY_ID{$i}"];
						if(!empty($cOACodeData)) {
							$j	= $i-1;
							$retstockData['CATEGORY_ID'][$j]				= $postedData["CATEGORY_ID{$i}"];
							$retstockData['CATEGORY'][$j]					= $postedData["CATEGORY{$i}"];
							$retstockData['CAT_PRICE_ID'][$j]				= $postedData["CAT_PRICE_ID{$i}"];
							$retstockData['RET_QUANTITY'][$j]				= str_replace(",", "", $postedData["RQUANTITY{$i}"]);
							$retstockData['RET_BUY_PRICE'][$j]				= str_replace(",", "", $postedData["BUY_PRICE{$i}"]);
							$retstockData['RET_TOTAL_AMOUNT'][$j]			= str_replace(",", "", $postedData["TOTAL_AMOUNT{$i}"]);
							$retstockData['RET_DISCOUNT'][$j]				= str_replace(",", "", $postedData["DISCOUNT{$i}"]);
							$retstockData['RET_DISCOUNT_RECEIVE'][$j]		= str_replace(",", "", $postedData["DISCOUNT_RECEIVE{$i}"]);
							$retstockData['RET_AVG_RATE'][$j]				= str_replace(",", "", $postedData["AVG_RATE{$i}"]);
							$retstockData['REMARKS'][$j]					= $postedData["REMARKS{$i}"];
							$retstockData['RET_NET_AMOUNT'][$j]				= str_replace(",", "", $postedData["NET_AMOUNT{$i}"]);
							
							$stockDetails = array(
													"RECEIVABLE_COA_CODE"	=> $postedData["RECEIVABLE_COA1"],
													"RECEIVABLE_COA_NAME"	=> trim($postedData["RECEIVABLE_COA_NAME1"]),
													"ENCASHMENT_COA_CODE"	=> $postedData["COA_CODE{$i}"],
													"ENCASHMENT_COA_NAME"	=> $postedData["COA_NAME{$i}"],
													"DISCOUNT_COA_CODE"		=> $postedData["DISCOUNT_COACODE"],
													"DISCOUNT_COA_NAME"		=> $postedData["DISCOUNT_COANAME"],
													"GAINLOSS_COA_CODE"		=> $postedData["GAINLOSS_COACODE"],
													"GAINLOSS_COA_NAME"		=> $postedData["GAINLOSS_COANAME"],
													"AMOUNT"				=> str_replace(",", "", $postedData["NET_AMOUNT{$i}"]),
													"DISCOUNT"				=> str_replace(",", "", $postedData["DISCOUNT{$i}"]),
													"CATEGORY"				=> $postedData["CATEGORY{$i}"],
													"RET_SHORT_NAME"		=> $RET_SHORT_NAME,
													"NET_PAYMENT"			=> str_replace(",", "", $postedData["NET_PAYMENT"]),
												);
							array_push($stockWiseVoucher[$RETAILER_ID],$stockDetails);
						}
					}
					
					$retstockData['BRANCH_ID'] 								= $BRANCH_ID;
					$retstockData['tranDateTo']								= $postedData["tranDateTo"];
					$retstockData['insertJournal']							= $postedData["insertJournal"];
					$retstockData['NumberOfRows']							= $postedData["NumberOfRows"];
					$retstockData['DUE']									= $postedData["DUE"];
					$retstockData['NET_PAYMENT']							= $postedData["NET_PAYMENT"];
					$retstockorder = new RETStockOrder();
					$retstockorderData = array();
					$retstockorderData['ORDER_NO'] 							= $postedData["ORDER_NO"];
					$retstockorderData['RETAILER_ID'] 						= $postedData["RETAILER_ID"];
					$retstockorderData['EMPLOYEE_ID'] 						= $postedData["EMPLOYEE_ID"];
					$retstockorderData['RET_TOTAL_AMOUNT']					= str_replace(",", "", $postedData["TOTALAMOUNT_HIDDEN"]);
					$retstockorderData['RET_DISCOUNT_AMOUNT']				= str_replace(",", "", $postedData["TOTALDISCOUNT_HIDDEN"]);
					$retstockorderData['RET_TOT_DIS_RECEIVE']				= str_replace(",", "", $postedData["TOTALDISCOUNTRECEIVE_HIDDEN"]);
					$retstockorder->exchangeArray($retstockorderData);
					$retStockOrderID = $this->getRETStockOrderTable()->saveRETStockReturn($retstockorder);
					if($retStockOrderID > 0){
						$retstockData['RET_STOCK_DIST_ID']	= $retStockOrderID;
						$retstockData['ORDER_NO']			= $postedData["ORDER_NO"];
						$retstockdetails->exchangeArray($retstockData);
						if($msg = $this->getRETStockDetailsTable()->saveRETStockReturnDetails($retstockdetails)) {
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
					//echo '<pre>';print_r($stockWiseVoucher);die();
					
					// Voucher Generate Start by Akhand
					$allChartOfAccountDisplay			= "";
					$chartOfAccountDisplay				= "";
					$chartOfAccountDisplayEncashment	= "";
					$chartOfAccount						= '';
					$oddClass							= "#EEE9E9";
					$evenClass							= "#F7F4F4";
					$chartOfAccountReceive				= "";
					
					$RETAILER_DATA	= array_unique($VOUCHER_RETAILER_ID);
					$RETAILER_DATAS	= array();
					foreach($RETAILER_DATA as $RETAILER_DATA_VALUE) {
						$RETAILER_DATAS[]	= $RETAILER_DATA_VALUE;
					}
					$voucherTypeIndex 		= 0;
					for($o=0;$o<sizeof($RETAILER_DATAS);$o++) {
						$RID 					= $RETAILER_DATAS[$o];
						$PARTICULARS_ENCASHMENT	= "";
						$ENCASHMENT_COA_CODE	= "";
						$ENCASHMENT_COA_NAME	= "";
						$RECEIVABLE_COA_CODE		= "";
						$RECEIVABLE_COA_NAME		= "";
						$TOTAL_RECEIVABLE_AMOUNT 	= 0;
						$TOTAL_DISCOUNT_AMOUNT	= 0;
						$TOTAL_GAINLOSS_AMOUNT	= 0;
						
						for($c=0;$c<sizeof($stockWiseVoucher[$RID]);$c++) {
							$BRANCH_ID				= $BRANCH_ID;
							$RECEIVABLE_COA_CODE	= $stockWiseVoucher[$RID][$c]['RECEIVABLE_COA_CODE'];
							$RECEIVABLE_COA_NAME	= $stockWiseVoucher[$RID][$c]['RECEIVABLE_COA_NAME'];
							$ENCASHMENT_COA_CODE	= $stockWiseVoucher[$RID][$c]['ENCASHMENT_COA_CODE'];
							$ENCASHMENT_COA_NAME	= $stockWiseVoucher[$RID][$c]['ENCASHMENT_COA_NAME'];
							$DISCOUNT_COA_CODE		= $stockWiseVoucher[$RID][$c]['DISCOUNT_COA_CODE'];
							$DISCOUNT_COA_NAME		= $stockWiseVoucher[$RID][$c]['DISCOUNT_COA_NAME'];
							$GAINLOSS_COA_CODE		= $stockWiseVoucher[$RID][$c]['GAINLOSS_COA_CODE'];
							$GAINLOSS_COA_NAME		= $stockWiseVoucher[$RID][$c]['GAINLOSS_COA_NAME'];
							$AMOUNT					= $stockWiseVoucher[$RID][$c]['AMOUNT'];
							$DISCOUNT				= $stockWiseVoucher[$RID][$c]['DISCOUNT'];
							$CATEGORY				= $stockWiseVoucher[$RID][$c]['CATEGORY'];
							$NET_PAYMENT			= $stockWiseVoucher[$RID][$c]['NET_PAYMENT'];
							
							$RET_SHORT_NAME			= $RET_SHORT_NAME;
							$PARTICULARS_ENCASHMENT	.= " ".$CATEGORY.", ";
							if($c == 0) {
								$chartOfAccountDisplay	= "
															<table cellspacing='0' class='vtbl' >
																<tr class='head'>
																	<td colspan='4' align='center'>
																		Journal Voucher for RETAILER : {$RET_SHORT_NAME}
																	</td>
																</tr>
																<tr class='head'>
																	<td width='55%' align='left'>Account Head</td>
																	<td width='15%' align='center'>Account Code</td>
																	<td width='15%' align='center'>Dr/Cr</td>
																	<td width='15%' align='right'>Amount</td>
																</tr>
															";
							}
							$TOTAL_RECEIVABLE_AMOUNT 					+= ($AMOUNT-$DISCOUNT);
							$TOTAL_DISCOUNT_AMOUNT 						+= $DISCOUNT;
							$ENCASHMENT_DEBIT_CHECKED					= "";
							$ENCASHMENT_CREDIT_CHECKED					= "checked='checked'";
							$ENCASHMENT_DEBIT_DISABLED					= "disabled='disabled'";
							$ENCASHMENT_CREDIT_DISABLED					= "";
							$ENCASHMENT_AMOUNT_ALIGN					= "right";
							$ENCASHMENT_VOUCHER_TYPE					= "C";
							$VOUCHER_COA_CODE[$RID]['COA_CODE'][]		= $ENCASHMENT_COA_CODE;
							$VOUCHER_COA_CODE[$RID]['VOUCHER_TYPE'][]	= $ENCASHMENT_VOUCHER_TYPE;
							$VOUCHER_COA_CODE[$RID]['VOUCHER_AMOUNT'][]	= abs($AMOUNT);
							$chartOfAccountDisplayEncashment	.= "
																	<tr class='{$oddClass}'>
																		<td align='left'>
																			{$ENCASHMENT_COA_NAME}
																		</td>
																		<td align='center'>																												
																			<input type='hidden' name='COA_CODE[]' value='{$ENCASHMENT_COA_CODE}'/>
																			{$ENCASHMENT_COA_CODE}
																		</td>
																		<td align='center'>
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$ENCASHMENT_DEBIT_CHECKED} {$ENCASHMENT_DEBIT_DISABLED}/>Dr 
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$ENCASHMENT_CREDIT_CHECKED} {$ENCASHMENT_CREDIT_DISABLED}/>Cr
																		</td>
																		<td align='{$ENCASHMENT_AMOUNT_ALIGN}'>
																			<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																		</td>
																	</tr>
																	";
							$voucherTypeIndex++;
						}
						
						$RECEIVABLE_DEBIT_CHECKED						= "checked='checked'";
						$RECEIVABLE_CREDIT_CHECKED						= "";
						$RECEIVABLE_DEBIT_DISABLED						= "";
						$RECEIVABLE_CREDIT_DISABLED						= "disabled='disabled'";
						$RECEIVABLE_AMOUNT_ALIGN						= "left";
						$RECEIVABLE_VOUCHER_TYPE						= "D";
						$VOUCHER_COA_CODE[$RID]['COA_CODE'][]		= $RECEIVABLE_COA_CODE;
						$VOUCHER_COA_CODE[$RID]['VOUCHER_TYPE'][]	= $RECEIVABLE_VOUCHER_TYPE;
						$VOUCHER_COA_CODE[$RID]['VOUCHER_AMOUNT'][]	= abs($TOTAL_RECEIVABLE_AMOUNT);
						
						$chartOfAccountDisplayEncashment	.= "
																<tr class='{$oddClass}'>
																	<td align='left'>
																		{$RECEIVABLE_COA_NAME}
																	</td>
																	<td align='center'>																												
																		<input type='hidden' name='COA_CODE[]' value='{$RECEIVABLE_COA_CODE}'/>
																		{$RECEIVABLE_COA_CODE}
																	</td>
																	<td align='center'>
																	  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$RECEIVABLE_DEBIT_CHECKED} {$RECEIVABLE_DEBIT_DISABLED}/>Dr 
																	  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$RECEIVABLE_CREDIT_CHECKED} {$RECEIVABLE_CREDIT_DISABLED} />Cr
																	</td>
																	<td align='{$RECEIVABLE_AMOUNT_ALIGN}'>
																		<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($TOTAL_RECEIVABLE_AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																	</td>
																</tr>
																";
						//$voucherTypeIndex++;	
						
						$DISCOUNT_DEBIT_CHECKED						= "checked='checked'";
						$DISCOUNT_CREDIT_CHECKED					= "";
						$DISCOUNT_DEBIT_DISABLED					= "";
						$DISCOUNT_CREDIT_DISABLED					= "disabled='disabled'";
						$DISCOUNT_AMOUNT_ALIGN						= "left";
						$DISCOUNT_VOUCHER_TYPE						= "D";	
						$VOUCHER_COA_CODE[$RID]['COA_CODE'][]		= $DISCOUNT_COA_CODE;
						$VOUCHER_COA_CODE[$RID]['VOUCHER_TYPE'][]	= $DISCOUNT_VOUCHER_TYPE;
						$VOUCHER_COA_CODE[$RID]['VOUCHER_AMOUNT'][]	= abs($TOTAL_DISCOUNT_AMOUNT);
						
						$chartOfAccountDisplayEncashment	.= "
																<tr class='{$oddClass}'>
																	<td align='left'>
																		{$DISCOUNT_COA_NAME}
																	</td>
																	<td align='center'>																												
																		<input type='hidden' name='COA_CODE[]' value='{$DISCOUNT_COA_CODE}'/>
																		{$DISCOUNT_COA_CODE}
																	</td>
																	<td align='center'>
																	  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$DISCOUNT_DEBIT_CHECKED} {$DISCOUNT_DEBIT_DISABLED}/>Dr 
																	  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$DISCOUNT_CREDIT_CHECKED} {$DISCOUNT_CREDIT_DISABLED}/>Cr
																	</td>
																	<td align='{$DISCOUNT_AMOUNT_ALIGN}'>
																		<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($TOTAL_DISCOUNT_AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																	</td>
																</tr>
																";
						$voucherTypeIndex++;
						
						$chartOfAccountDisplay	.= "{$chartOfAccountDisplayEncashment}";
						$PARTICULARS_ENCASHMENT	 = "Stock Return ".$PARTICULARS_ENCASHMENT." on ".$businessDate." from ".$RET_SHORT_NAME."";
						$chartOfAccountDisplay	.= "
														<tr class='oddClass'>
															<td align='left'>Particulars</td>
															<td align='left' colspan='3'>
																<textarea name='PARTICULARS' cols='100' rows='2' style='text-align:left;'>".trim($PARTICULARS_ENCASHMENT)."</textarea>
															</td>
														</tr>
														<tr class='evenClass'>
															<td align='left' colspan='4'>&nbsp;</td>
														</tr>
													</table>
													";
						/// Receive Vocuher Start ///
						// if net payment greater than zero //
						if($stockDetails['NET_PAYMENT'] > 0){
							$chartOfAccountReceive	= "
													<table cellspacing='0' class='vtbl' >
														<tr class='head'>
															<td colspan='4' align='center'>
																Receive Voucher for RETAILER : {$RET_SHORT_NAME}
															</td>
														</tr>
														<tr class='head'>
															<td width='55%' align='left'>Account Head</td>
															<td width='15%' align='center'>Account Code</td>
															<td width='15%' align='center'>Dr/Cr</td>
															<td width='15%' align='right'>Amount</td>
														</tr>
													";
							$RECEIVE_COA_NAME = $RECEIVABLE_COA_NAME;
							$RECEIVE_COA_CODE = $RECEIVABLE_COA_CODE;
							$RECEIVE_DEBIT_CHECKED						= "";
							$RECEIVE_CREDIT_CHECKED						= "checked='checked'";
							$RECEIVE_DEBIT_DISABLED						= "disabled='disabled'";
							$RECEIVE_CREDIT_DISABLED					= "";
							$RECEIVE_AMOUNT_ALIGN						= "right";
							$RECEIVE_VOUCHER_TYPE						= "C";
							$RECEIVE_VOUCHER_COA_CODE[$RID]['COA_CODE'][]		= $RECEIVE_COA_CODE;
							$RECEIVE_VOUCHER_COA_CODE[$RID]['VOUCHER_TYPE'][]	= $RECEIVE_VOUCHER_TYPE;
							$RECEIVE_VOUCHER_COA_CODE[$RID]['VOUCHER_AMOUNT'][]	= abs($NET_PAYMENT);
							
							$BANK_COA_NAME = "Cash in Hand";
							$BANK_COA_CODE = "303001001";
							$BANK_DEBIT_CHECKED						= "checked='checked'";
							$BANK_CREDIT_CHECKED					= "";
							$BANK_DEBIT_DISABLED					= "";
							$BANK_CREDIT_DISABLED					= "disabled='disabled'";
							$BANK_AMOUNT_ALIGN						= "left";
							$BANK_VOUCHER_TYPE						= "D";
							$RECEIVE_VOUCHER_COA_CODE[$RID]['COA_CODE'][]		= $BANK_COA_CODE;
							$RECEIVE_VOUCHER_COA_CODE[$RID]['VOUCHER_TYPE'][]	= $BANK_VOUCHER_TYPE;
							$RECEIVE_VOUCHER_COA_CODE[$RID]['VOUCHER_AMOUNT'][]	= abs($NET_PAYMENT);
							
							
							$chartOfAccountReceive	.= "
																	<tr class='{$oddClass}'>
																		<td align='left'>
																			{$RECEIVE_COA_NAME}
																		</td>
																		<td align='center'>																												
																			<input type='hidden' name='RECEIVE_COA_CODE[]' value='{$RECEIVE_COA_CODE}'/>
																			{$RECEIVE_COA_CODE}
																		</td>
																		<td align='center'>
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$RECEIVE_DEBIT_CHECKED} {$RECEIVE_DEBIT_DISABLED}/>Dr 
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$RECEIVE_CREDIT_CHECKED} {$RECEIVE_CREDIT_DISABLED}/>Cr
																		</td>
																		<td align='{$RECEIVE_AMOUNT_ALIGN}'>
																			<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($NET_PAYMENT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																		</td>
																	</tr>
																	";
							$voucherTypeIndex++;	
							
							$chartOfAccountReceive	.= "
																	<tr class='{$oddClass}'>
																		<td align='left'>
																			{$BANK_COA_NAME}
																		</td>
																		<td align='center'>																												
																			<input type='hidden' name='PAYMENT_COA_CODE[]' value='{$BANK_COA_CODE}'/>
																			{$BANK_COA_CODE}
																		</td>
																		<td align='center'>
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$BANK_DEBIT_CHECKED} {$BANK_DEBIT_DISABLED}/>Dr 
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$BANK_CREDIT_CHECKED} {$BANK_CREDIT_DISABLED}/>Cr
																		</td>
																		<td align='{$BANK_AMOUNT_ALIGN}'>
																			<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($NET_PAYMENT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																		</td>
																	</tr>
																";
							
							$RECEIVE_PARTICULARS = 	"Received from {$RET_SHORT_NAME} for the purpose of sales return on ".$businessDate."";
							$chartOfAccountReceive	.= "
															<tr class='oddClass'>
																<td align='left'>Particulars</td>
																<td align='left' colspan='3'>
																	<textarea name='PARTICULARS' cols='100' rows='2' style='text-align:left;'>".trim($RECEIVE_PARTICULARS)."</textarea>
																</td>
															</tr>
															<tr class='evenClass'>
																<td align='left' colspan='4'>&nbsp;</td>
															</tr>
														</table>
														";
						} // if net payment greater than zero condition ends here
						/// Receive Vocuher End ///
						$voucher 		= new Voucher();
						$voucherData	= array();
						//echo $chartOfAccountDisplay;die();
						//echo $chartOfAccountReceive;die();
						$allChartOfAccountDisplay			.= $chartOfAccountDisplay;
						$chartOfAccountDisplayEncashment 	= '';
						$chartOfAccountDisplay 				= '';
						$PARTICULARS 						= $PARTICULARS_ENCASHMENT;
						$v_voucher_no_in_out  		= '';
						$v_max_transaction_no 		= 0;
						$v_chq_effected_dt    		= '';
						$v_chq_dt             		= '';
						$v_voucher_type       		= '';
						$v_temp_voucher_type  		= '';
						$v_temp_voucher_no    		= '';
						
						
						$tm_drawn_on		= 'N/A';
						$tm_auto_tran 		= 'y';
						
						$transStatus   		= (int) 0;
						$flag				= 0;
						$tmTransectionDate	= date("d-m-Y", strtotime($businessDate));
						$tmNtr              = '';
						
						$tmCbjt 		    = 'J';
						$tmCbCOACode        = '';
						$effected_bank_tran	= '';
						$tmChequeNo 	    = '';
						$tmChqDate 	        = '';
						
						$tcAccCodes       	= array();
						$tcNtrs           	= array();
						$tcNarrations     	= array();
						$tcAmounts        	= array();
						$tcAccCode		  	= '';
						$tcAmount		 	= '';
						$tcNarration	  	= '';
						$tcBranchId			= $BRANCH_ID;
						$tcNarration 		= $PARTICULARS;
						
						$cbCode					= '';
						$tmInvoiceNo			= 'Auto';
						$tmMoneyReceiptNo 		= 'Auto';
						$backDateFlag			= '';
						
						//echo '<pre>'; print_r($VOUCHER_COA_CODE[$RID]["COA_CODE"]);die();
						$ALL_COA_CODE		= $VOUCHER_COA_CODE[$RID]["COA_CODE"];
						foreach($ALL_COA_CODE as $index=>$ALL_COA_CODE_VALUE) {
							$tcAccCodes[]		= $VOUCHER_COA_CODE[$RID]["COA_CODE"][$index];
							$tcNtrs[]       	= $VOUCHER_COA_CODE[$RID]["VOUCHER_TYPE"][$index];
							$tcNarrations[] 	= $PARTICULARS;
							$tcAmounts[]    	= str_replace(",", "", $VOUCHER_COA_CODE[$RID]["VOUCHER_AMOUNT"][$index]);
						}
						
						//echo "<pre>"; print_r($tcAccCodes); print_r($tcNtrs); print_r($tcAmounts); print_r($tcNarrations); die();
						
						// General Accounts Entry Start BY Akhand
						$v_tm_tran_dt_in        	= $tmTransectionDate;
						$v_tm_drown_on_in       	= $tm_drawn_on;
						$v_tm_auto_tran_in     	 	= $tm_auto_tran;
						$v_tm_ntr_in            	= $tmNtr;
						$v_tm_cbjt_in           	= $tmCbjt;
						$v_tm_cb_code_in        	= $tmCbCOACode;
						$v_tm_chq_no_in         	= $tmChequeNo;
						$v_tm_chq_dt_in         	= $tmChqDate;
						
						$v_tm_invoice_no_in			= $tmInvoiceNo;
						$v_tm_money_receipt_no_in	= $tmMoneyReceiptNo;
						$v_cb_code_cond				= $cbCode;
						$v_back_date_flag			= $backDateFlag;
						
						$v_business_date_in			= $tmTransectionDate;
						$v_tc_branch_id_in			= $tcBranchId;
						
						$v_tm_user_id_in			= '';
						$v_effected_bank_tran_in	= $effected_bank_tran;
						$v_trans_status_in_out		= $transStatus;
						$v_msg_out					= $msg;
			
						$v_tc_ac_code_in			= $tcAccCodes;
						$v_tc_ntr_in				= $tcNtrs;
						$v_tc_narration_in			= $tcNarrations;
						$v_tc_amount_in				= $tcAmounts;
						
						if($v_effected_bank_tran_in = 'y') {
							$v_chq_effected_dt 	= $v_tm_tran_dt_in;
						}
						
						if($v_tm_ntr_in == '') {
							$v_voucher_type 		= 'JV';
						} else {
							if($v_tm_ntr_in == 'D') {
								if ($v_cb_code_cond == 'bank') {
									$v_voucher_type = 'BP';
								} else {
									$v_voucher_type = 'CP';
								}
							} else {
								if ($v_cb_code_cond == 'bank') {
									$v_voucher_type = 'BR';
								} else {
									$v_voucher_type = 'CR';
								}
							}		
						}
						
						$voucher 		= new Voucher();
						$master 		= new Master();
						$child		 	= new Child();
						// Voucher Table Data Insert Start BY Akhand
						$voucherData		= array();					
						$voucherData		= array(
														'BRANCH_ID' 			=> $v_tc_branch_id_in,
														'V_YEAR' 				=> date("Y", strtotime($tmTransectionDate)),
														'JOURNAL_VOUCHER' 		=> $v_voucher_type,
													);
						$voucher->exchangeArray($voucherData);
						//echo "<pre>"; print_r($voucher);die();
						if($returnVoucher = $this->getVoucherTable()->saveVoucher($voucher)) {
							// Master Table Data Insert Start BY Akhand
							$masterData	= array();
							$masterData 	= array(
								'TRAN_NO'				=> '',
								'TRAN_DATE' 			=> $v_tm_tran_dt_in,				
								'VOUCHER_NO' 			=> $returnVoucher,
								'NTR' 					=> $v_tm_ntr_in,
								'CBJT' 					=> $v_tm_cbjt_in,
								'CB_CODE' 				=> $v_tm_cb_code_in,
								'CHEQUE_NO' 			=> $v_tm_chq_no_in,
								'CHEQUE_DATE' 			=> $v_tm_chq_dt_in,
								'EFFECTED_DATE' 		=> $v_chq_effected_dt,
								'RECONCILIATION_FLAG' 	=> $v_effected_bank_tran_in,
								'RECORD_DATE' 			=> '',
								'DRAWN_ON' 				=> $v_tm_drown_on_in,
								'AUTO_TRANSACTION_FLAG' => $v_tm_auto_tran_in,
								'INVOICE_NO' 			=> $v_tm_invoice_no_in,
								'MONEY_RECEIPT_NO' 		=> $v_tm_money_receipt_no_in,
								'BACK_DATE' 			=> $v_back_date_flag,
								'STOCK_ORDER_ID' 		=> $retStockOrderID,
								'OPERATE_BY' 			=> $v_tm_user_id_in,
							);
							$master->exchangeArray($masterData);
							//echo "<pre>"; print_r($master);die();
							if($returnTransactionNo=$this->getMasterTable()->saveMaster($master)) {
								for($i=0;$i<sizeof($tcAccCodes);$i++) {
									// Child Table Data Insert Start BY Akhand
									$childData		= array();
									$childData 	= array(
															'TRAN_NO'		=> $returnTransactionNo,
															'AC_CODE' 		=> $tcAccCodes[$i],				
															'BRANCH_ID' 	=> $v_tc_branch_id_in,
															'NTR' 			=> $tcNtrs[$i],	
															'CBJT' 			=> $v_tm_cbjt_in,
															'CB_CODE' 		=> $v_tm_cb_code_in,
															'NARRATION' 	=> $tcNarrations[$i],	
															'AMOUNT' 		=> $tcAmounts[$i],	
															'RECORD_DATE' 	=> '',
														);
									$child->exchangeArray($childData);
									//echo "<pre>"; print_r($child); die();
									if($this->getChildTable()->saveChild($child)) {
										$success	= true;
									} else {
										$success	= false;
										break;
									}
									// Child Table Data Insert End BY Akhand
								}
							} else {
								$success	= false;
								break;
							}
							// Master Table Data Insert Start BY Akhand
						} else {
							$success	= false;
							break;
						}
						// Voucher Table Data Insert End BY Akhand
						
						// Receive Voucher Entry Start to Accounts//
						// if net payment greater than zero condition voucher insert start process start herve Voucher Entry End to Accounts//
						} // if net payment greae
						if($stockDetails['NET_PAYMENT'] > 0){
							$tm_drawn_on		= 'N/A';
							$tm_auto_tran 		= 'y';
							
							$transStatus   		= (int) 0;
							$flag				= 0;
							$tmTransectionDate	= date("d-m-Y", strtotime($businessDate));
							$tmNtr              = 'C';
							
							$tmCbjt 		    = '';
							$tmCbCOACode        = '';
							$effected_bank_tran	= '';
							$tmChequeNo 	    = '';
							$tmChqDate 	        = '';
							
							$tcAccCodes       	= array();
							$tcNtrs           	= array();
							$tcNarrations     	= array();
							$tcAmounts        	= array();
							$tcAccCode		  	= '';
							$tcAmount		 	= '';
							$tcNarration	  	= '';
							$tcBranchId			= $BRANCH_ID;
							$tcNarration 		= $RECEIVE_PARTICULARS;
							
							$cbCode					= '';
							$tmInvoiceNo			= 'Auto';
							$tmMoneyReceiptNo 		= 'Auto';
							$backDateFlag			= '';
							
							//echo '<pre>'; print_r($VOUCHER_COA_CODE[$RID]["COA_CODE"]);die();
							$ALL_COA_CODE		= $RECEIVE_VOUCHER_COA_CODE[$RID]["COA_CODE"];
							foreach($ALL_COA_CODE as $index=>$ALL_COA_CODE_VALUE) {
								$tcAccCode	= $RECEIVE_VOUCHER_COA_CODE[$RID]["COA_CODE"][$index];
								$tcAmount   = $RECEIVE_VOUCHER_COA_CODE[$RID]["VOUCHER_AMOUNT"][$index];
								$tcNtr		= str_replace(",", "", $RECEIVE_VOUCHER_COA_CODE[$RID]["VOUCHER_TYPE"][$index]);
								
								if(!empty($tmTransectionDate) 
								   && !empty($tcAccCode) 
								   && !empty($tcBranchId) 
								   && !empty($tcAmount)
								   && !empty($tcNtr)) {
									if(substr($tcAccCode,0,3) == '303'){
										$tmCbjt  			= 'C';
										$tmCbCOACode    	= $tcAccCode;
									} else if(substr($tcAccCode,0,3) == '304') {
										$tmCbjt       		= 'B';
										$tmCbCOACode        = $tcAccCode;
										$effected_bank_tran	= $postedData["EFFECTED_AT_BANK"];
										$tmChequeNo   		= (isset($postedData["chequeNo"])) ? $postedData["chequeNo"]:'';
										$tmChqDate			= date("d-m-Y", strtotime($postedData["chq_date"]));
									} else {
										$tcAccCodes[]		= $tcAccCode;
										$tcNtrs[]       	= $tcNtr;
										$tcNarrations[] 	= $tcNarration;
										$tcAmounts[]    	= str_replace(",", "", $tcAmount);   
									}
									$flag 					= 1;
								}
							}
							//echo "<pre>"; print_r($tcAccCodes); print_r($tcNtrs); print_r($tcAmounts); print_r($tcNarrations); die();
							
							$v_tm_tran_dt_in        	= $tmTransectionDate;
							$v_tm_drown_on_in       	= $tm_drawn_on;
							$v_tm_auto_tran_in     	 	= $tm_auto_tran;
							$v_tm_ntr_in            	= $tmNtr;
							$v_tm_cbjt_in           	= $tmCbjt;
							$v_tm_cb_code_in        	= $tmCbCOACode;
							$v_tm_chq_no_in         	= $tmChequeNo;
							$v_tm_chq_dt_in         	= $tmChqDate;
							
							$v_tm_invoice_no_in			= $tmInvoiceNo;
							$v_tm_money_receipt_no_in	= $tmMoneyReceiptNo;
							$v_cb_code_cond				= $cbCode;
							$v_back_date_flag			= $backDateFlag;
							
							$v_business_date_in			= $tmTransectionDate;
							$v_tc_branch_id_in			= $tcBranchId;
							
							$v_tm_user_id_in			= '';
							$v_effected_bank_tran_in	= $effected_bank_tran;
							$v_trans_status_in_out		= $transStatus;
							$v_msg_out					= $msg;
			
							$v_tc_ac_code_in			= $tcAccCodes;
							$v_tc_ntr_in				= $tcNtrs;
							$v_tc_narration_in			= $tcNarrations;
							$v_tc_amount_in				= $tcAmounts;
							
							if($v_effected_bank_tran_in = 'y') {
								$v_chq_effected_dt 	= $v_tm_tran_dt_in;
							}
							
							if($v_tm_ntr_in == '') {
								$v_voucher_type 		= 'JV';
							} else {
								if($v_tm_ntr_in == 'D') {
									if ($v_cb_code_cond == 'bank') {
										$v_voucher_type = 'BP';
									} else {
										$v_voucher_type = 'CP';
									}
								} else {
									if ($v_cb_code_cond == 'bank') {
										$v_voucher_type = 'BR';
									} else {
										$v_voucher_type = 'CR';
									}
								}		
							}
							
							// Voucher Table Data Insert Start BY Akhand
							$voucher 			= new Voucher();
							$voucherData		= array();					
							$voucherData		= array(
								'BRANCH_ID' 			=> $v_tc_branch_id_in,
								'V_YEAR' 				=> date("Y", strtotime($tmTransectionDate)),
								'CREDIT_VOUCHER' 		=> $v_voucher_type,
							);
							$voucher->exchangeArray($voucherData);
							//echo "<pre>"; print_r($voucher);die();
							if($returnVoucher = $this->getVoucherTable()->saveVoucher($voucher)) {
								// Master Table Data Insert Start BY Akhand
								$masterData	= array();
								$masterData 	= array(
									'TRAN_NO'				=> '',
									'TRAN_DATE' 			=> $v_tm_tran_dt_in,				
									'VOUCHER_NO' 			=> $returnVoucher,
									'NTR' 					=> $v_tm_ntr_in,
									'CBJT' 					=> $v_tm_cbjt_in,
									'CB_CODE' 				=> $v_tm_cb_code_in,
									'CHEQUE_NO' 			=> $v_tm_chq_no_in,
									'CHEQUE_DATE' 			=> $v_tm_chq_dt_in,
									'EFFECTED_DATE' 		=> $v_chq_effected_dt,
									'RECONCILIATION_FLAG' 	=> $v_effected_bank_tran_in,
									'RECORD_DATE' 			=> '',
									'DRAWN_ON' 				=> $v_tm_drown_on_in,
									'AUTO_TRANSACTION_FLAG' => $v_tm_auto_tran_in,
									'INVOICE_NO' 			=> $v_tm_invoice_no_in,
									'MONEY_RECEIPT_NO' 		=> $v_tm_money_receipt_no_in,
									'BACK_DATE' 			=> $v_back_date_flag,
									'OPERATE_BY' 			=> $v_tm_user_id_in,
								);
								$master->exchangeArray($masterData);
								//echo "<pre>"; print_r($master);die();
								if($returnTransactionNo=$this->getMasterTable()->saveMaster($master)) {												
									for($i=0;$i<sizeof($tcAccCodes);$i++) {
										// Child Table Data Insert Start BY Akhand
										$childData		= array();
										$childData 	= array(
											'TRAN_NO'		=> $returnTransactionNo,
											'AC_CODE' 		=> $tcAccCodes[$i],				
											'BRANCH_ID' 	=> $v_tc_branch_id_in,
											'NTR' 			=> $tcNtrs[$i],	
											'CBJT' 			=> $v_tm_cbjt_in,
											'CB_CODE' 		=> $v_tm_cb_code_in,
											'NARRATION' 	=> $tcNarrations[$i],	
											'AMOUNT' 		=> $tcAmounts[$i],	
											'RECORD_DATE' 	=> '',
										);
										$child->exchangeArray($childData);
										//echo "<pre>"; print_r($child); die();
										if($this->getChildTable()->saveChild($child)) {
											$success	= true;
										} else {
											$success	= false;
											break;
										}
										// Child Table Data Insert End BY Akhand
									}
								} else {
									$success	= false;
									break;
								}
								// Master Table Data Insert Start BY Akhand
							} else {
								$success	= false;
								break;
							}
							// Receiter than zero condition voucher insert start process ends here
					}
					// Voucher Generate End by Akhand
					//echo $allChartOfAccountDisplay;die();
					if($success) {
						//$this->getStockEntryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																<tr class='valid_msg'>
																	<td width='100%' style='text-align:center;'>{$msg}</td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('salesreturn');
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
		public function getSRListAction() {
			$id =  $this->params()->fromQuery('id', 0);
			//echo $id;die();
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
				//echo $id;die();
				$templateList = $this->getRetailerInformationTable()->getSRList($id);
				$data = array();
				if($templateList) {
					foreach($templateList as $row) {
						$data[] = array(
										'EMPLOYEE_ID' => $row->EMPLOYEE_ID,
										'EMPLOYEE_NAME' => $row->EMPLOYEE_NAME
									);
					}
				}
				//echo $data;
				echo json_encode($data);
				exit;
			}
		}
		public function getRETListAction() {
			$id =  $this->params()->fromQuery('id', 0);
			//echo $id;die();
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
				//echo $id;die();
				$templateList = $this->getRetailerInformationTable()->getRETList($id);
				$data = array();
				if($templateList) {
					foreach($templateList as $row) {
						$data[] = array(
										'RETAILER_ID' => $row->RETAILER_ID,
										'NAME' => $row->SHOP_NAME
									);
					}
				}
				//echo $data;
				echo json_encode($data);
				exit;
			}
		}
		public function getSRWiseRETListAction() {
			$id =  $this->params()->fromQuery('id', 0);
			//echo $id;die();
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
				//echo $id;die();
				$templateList = $this->getRetailerInformationTable()->getSRWiseRETList($id);
				$data = array();
				if($templateList) {
					foreach($templateList as $row) {
						$data[] = array(
										'RETAILER_ID' => $row->RETAILER_ID,
										'NAME' => $row->SHOP_NAME,
										'ZONESHORTNAME' => $row->ZONESHORTNAME,
									);
					}
				}
				//echo $data;
				echo json_encode($data);
				exit;
			}
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
		
		/*
		public function getSuggestRefCOANameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower($_REQUEST['no']);
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getRETStockDetailsTable()->fetchSalesStockWiseModelDetails($input);	
			$coaCode = '';		
			foreach ($IPAData as $selectOption) {
				$catId = $selectOption['CATEGORY_ID'];
				$catName = $selectOption['CATEGORY_NAME'];
				$catIDName = $catId.",".$catName;
				$sumOfQuantity = $this->getRETStockDetailsTable()->fetchRetSumofStockQuantity($catId);
				$totalSumOfQuantity = '';
				foreach ($sumOfQuantity as $sumOfQuantityData) {
					$totalSumOfQuantity = $sumOfQuantityData['RET_QUANTITY'];
					$sellPrice 			= $sumOfQuantityData['SALE_PRICE'];
					$catPriceID 		= $sumOfQuantityData['CAT_PRICE_ID'];
					$coaCode 			= $sumOfQuantityData['COA_CODE'];
					$coaName 			= $sumOfQuantityData['COA_NAME'];
					$buyPrice 			= $sumOfQuantityData['BUY_PRICE'];
				}
				$str .= "<div align='left' onClick=\"fill_id('".$catIDName."','".$no."','".$buyPrice."','".$catPriceID."','".$totalSumOfQuantity."','".$coaCode."','".$coaName."');\"><b>".$catName."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		*/
		public function getSuggestRefCOANameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );
			//$suppInfoId = $_REQUEST['suppInfoId'];
			if(!empty($input)){
					$investorInfoArray 	= array();
					$IPAData = $this->getRETStockDetailsTable()->fetchSalesStockWiseModelDetails($input);	
					$coaCode = '';		
					foreach ($IPAData as $selectOption) {
						$catId = $selectOption['CATEGORY_ID'];
						$catName = $selectOption['CATEGORY_NAME'];
						$catIDName = $catId.",".$catName;
						$sumOfQuantity = $this->getRETStockDetailsTable()->fetchRetSumofStockQuantity($catId);
						$totalSumOfQuantity = '';
						foreach ($sumOfQuantity as $sumOfQuantityData) {
							$totalSumOfQuantity = $sumOfQuantityData['RET_QUANTITY'];
							$sellPrice 			= $sumOfQuantityData['SALE_PRICE'];
							$catPriceID 		= $sumOfQuantityData['CAT_PRICE_ID'];
							$coaCode 			= $sumOfQuantityData['COA_CODE'];
							$coaName 			= $sumOfQuantityData['COA_NAME'];
							$buyPrice 			= $sumOfQuantityData['BUY_PRICE'];
						}
						//$str .= "<div align='left' onClick=\"fill_id('".$catIDName."','".$no."','".$buyPrice."','".$catPriceID."','".$totalSumOfQuantity."','".$coaCode."','".$coaName."');\"><b>".$catName."</b></div>";
					}
				
				if(strlen($catId)>0){
						//$str .= $catIDName;
						$str = $catIDName.",".$no.",".$buyPrice.",".$catPriceID.",".$totalSumOfQuantity.",".$coaCode.",".$coaName;
						//$str = 1;
					}else{
						$str = 0;
					}
			}else{
				$str='';
				
				$IPAData = $this->getRETStockDetailsTable()->fetchSalesStockWiseModelDetails($input='');		
				$i=0;
				foreach ($IPAData as $selectOption) {
					$coaCode = $selectOption['CATEGORY_ID'];
					$coaHead = $selectOption['CATEGORY_NAME'];
					$coaCodeHead = $coaCode.",".$coaHead;
					$str .= "<option value='".$coaHead."'></option>";
				 $i++;
				}
			
			}
			
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
		public function getRetCOACodeAction() {
			$retID = $_REQUEST['retID'];
			$payableCoa='';
			$retDetails_array = array();
			$IPAData = $this->getRetailerInformationTable()->getRetCOACode($retID);			
			foreach ($IPAData as $selectOption) {
				$receivableCoa = $selectOption['RECEIVABLE_COA'];
				$receivableCoaName = $selectOption['COA_NAME'];
				$retShortName = $selectOption['SHOP_NAME'];
				$retDetails_array[] = array('receivableCoa'=>$receivableCoa,'receivableCoaName'=>$receivableCoaName,'retShortName'=>$retShortName);
			}
			echo json_encode($retDetails_array);
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
		public function getRETStockOrderTable() {
			if(!$this->retstockOrderTable) {
				$sm = $this->getServiceLocator();
				$this->retstockOrderTable = $sm->get('Inventory\Model\RETStockOrderTable');
			}
			return $this->retstockOrderTable;
		}
		public function getRETStockDetailsTable() {
			if(!$this->retstockDetailsTable) {
				$sm = $this->getServiceLocator();
				$this->retstockDetailsTable = $sm->get('Inventory\Model\RETStockDetailsTable');
			}
			return $this->retstockDetailsTable;
		}
		public function getSRStockDetailsTable() {
			if(!$this->srStockDetailsTable) {
				$sm = $this->getServiceLocator();
				$this->srStockDetailsTable = $sm->get('Inventory\Model\SRStockDetailsTable');
			}
			return $this->srStockDetailsTable;
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
		public function getRetailerInformationTable() {
			
			if(!$this->retailerInformationTable) {
				$sm = $this->getServiceLocator();
				$this->retailerInformationTable = $sm->get('LocalSetting\Model\RetailerInformationTable');
			}
			return $this->retailerInformationTable;
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