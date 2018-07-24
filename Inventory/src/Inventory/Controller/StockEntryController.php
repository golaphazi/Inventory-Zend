<?php
	namespace Inventory\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Inventory\Form\StockEntryForm;
	use Accounts\Model\TrialBalanceTable;
	
	use Inventory\Model\StockOrder;
	use Inventory\Model\StockEntry;
	
	
	use Accounts\Model\Master;
	use Accounts\Model\Child;
	use Accounts\Model\Voucher;
	use Accounts\Model\PaymentTransaction;
	
	
	use Zend\Session\Container as SessionContainer;
	
	class StockEntryController extends AbstractActionController {		
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
		protected $paymentTransactionTable;
				
		public function indexAction() {
			//echo 'hi there';die();
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Inventory',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new StockEntryForm('stockentry', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$stockentry = new StockEntry();
				$form->setInputFilter($stockentry->getInputFilter());
				$form->setData($request->getPost());
				//echo '<pre>';print_r($request->getPost());die();
				//if($form->isValid()) {
					$msg = '';
					$stockData	= array();
					$postedData = $request->getPost();
					$this->session 		= new SessionContainer('post_supply');
					$businessDate 		= $this->session->businessdate;
					$BRANCH_ID			= 1;//$this->session->branchid;
					$stockDetails		= array();
					$stockWiseVoucher	= array();
					$oddClass			= "#EEE9E9";
					$evenClass			= "#F7F4F4";
					$PARTICULARS_INVESTMENT = '';
					$CATEGORY_NAME = '';
					$SUPPLIER_INFO_ID = $postedData["SUPPLIER_INFO_ID1"];
					$SUPP_SHORT_NAME = $postedData["SUPP_SHORT_NAME1"];
					$VOUCHER_SUPPLIER_ID[]	= $SUPPLIER_INFO_ID;
					if(!isset($stockWiseVoucher[$SUPPLIER_INFO_ID])){
						$stockWiseVoucher[$SUPPLIER_INFO_ID]	= array();
					}
					for($i = 1; $i < $postedData->NumberOfRows; $i++) {
						$cOACodeData = $postedData["CATEGORY_ID{$i}"];
						if(!empty($cOACodeData)) {
							$j	= $i-1;					
							$stockData['SUPPLIER_INFO_ID'][$j]			= $SUPPLIER_INFO_ID;
							$stockData['CATEGORY_ID'][$j]				= $postedData["CATEGORY_ID{$i}"];
							$stockData['CATEGORY'][$j]					= $postedData["CATEGORY{$i}"];
							$stockData['QUANTITY'][$j]					= $postedData["QUANTITY{$i}"];
							$stockData['BUY_PRICE'][$j]					= $postedData["BUY_PRICE{$i}"];
							$stockData['TOTAL_AMOUNT'][$j]				= $postedData["TOTAL_AMOUNT{$i}"];
							$stockData['DISCOUNT'][$j]					= str_replace(",", "", $postedData["DISCOUNT{$i}"]);
							$stockData['DISCOUNTMODE'][$j]				= $postedData["DISCOUNTMODE{$i}"];
							$stockData['INDVDISCOUNTHIDDEN'][$j]		= $postedData["INDVDISCOUNTHIDDEN{$i}"];
							$stockData['AVG_RATE'][$j]					= $postedData["AVG_RATE{$i}"];
							$stockData['NET_AMOUNT'][$j]				= $postedData["NET_AMOUNT{$i}"];
							$stockData['CAT_PRICE_ID'][$j]				= $postedData["CAT_PRICE_ID{$i}"];
							$stockData['REMARKS'][$j]					= $postedData["REMARKS{$i}"];
							
							$DISCOUNT_AMOUNT	= 0;
							$DISCOUNT_TYPE		= '';
							
							if($postedData["DISCOUNT_TYPE"] == 'individual'){
								/*if($postedData["DISCOUNTMODE"] == 'percent') {
									$DISCOUNT_AMOUNT += $postedData["INDVDISCOUNTHIDDEN{$i}"];
								}*/
								$DISCOUNT_AMOUNT	= str_replace(",", "", $postedData["TOTALDISCOUNT_HIDDEN"]);
								$DISCOUNT_TYPE		= 'individual';
							} else if($postedData["DISCOUNT_TYPE"] == 'intotal') {
								$DISCOUNT_AMOUNT	= str_replace(",", "", $postedData["INTOTALDISCOUNT"]);
								$DISCOUNT_TYPE		= 'intotal';
							}
							
							$stockDetails = array(
													"PAYABLE_COA_CODE"		=> $postedData["PAYABLE_COA1"],
													"PAYABLE_COA_NAME"		=> trim($postedData["PAYABLE_COA_NAME1"]),
													"INVESTMENT_COA_CODE"	=> $postedData["COA_CODE{$i}"],
													"INVESTMENT_COA_NAME"	=> $postedData["COA_NAME{$i}"],
													"DISCOUNT_COA_CODE"		=> $postedData["DISCOUNT_COACODE"],
													"DISCOUNT_COA_NAME"		=> $postedData["DISCOUNT_COANAME"],
													"AMOUNT"				=> str_replace(",", "", $postedData["TOTAL_AMOUNT{$i}"]),
													"DISCOUNT"				=> $DISCOUNT_AMOUNT,
													"DISCOUNT_TYPE"			=> $DISCOUNT_TYPE,
													"CATEGORY"				=> $postedData["CATEGORY{$i}"],
													"SUPP_SHORT_NAME"		=> $SUPP_SHORT_NAME,
													"NET_PAYMENT"			=> str_replace(",", "", $postedData["NET_PAYMENT"]),
													"NETTOTAL_HIDDEN"		=> str_replace(",", "", $postedData["NETTOTAL_HIDDEN"]),
												);
							array_push($stockWiseVoucher[$SUPPLIER_INFO_ID],$stockDetails);
						}
					}
					
					$stockData['BRANCH_ID'] 							= $BRANCH_ID;
					$stockData['tranDateTo']							= $postedData["tranDateTo"];
					$stockData['insertJournal']							= $postedData["insertJournal"];
					$stockData['NumberOfRows']							= $postedData["NumberOfRows"];
					$stockData['DUE']									= $postedData["DUE"];
					$stockData['NET_PAYMENT']							= str_replace(",", "", $postedData["NET_PAYMENT"]);
					$stockData['ORDER_NO']								= $postedData["ORDER_NO"];
					$stockData['INVOICE_NO']							= $postedData["INVOICE_NO"];
					//echo '<pre>';print_r($stockData);die();
					
					
					$stockorder = new StockOrder();
					$stockorderData = array();
					$stockorderData['ORDER_NO'] 						= $postedData["ORDER_NO"];
					$stockorderData['DISCOUNT_TYPE'] 					= $postedData["DISCOUNT_TYPE"];
					$stockorderData['TOTAL_AMOUNT']						= str_replace(",", "", $postedData["TOTALAMOUNT_HIDDEN"]);
					if($postedData["DISCOUNT_TYPE"] == 'individual'){
						$stockorderData['DISCOUNT_AMOUNT']				= str_replace(",", "", $postedData["INTOTALDISCOUNT"]);//str_replace(",", "", $postedData["TOTALDISCOUNT_HIDDEN"]);
					} else if($postedData["DISCOUNT_TYPE"] == 'intotal') {
						$stockorderData['DISCOUNT_AMOUNT']				= str_replace(",", "", $postedData["INTOTALDISCOUNT"]);
					}
					$stockorderData['NET_AMOUNT']						= str_replace(",", "", $postedData["NETTOTAL_HIDDEN"]);
					$stockorderData['PAYMENT_AMOUNT']					= str_replace(",", "", $postedData["NET_PAYMENT"]);
					$stockorderData['REMAINING_AMOUNT']					= str_replace(",", "", $postedData["DUE"]);
					$stockorderData['LESS_DESCRIPTION']					= $postedData["LESS_DESCRIPTION"];
					$stockorder->exchangeArray($stockorderData);
					$stockOrderID = $this->getStockOrderTable()->saveStockOrder($stockorder);
					if($stockOrderID > 0){
						$stockData['STOCK_ORDER_ID'] = $stockOrderID;
						$stockentry->exchangeArray($stockData);
						if($msg = $this->getStockEntryTable()->saveStock($stockentry)) {							
							$paymenttransaction = new PaymentTransaction();
							$paymenttransactionData = array();
							$paymenttransactionData['SUPPLIER_INFO_ID'] 	= $SUPPLIER_INFO_ID;
							$paymenttransactionData['TRANSACTION_FLAG'] 	= 'buy';
							$paymenttransactionData['BRANCH_ID'] 			= $BRANCH_ID;
							$paymenttransactionData['AMOUNT']				= str_replace(",", "", $postedData["NETTOTAL_HIDDEN"]);							
							$paymenttransaction->exchangeArray($paymenttransactionData);
							$success = $this->getPaymentTransactionTable()->savePaymentTransaction($paymenttransaction);
							//$success = true;							
							$msg = "Successfull". '&nbsp;Print Invoice&nbsp;<a onclick="if(confirm(\'Are you sure you want to print this invoice now?\')){return true;} else {return false;};" href="/branchvoucherprint/purchaseinvoiceprint?invoiceNo='.$postedData["INVOICE_NO"].'&invoiceType=purchase" target="_blank">Click Here</a>';
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
					$chartOfAccountDisplayInvestment	= "";
					$chartOfAccount						= '';
					$oddClass							= "#EEE9E9";
					$evenClass							= "#F7F4F4";
					$chartOfAccountPayment				= "";
					
					$SUPPLIER_DATA	= array_unique($VOUCHER_SUPPLIER_ID);
					$SUPPLIER_DATAS	= array();
					foreach($SUPPLIER_DATA as $SUPPLIER_DATA_VALUE) {
						$SUPPLIER_DATAS[]	= $SUPPLIER_DATA_VALUE;
					}
					$voucherTypeIndex 		= 0;
					for($o=0;$o<sizeof($SUPPLIER_DATAS);$o++) {
						$SID 					= $SUPPLIER_DATAS[$o];
						$PARTICULARS_INVESTMENT	= "";
						$INVESTMENT_COA_CODE	= "";
						$INVESTMENT_COA_NAME	= "";
						$PAYABLE_COA_CODE		= "";
						$PAYABLE_COA_NAME		= "";
						$TOTAL_PAYABLE_AMOUNT 	= 0;
						$TOTAL_DISCOUNT_AMOUNT	= 0;
						
						for($c=0;$c<sizeof($stockWiseVoucher[$SID]);$c++) {
							$BRANCH_ID				= $BRANCH_ID;
							$PAYABLE_COA_CODE		= $stockWiseVoucher[$SID][$c]['PAYABLE_COA_CODE'];
							$PAYABLE_COA_NAME		= $stockWiseVoucher[$SID][$c]['PAYABLE_COA_NAME'];
							$INVESTMENT_COA_CODE	= $stockWiseVoucher[$SID][$c]['INVESTMENT_COA_CODE'];
							$INVESTMENT_COA_NAME	= $stockWiseVoucher[$SID][$c]['INVESTMENT_COA_NAME'];
							$DISCOUNT_COA_CODE		= $stockWiseVoucher[$SID][$c]['DISCOUNT_COA_CODE'];
							$DISCOUNT_COA_NAME		= $stockWiseVoucher[$SID][$c]['DISCOUNT_COA_NAME'];
							$AMOUNT					= $stockWiseVoucher[$SID][$c]['AMOUNT'];
							$DISCOUNT_TYPE			= $stockWiseVoucher[$SID][$c]['DISCOUNT_TYPE'];
							$DISCOUNT				= $stockWiseVoucher[$SID][$c]['DISCOUNT'];
							$CATEGORY				= $stockWiseVoucher[$SID][$c]['CATEGORY'];
							$NET_PAYMENT			= $stockWiseVoucher[$SID][$c]['NET_PAYMENT'];
							$NETTOTAL_HIDDEN		= $stockWiseVoucher[$SID][$c]['NETTOTAL_HIDDEN'];
							
							if($DISCOUNT_TYPE == 'individual'){
								$TOTAL_DISCOUNT_AMOUNT	= $DISCOUNT;
							} else if($DISCOUNT_TYPE == 'intotal') {
								$TOTAL_DISCOUNT_AMOUNT	= $DISCOUNT;
							}
							
							$SUPP_SHORT_NAME		= $SUPP_SHORT_NAME;
							$PARTICULARS_INVESTMENT	.= " ".$CATEGORY.", ";
							if($c == 0) {
								$chartOfAccountDisplay	= "
															<table cellspacing='0' class='vtbl' >
																<tr class='head'>
																	<td colspan='4' align='center'>
																		Journal Voucher for Supplier : {$SUPP_SHORT_NAME}
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
							
							$INVESTMENT_DEBIT_CHECKED					= "checked='checked'";
							$INVESTMENT_CREDIT_CHECKED					= "";
							$INVESTMENT_DEBIT_DISABLED					= "";
							$INVESTMENT_CREDIT_DISABLED					= "disabled='disabled'";
							$INVESTMENT_AMOUNT_ALIGN					= "left";
							$INVESTMENT_VOUCHER_TYPE					= "D";
							$VOUCHER_COA_CODE[$SID]['COA_CODE'][]		= $INVESTMENT_COA_CODE;
							$VOUCHER_COA_CODE[$SID]['VOUCHER_TYPE'][]	= $INVESTMENT_VOUCHER_TYPE;
							$VOUCHER_COA_CODE[$SID]['VOUCHER_AMOUNT'][]	= abs($AMOUNT) - abs($TOTAL_DISCOUNT_AMOUNT);
							$chartOfAccountDisplayInvestment	.= "
																	<tr class='{$oddClass}'>
																		<td align='left'>
																			{$INVESTMENT_COA_NAME}
																		</td>
																		<td align='center'>																												
																			<input type='hidden' name='COA_CODE[]' value='{$INVESTMENT_COA_CODE}'/>
																			{$INVESTMENT_COA_CODE}
																		</td>
																		<td align='center'>
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$INVESTMENT_DEBIT_CHECKED} {$INVESTMENT_DEBIT_DISABLED}/>Dr 
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$INVESTMENT_CREDIT_CHECKED} {$INVESTMENT_CREDIT_DISABLED}/>Cr
																		</td>
																		<td align='{$INVESTMENT_AMOUNT_ALIGN}'>
																			<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																		</td>
																	</tr>
																	";
							$voucherTypeIndex++;
						}
						if($DISCOUNT_TYPE == 'individual'){
							$TOTAL_PAYABLE_AMOUNT	= $NETTOTAL_HIDDEN - abs($TOTAL_DISCOUNT_AMOUNT);
						} else if($postedData["DISCOUNT_TYPE"] == 'intotal') {
							$TOTAL_PAYABLE_AMOUNT	= $NETTOTAL_HIDDEN - abs($TOTAL_DISCOUNT_AMOUNT);
						}
						
						$PAYABLE_DEBIT_CHECKED						= "";
						$PAYABLE_CREDIT_CHECKED						= "checked='checked'";
						$PAYABLE_DEBIT_DISABLED						= "disabled='disabled'";
						$PAYABLE_CREDIT_DISABLED					= "";
						$PAYABLE_AMOUNT_ALIGN						= "right";
						$PAYABLE_VOUCHER_TYPE						= "C";
						$VOUCHER_COA_CODE[$SID]['COA_CODE'][]		= $PAYABLE_COA_CODE;
						$VOUCHER_COA_CODE[$SID]['VOUCHER_TYPE'][]	= $PAYABLE_VOUCHER_TYPE;
						//$VOUCHER_COA_CODE[$SID]['VOUCHER_AMOUNT'][]	= abs($TOTAL_PAYABLE_AMOUNT) - abs($TOTAL_DISCOUNT_AMOUNT);
						$VOUCHER_COA_CODE[$SID]['VOUCHER_AMOUNT'][]	= abs($TOTAL_PAYABLE_AMOUNT);
						
						$chartOfAccountDisplayInvestment	.= "
																<tr class='{$oddClass}'>
																	<td align='left'>
																		{$PAYABLE_COA_NAME}
																	</td>
																	<td align='center'>																												
																		<input type='hidden' name='COA_CODE[]' value='{$PAYABLE_COA_CODE}'/>
																		{$PAYABLE_COA_CODE}
																	</td>
																	<td align='center'>
																	  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$PAYABLE_DEBIT_CHECKED} {$PAYABLE_DEBIT_DISABLED}/>Dr 
																	  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$PAYABLE_CREDIT_CHECKED} {$PAYABLE_CREDIT_DISABLED}/>Cr
																	</td>
																	<td align='{$PAYABLE_AMOUNT_ALIGN}'>
																		<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($TOTAL_PAYABLE_AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																	</td>
																</tr>
																";
						$voucherTypeIndex++;
						
						//if($TOTAL_DISCOUNT_AMOUNT > 0){
//							$DISCOUNT_DEBIT_CHECKED						= "";
//							$DISCOUNT_CREDIT_CHECKED					= "checked='checked'";
//							$DISCOUNT_DEBIT_DISABLED					= "disabled='disabled'";
//							$DISCOUNT_CREDIT_DISABLED					= "";
//							$DISCOUNT_AMOUNT_ALIGN						= "right";
//							$DISCOUNT_VOUCHER_TYPE						= "C";	
//							$VOUCHER_COA_CODE[$SID]['COA_CODE'][]		= $DISCOUNT_COA_CODE;
//							$VOUCHER_COA_CODE[$SID]['VOUCHER_TYPE'][]	= $DISCOUNT_VOUCHER_TYPE;
//							$VOUCHER_COA_CODE[$SID]['VOUCHER_AMOUNT'][]	= abs($TOTAL_DISCOUNT_AMOUNT);
//							$chartOfAccountDisplayInvestment	.= "
//																	<tr class='{$oddClass}'>
//																		<td align='left'>
//																			{$DISCOUNT_COA_NAME}
//																		</td>
//																		<td align='center'>																												
//																			<input type='hidden' name='COA_CODE[]' value='{$DISCOUNT_COA_CODE}'/>
//																			{$DISCOUNT_COA_CODE}
//																		</td>
//																		<td align='center'>
//																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$DISCOUNT_DEBIT_CHECKED} {$DISCOUNT_DEBIT_DISABLED}/>Dr 
//																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$DISCOUNT_CREDIT_CHECKED} {$DISCOUNT_CREDIT_DISABLED}/>Cr
//																		</td>
//																		<td align='{$DISCOUNT_AMOUNT_ALIGN}'>
//																			<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($TOTAL_DISCOUNT_AMOUNT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
//																		</td>
//																	</tr>
//																	";
//							$voucherTypeIndex++;
//							
//						}
						
						
						$chartOfAccountDisplay	.= "{$chartOfAccountDisplayInvestment}";
						$PARTICULARS_INVESTMENT	 = "Stock Purchase ".$PARTICULARS_INVESTMENT." on ".$businessDate." from ".$SUPP_SHORT_NAME."";
						$chartOfAccountDisplay	.= "
														<tr class='oddClass'>
															<td align='left'>Particulars</td>
															<td align='left' colspan='3'>
																<textarea name='PARTICULARS' cols='100' rows='2' style='text-align:left;'>".trim($PARTICULARS_INVESTMENT)."</textarea>
															</td>
														</tr>
														<tr class='evenClass'>
															<td align='left' colspan='4'>&nbsp;</td>
														</tr>
													</table>
													";
						/// Payment Vocuher Start ///
						// if net payment greater than zero //
						if($stockDetails['NET_PAYMENT'] > 0){
							$chartOfAccountPayment	= "
													<table cellspacing='0' class='vtbl' >
														<tr class='head'>
															<td colspan='4' align='center'>
																Payment Voucher for Supplier : {$SUPP_SHORT_NAME}
															</td>
														</tr>
														<tr class='head'>
															<td width='55%' align='left'>Account Head</td>
															<td width='15%' align='center'>Account Code</td>
															<td width='15%' align='center'>Dr/Cr</td>
															<td width='15%' align='right'>Amount</td>
														</tr>
													";
							$PAYMENT_COA_NAME = $PAYABLE_COA_NAME;
							$PAYMENT_COA_CODE = $PAYABLE_COA_CODE;
							$PAYMENT_DEBIT_CHECKED						= "checked='checked'";
							$PAYMENT_CREDIT_CHECKED						= "";
							$PAYMENT_DEBIT_DISABLED						= "";
							$PAYMENT_CREDIT_DISABLED					= "disabled='disabled'";
							$PAYMENT_AMOUNT_ALIGN						= "left";
							$PAYMENT_VOUCHER_TYPE						= "D";
							$PAYMENT_VOUCHER_COA_CODE[$SID]['COA_CODE'][]		= $PAYMENT_COA_CODE;
							$PAYMENT_VOUCHER_COA_CODE[$SID]['VOUCHER_TYPE'][]	= $PAYMENT_VOUCHER_TYPE;
							$PAYMENT_VOUCHER_COA_CODE[$SID]['VOUCHER_AMOUNT'][]	= abs($NET_PAYMENT);
							
							$BANK_COA_NAME = "Cash in Hand";
							$BANK_COA_CODE = "303001001";
							$BANK_DEBIT_CHECKED						= "";
							$BANK_CREDIT_CHECKED					= "checked='checked'";
							$BANK_DEBIT_DISABLED					= "disabled='disabled'";
							$BANK_CREDIT_DISABLED					= "";
							$BANK_AMOUNT_ALIGN						= "right";
							$BANK_VOUCHER_TYPE						= "C";
							$PAYMENT_VOUCHER_COA_CODE[$SID]['COA_CODE'][]		= $BANK_COA_CODE;
							$PAYMENT_VOUCHER_COA_CODE[$SID]['VOUCHER_TYPE'][]	= $BANK_VOUCHER_TYPE;
							$PAYMENT_VOUCHER_COA_CODE[$SID]['VOUCHER_AMOUNT'][]	= abs($NET_PAYMENT);
							
							
							$chartOfAccountPayment	.= "
																	<tr class='{$oddClass}'>
																		<td align='left'>
																			{$PAYMENT_COA_NAME}
																		</td>
																		<td align='center'>																												
																			<input type='hidden' name='PAYMENT_COA_CODE[]' value='{$PAYMENT_COA_CODE}'/>
																			{$PAYMENT_COA_CODE}
																		</td>
																		<td align='center'>
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='D' {$PAYMENT_DEBIT_CHECKED} {$PAYMENT_DEBIT_DISABLED}/>Dr 
																		  <input type='radio' name='VOUCHER_TYPE[$voucherTypeIndex]' value='C' {$PAYMENT_CREDIT_CHECKED} {$PAYMENT_CREDIT_DISABLED}/>Cr
																		</td>
																		<td align='{$PAYMENT_AMOUNT_ALIGN}'>
																			<input type='text' name='PAYMENT_AMOUNT[$voucherTypeIndex]' value=".number_format(abs($NET_PAYMENT),4)." style='font-family:Tahoma, Geneva, sans-serif;text-align:right;width:120px'>
																		</td>
																	</tr>
																	";
							$voucherTypeIndex++;	
							
							$chartOfAccountPayment	.= "
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
							
							$PAYMENT_PARTICULARS = 	"Paid to {$SUPP_SHORT_NAME} for the purpose of stock purchase on ".$businessDate."";
							$chartOfAccountPayment	.= "
															<tr class='oddClass'>
																<td align='left'>Particulars</td>
																<td align='left' colspan='3'>
																	<textarea name='PARTICULARS' cols='100' rows='2' style='text-align:left;'>".trim($PAYMENT_PARTICULARS)."</textarea>
																</td>
															</tr>
															<tr class='evenClass'>
																<td align='left' colspan='4'>&nbsp;</td>
															</tr>
														</table>
														";
							
							//payment transaction insert starts here //
							$paymenttransaction = new PaymentTransaction();
							$paymenttransactionData = array();
							$paymenttransactionData['SUPPLIER_INFO_ID'] 	= $SUPPLIER_INFO_ID;
							$paymenttransactionData['TRANSACTION_FLAG'] 	= 'paid';
							$paymenttransactionData['BRANCH_ID'] 			= $BRANCH_ID;
							$paymenttransactionData['AMOUNT']				= str_replace(",", "", $postedData['NET_PAYMENT']);							
							$paymenttransaction->exchangeArray($paymenttransactionData);
							$success = $this->getPaymentTransactionTable()->savePaymentTransaction($paymenttransaction);
							//$success = true;
							//payment transaction insert ends here //
							
						} // if net payment greater than zero condition ends here
						/// Payment Vocuher End ///
						$voucher 		= new Voucher();
						$voucherData	= array();
						//echo $chartOfAccountDisplay;
						//die();
						//echo $chartOfAccountPayment;die();
						$allChartOfAccountDisplay			.= $chartOfAccountDisplay;
						$chartOfAccountDisplayInvestment 	= '';
						$chartOfAccountDisplay 				= '';
						$PARTICULARS 						= $PARTICULARS_INVESTMENT;
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
						
						//echo '<pre>'; print_r($VOUCHER_COA_CODE[$SID]["COA_CODE"]);die();
						$ALL_COA_CODE		= $VOUCHER_COA_CODE[$SID]["COA_CODE"];
						foreach($ALL_COA_CODE as $index=>$ALL_COA_CODE_VALUE) {
							$tcAccCodes[]		= $VOUCHER_COA_CODE[$SID]["COA_CODE"][$index];
							$tcNtrs[]       	= $VOUCHER_COA_CODE[$SID]["VOUCHER_TYPE"][$index];
							$tcNarrations[] 	= $PARTICULARS;
							$tcAmounts[]    	= str_replace(",", "", $VOUCHER_COA_CODE[$SID]["VOUCHER_AMOUNT"][$index]);
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
								'STOCK_ORDER_ID' 		=> $stockOrderID,
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
						
						// Payment Voucher Entry Start to Accounts//
						// if net payment greater than zero condition voucher insert start process start here
						if($stockDetails['NET_PAYMENT'] > 0){
							$tm_drawn_on		= 'N/A';
							$tm_auto_tran 		= 'y';
							
							$transStatus   		= (int) 0;
							$flag				= 0;
							$tmTransectionDate	= date("d-m-Y", strtotime($businessDate));
							$tmNtr              = 'D';
							
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
							$tcNarration 		= $PAYMENT_PARTICULARS;
							
							$cbCode					= '';
							$tmInvoiceNo			= 'Auto';
							$tmMoneyReceiptNo 		= 'Auto';
							$backDateFlag			= '';
							
							//echo '<pre>'; print_r($VOUCHER_COA_CODE[$SID]["COA_CODE"]);die();
							$ALL_COA_CODE		= $PAYMENT_VOUCHER_COA_CODE[$SID]["COA_CODE"];
							foreach($ALL_COA_CODE as $index=>$ALL_COA_CODE_VALUE) {
								$tcAccCode	= $PAYMENT_VOUCHER_COA_CODE[$SID]["COA_CODE"][$index];
								$tcAmount   = $PAYMENT_VOUCHER_COA_CODE[$SID]["VOUCHER_AMOUNT"][$index];
								$tcNtr		= str_replace(",", "", $PAYMENT_VOUCHER_COA_CODE[$SID]["VOUCHER_TYPE"][$index]);
								
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
								'DEBIT_VOUCHER' 		=> $v_voucher_type,
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
							// Payment Voucher Entry End to Accounts//
						} // if net payment greater than zero condition voucher insert start process ends here
					}
					// Voucher Generate End by Akhand
					//echo $allChartOfAccountDisplay;die();
					if($success) {
						$this->getTrialBalanceTable()->updateTrialBalance($v_tm_tran_dt_in);
						//$this->getStockEntryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																<tr class='valid_msg'>
																	<td width='100%' style='text-align:center;'>{$msg}</td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('stockentry');
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
			$no = strtolower( $_REQUEST['no'] );
			$suppInfoId = $_REQUEST['suppInfoId'];
			if(!empty($input)){
				$IPAData = $this->getStockTable()->fetchModelName($input, $suppInfoId);		
				$str = '';
				//$coaCode = '';
				foreach ($IPAData as $selectOption) {
					
					$catID = $selectOption['CATEGORY_ID'];
					$catName = $selectOption['CATEGORY_NAME'];
					$buyPrice = $selectOption['BUY_PRICE'];
					$catPriceID = $selectOption['CAT_PRICE_ID'];
					$coaCode = $selectOption['COA_CODE'];
					$coaName = $selectOption['COA_NAME'];
					$catIDName = $catID.",".$catName;
					
				}
				
				if(strlen($catID)>0){
						//$str .= $catIDName;
						$str = $catIDName.",".$no.",".$buyPrice.",".$catPriceID.",".$coaCode.",".$coaName;
						//$str = 1;
					}else{
						$str = 0;
					}
			}else{
				$str='';
				
				$IPAData = $this->getStockTable()->fetchModelName($input='', $suppInfoId);			
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
		/*
		public function getSuggestRefCOANameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );
			$suppInfoId = $_REQUEST['suppInfoId'];
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getCoaTable()->fetchModelName($input, $suppInfoId);
			foreach ($IPAData as $selectOption) {
				$catID = $selectOption['CATEGORY_ID'];
				$catName = $selectOption['CATEGORY_NAME'];
				$buyPrice = $selectOption['BUY_PRICE'];
				$catPriceID = $selectOption['CAT_PRICE_ID'];
				$coaCode = $selectOption['COA_CODE'];
				$coaName = $selectOption['COA_NAME'];
				$catIDName = $catID.",".$catName;
				$str .= "<div align='left' onClick=\"fill_id('".$catIDName."','".$no."','".$buyPrice."','".$catPriceID."','".$coaCode."','".$coaName."');\"><b>".$catName."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		*/
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
		
		public function getSuppCOACodeAmountSupplyAction(){
			$suppID = $_REQUEST['srID'];
			
			$IPAData = $this->getSupplierInformationTable()->getSuppCOACodeAmountModel($suppID);			
			
			echo $IPAData;
			
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
		public function getStockTable() {
			if(!$this->stockEntryTable) {
				$sm = $this->getServiceLocator();
				$this->stockEntryTable = $sm->get('Inventory\Model\StockEntryTable');
			}
			return $this->stockEntryTable;
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
		public function getPaymentTransactionTable() {
			if(!$this->paymentTransactionTable) {
				$sm = $this->getServiceLocator();
				$this->paymentTransactionTable = $sm->get('Accounts\Model\PaymentTransactionTable');
			}
			return $this->paymentTransactionTable;
		}
		
	}
?>