<?php
	namespace Accounts\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Accounts\Form\OpeningBalanceEntryForm;
	use Accounts\Model\TrialBalanceTable;
	
	use Accounts\Model\OpeningBalanceEntry;
	
	use Zend\Session\Container as SessionContainer;
	
	class OpeningBalanceEntryController extends AbstractActionController {		
		protected $dbAdapter;
		protected $branchTable;
		protected $coaTable;
		protected $trialBalanceTable;
		protected $openingBalanceEntryTable;
		protected $investorFundTable;
		protected $portfolioCoaTable;
				
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Accounts',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new OpeningBalanceEntryForm('openingbalanceentry', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$openingbalanceentry = new OpeningBalanceEntry();
				$this->getOpeningBalanceEntryTable()->transectionStart();
				$form->setInputFilter($openingbalanceentry->getInputFilter());
				$form->setData($request->getPost());
				//echo '<pre>';print_r($request->getPost());die();
				//if($form->isValid()) {
					$msg = '';
					$oBalanceData	= array();
					$postedData 			= $request->getPost();
					for($i = 1; $i < $postedData->NumberOfRows; $i++) {
						$cOACodeData	= $postedData["coa_code{$i}"];
						if(!empty($cOACodeData)) {
							$j	= $i-1;
							$oBalanceData['coa_head'][$j]					= $postedData["coa_head{$i}"];
							$oBalanceData['coa_code'][$j]					= $postedData["coa_code{$i}"];
							$oBalanceData['COAType'][$cOACodeData] 			= $postedData["COAType{$i}"];
							$oBalanceData['amount'][$cOACodeData] 			= $postedData["amount{$i}"];
						}
					}
					
					$oBalanceData['BRANCH_ID'] 								= $postedData["BRANCH_ID"];
					$oBalanceData['tranDateTo']								= $postedData["tranDateTo"];
					$oBalanceData['paymentEntry']							= $postedData["paymentEntry"];
					$oBalanceData['NumberOfRows']							= $postedData["NumberOfRows"];
					//echo "<pre>"; print_r($oBalanceData); die();
					for($i=0;$i<sizeof($oBalanceData['coa_code']);$i++){
						$coaCode = $oBalanceData['coa_code'][$i];
						$data = array(
								'BRANCH_ID' 		=> $oBalanceData['BRANCH_ID'],
								'coa_code' 			=> $oBalanceData['coa_code'][$i],
								'COAType' 			=> $oBalanceData['COAType'][$coaCode],
								'amount' 			=> $oBalanceData['amount'][$coaCode],
								'tranDateTo' 		=> $oBalanceData['tranDateTo'],
						);
						$openingbalanceentry->exchangeArray($data);
						//echo "<pre>"; print_r($openingbalanceentry); die();
						if($this->getOpeningBalanceEntryTable()->saveOpeningBalance($openingbalanceentry)) {
							$success = 1;
						} else {
							$success = 0;
						}
					}
					//echo 'asdfasdfasd';die();
					//echo $success; die();
					if($success) {
						$this->getOpeningBalanceEntryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																<tr class='valid_msg'>
																	<td colspan='3' style='text-align:center;'><h4>Opening Balance Entry Submitted properly!</h4></td>
																</tr>
															</table>");
						return $this->redirect()->toRoute('openingbalanceentry');
					} else {
						$this->getOpeningBalanceEntryTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:50%;'>
																<tr class='error_msg'>
																	<td colspan='3' style='text-align:center;'><h4>System Error!</h4></td>
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
					$motherHead = strtolower($selectOption['MOTHER_ACCOUNT']);
				}
			} else {
				$getTBAmountSql = '';
			}
			//echo json_encode($motherHead);
			echo trim($motherHead,' ');
			exit;
		}
		
		public function getPortCOADrCrSelectAction() {
			$coaCode  = strtolower( $_REQUEST['coaCode'] );
			$motherHead      ='';
			$motherHeadRadio ='';
			if($coaCode) {
				$IPAData = $this->getPortfolioCoaTable()->fetchPCOAMotherAccount($coaCode);			
				foreach ($IPAData as $selectOption) {
					$motherHead = strtolower($selectOption['P_MOTHER_ACCOUNT']);
				}
			} else {
				$getTBAmountSql = '';
			}
			//echo json_encode($motherHead);
			echo trim($motherHead,' ');
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
		public function getSuggestRefCOANameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getCoaTable()->fetchCOAName($input);			
			foreach ($IPAData as $selectOption) {
				$coaCode = $selectOption['COA_CODE'];
				$coaHead = $selectOption['COA_NAME'];
				$coaCodeHead = $coaCode.",".$coaHead;
				$str .= "<div align='left' onClick=\"fill_id('".$coaCodeHead."','".$no."');\"><b>".$coaCode."-".$coaHead."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		
		public function getSuggestCOAPaymentRceiptAction() {
			$frm 		= strtolower( $_REQUEST['frm'] );
			$fundCode 	= strtolower($_REQUEST['fundCode']);
			$input 		= strtolower( $_REQUEST['queryString'] );
			$no 		= strtolower( $_REQUEST['no'] );
			$coa_head 	= array();
			$str		= '';
			$switch 	= '';
			if($frm=='p') {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (305, 501,502,503,504) AND SUBSTR(COA_CODE, 0, 6) NOT IN (201012,302001,302002,302003,302004,302005,302006,302007,302009,302010,302011,302012,302013) AND SUBSTR(COA_CODE, 0, 9) NOT IN (601010002,601010004,601010005,601010006,601010009) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='r') {
				$switch = "SUBSTR(COA_CODE, 0, 6) NOT IN (201001,201002,201003,201004,201005,201006,201007,201008,201009,201010,201011,201012,201013,302008)
   							AND SUBSTR(COA_CODE, 0, 9) NOT IN (302010002,503007008,601010002,601010004,601010005,601010006,601010009) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='c') {
				$switch = "SUBSTR(COA_CODE, 0, 3) IN (303,304) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (303,304) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			}
			$COAList = $this->getCoaTable()->fetchCOANameForGEntry($input,$switch);				
			$data = array();
			if($COAList) {
				foreach($COAList as $row) {
					$coaCode = $row->COA_CODE;
					$coaHead = $row->COA_NAME;
					$coaCodeHead = $coaCode.",".$coaHead;
					$str .= "<div align='left' onClick=\"fill_id('".$coaCodeHead."','".$no."');\"><b>".$coaCode."-".$coaHead."</b></div>";
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
		public function getBankReconciliationReportAction() {
			$this->session = new SessionContainer('post_supply');	
			$branchID = $_REQUEST['BRANCH_ID'];
			$branchData  = $this->getBranchTable()->getBranchInfoFinancialStatement($branchID);
			foreach ($branchData as $selectOption) {
					$companyName 	= $selectOption['COMPANY_NAME'];
					$branchName 	= $selectOption['BRANCH_NAME'];
					$branchCode 	= $selectOption['BRANCH_CODE'];
					$branchAddress 	= $selectOption['ADDRESS'];
					$webAddress 	= $selectOption['WEB'];
			}
			$fromDate  		= $this->session->businessdate;
			$cond 			= '';
			$transDateFrom 	= $_REQUEST['tranDateFrom'];
			$transDateTo 	= $_REQUEST['tranDateTo'];
			$accHead 		= $_REQUEST['accHead'];
			$accCode 		= $_REQUEST['accCode'];
			$narrationVal 	= $_REQUEST['narrationVal'];
			
			$transDateFrom1 = "to_date('".$transDateFrom."','dd-mm-yyyy')";
			$transDateTo1 	= "to_date('".$transDateTo."','dd-mm-yyyy')";
			
			if(($transDateFrom != '') && ($transDateTo != '') && ($accCode != '')) {
				$cond = "AND CB_CODE_T = $accCode AND DATE_FTDT BETWEEN $transDateFrom1 AND $transDateTo1";
				$fromDate 	= $_REQUEST['tranDateFrom'];
			} elseif (($transDateFrom != '') && ($transDateTo != '')) {
				$cond 		= "AND 	DATE_FTDT BETWEEN $transDateFrom1 AND $transDateTo1";
				$fromDate 	= $_REQUEST['tranDateFrom'];
			} elseif (($accCode != '')) {
				$cond 			= "AND CB_CODE_T = $accCode";
				$transDateTo	= 'Till';
			}  else {
				$cond 			= '';
				$transDateTo 	= 'Till';
			}
			
			$generateTable = "";
			$generateTable .= "<table border='0' cellpadding='2' cellspacing='2' width='100%' style='font-size:95%' >";
			$generateTable .= "<tr class='oddRow'>
								<td valign='top' colspan='2'  > 
										<table border='0' cellpadding='3' cellspacing='1' width='100%' style='font-size:100%' >
											<tr >
												<td valign='top' width='11%' align='left'>&nbsp;</td>
												<td valign='top' width='2%' align='center'>&nbsp;</td>
												<td colspan='7' valign='top' width='87%' align='right'>
											<a onclick='if(confirm(\"Are you sure you want to print bank reconciliation report\")){return true;} else {return false;};' href='printPendingBankReconciliationReport.php?branchName=".str_replace('&','*',$branchName)."&tranDateFrom={$transDateFrom}&tranDateTo={$transDateTo}&narrationVal={$narrationVal}&accCode={$accCode}&accHead={$accHead}' target='_blank'><img src='../img/print_icon.jpg' width='24' title='Print Bank Reconciliation Report' border='0' style='padding:0px 0px 5px 0px;' /></a>
										</td>
											</tr>
											<tr >
												<td valign='top' width='11%' align='left'> Branch</td>
												<td valign='top' width='2%' align='center'>:</td>
												<td valign='top' width='87%' align='left'>".$branchName."</td>
											</tr>
											<tr >
												<td valign='top' width='11%' align='left'> Account code</td>
												<td valign='top' width='2%' align='center'>:</td>
												<td valign='top' width='87%' align='left'>".$accCode."</td>
											</tr>
											<tr >
												<td valign='top' width='11%' align='left'> Account head</td>
												<td valign='top' width='2%' align='center'>:</td>
												<td valign='top' width='87%' align='left'>".$accHead."</td>
											</tr>
										   <tr>
												<td valign='top' align='left'> Period </td>
												<td valign='top' align='center'> :</td>
												<td valign='top' align='left'>".$fromDate." to ".$transDateTo."</td>
										  </tr>
										</table>
								 </td>
							  </tr>
							  </table>
								<table border='0' cellpadding='5' cellspacing='1' width='100%' class='tablesorter' style='font-size:75%'>
									<tr class='oddRow'>
										<th width='10%' align='center'>DATE</th>
										<th width='40%' align='left'>PARTICULARS</th>
										<th width='10%' align='left'>VOUCHER TYPE</th>
										<th width='10%' align='center'>CHEQUE NO.</th>
										<th width='10%' align='center'>BANK DATE</th>
										<th width='10%' align='right'>DEPOSIT</th>
										<th width='10%' align='right'>WITHDRAWAL</th>
									</tr>
									<tbody>
							 ";
				$sv = 1;
				$vDate 	 			= '';
				$vNumber 			= '';
				$class 				= '';
				$effDate 			= '';
				$totalDebit 		= 0;
				$totalCredit 		= 0;
				$deffDebitCredit 	= 0;
				$totNotRefBnkDebit 	= 0;
				$totNotRefBnkCredit = 0;
				if($bankReconInfo  = $this->getTrialBalanceTable()->fetchBankReconReport($branchName,$cond)){
					foreach ($bankReconInfo as $bankReconInfoData) {
						$transactionDate 	= $bankReconInfoData['DATE_FTDT'];
						$voucherNumber	  	= $bankReconInfoData['VOUCHER_NO_'];
						$particulars 		= str_replace('^','-',$bankReconInfoData['PARTICULARS_']);
						$narration 			= $bankReconInfoData['NARRATION_'];
						$debit	  			= $bankReconInfoData['DEBIT_'];
						$credit 			= $bankReconInfoData['CREDIT_'];
						$CBCode 			= $bankReconInfoData['CB_CODE_T'];
						$chqNo 				= $bankReconInfoData['CHQ_NO'];
						$effectedDate		= $bankReconInfoData['BANK_FTDT'];
						$transactionNo		= $bankReconInfoData['TRAN_NO_'];
						
						$totalDebit 	+= abs($debit);
						$totalCredit 	+= abs($credit);
						if(empty($effectedDate)){
							$totNotRefBnkDebit += abs($debit);
							$totNotRefBnkCredit += abs($credit);
							$effectedDate = "
										<input type='hidden' name='transactiondate' id='transactiondate{$transactionNo}' value='{$transactionDate}'/>	
										<input type='hidden' name='transactionNo[]' id='transactionNo{$transactionNo}' value='{$transactionNo}'/>								
										<input name='effectedDate[]' size='18' id='effectedDate{$transactionNo}' type='text' class='FormDateTypeInput' readonly='readonly' onclick=\"showCalender('effectedDate{$transactionNo}','effectedDate{$transactionNo}')\"; onchange=\"checkCalenderTransaction(this.value,$transactionNo)\"; />
							";
						}
						if($voucherNumber == $vNumber) {
							$class = $class;
						} else {
							if($class == 'evenRow') {
								$class = 'oddRow';
							} else {
								$class = 'evenRow';
							}
						}
						
						if(empty($particulars)) {
							$particulars = '&nbsp;';
						} else {
							if($voucherNumber == $vNumber) {
								$particulars = '&nbsp;';
							} else {
								$particulars = $particulars;
							}
						}
						$generateTable .= "<tr class='{$class}'>
											<td valign='top' align='left'>".$transactionDate."</td>
											<td valign='top' align='left'>".$particulars."</td>
											<td valign='top' align='left'>".$voucherNumber."</td>
											<td valign='top' align='left'>".$chqNo."</td>
											<td valign='top' align='left'>".$effectedDate."</td>
											<td valign='top' align='right'>".number_format(abs($debit),2)."</td>
											<td valign='top' align='right'>".number_format(abs($credit),2)."</td>
										  </tr>
										   ";	
						if($narrationVal == 'yes'){
							$generateTable .= "	<tr>
											<td colspan='7' class='{$class}'>
												<div style='float:left;width:10%'><b>Narration :</b></div><div style='float:left;width:80%'>{$narration}</div>
											</td>
										  </tr>
								 ";	
						}
					}
					$deffDebitCredit = $totalDebit-$totalCredit;
					if($deffDebitCredit>0){
						$generateTable .= "<tr class='oddRow' height='25'>
										<td colspan='5' align='right'></td>
										<td align='right' style='font-weight:bold;'>".number_format(abs($deffDebitCredit),2)."</td>
										<td align='right'></td>		
									</tr>";
					} else {
						$generateTable .= "<tr class='oddRow' height='25'>
										<td colspan='5' align='right'></td>
										<td align='right'></td>
										<td align='right' style='font-weight:bold;'>".number_format(abs($deffDebitCredit),2)."</td>		
									</tr>";
					}
					$generateTable .= "<tr class='oddRow' height='25'>
										<td colspan='5' align='right'>Amount not reflected in Bank:</td>
										<td align='right' style='font-weight:bold;'>".number_format(abs($totNotRefBnkDebit),2)."</td>
										<td align='right' style='font-weight:bold;'>".number_format(abs($totNotRefBnkCredit),2)."</td>		
									</tr>";
					$generateTable .= "<tr>
										<td colspan='7' align='center'>
											<input type='submit' name='reconSubmit' id='reconSubmit' value='Submit' class='FormBtn'/>
											<input type='reset' name='resetReconSubmit' id='resetReconSubmit' value='Reset' class='FormBtn'/>
										</td>
									</tr>";
				} else {
						$generateTable .= "<tr class='errorMsg' >
												<td valign='top' colspan='7' align='center'>No Data Found.</td>
											  </tr>
											 ";	
				}
				$generateTable .= "</tbody></table>";
			echo $generateTable;
			//echo '<pre>';print_r($generateTable);
			//echo json_encode($generateTable);
			exit;
		}
		
		public function getTrialBalanceTable() {
			if(!$this->trialBalanceTable) {
				$sm = $this->getServiceLocator();
				$this->trialBalanceTable = $sm->get('Accounts\Model\TrialBalanceTable');
			}
			return $this->trialBalanceTable;
		}
		
		public function getBranchTable() {
			if(!$this->branchTable) {
				$sm = $this->getServiceLocator();
				$this->branchTable = $sm->get('CompanyInformation\Model\BranchTable');
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
		
		public function getPortfolioCoaTable() {
			if(!$this->portfolioCoaTable) {
				$sm = $this->getServiceLocator();
				$this->portfolioCoaTable = $sm->get('GlobalSetting\Model\PortfolioCoaTable');
			}
			return $this->portfolioCoaTable;
		}
		
		public function getOpeningBalanceEntryTable() {
			if(!$this->openingBalanceEntryTable) {
				$sm = $this->getServiceLocator();
				$this->openingBalanceEntryTable = $sm->get('Accounts\Model\OpeningBalanceEntryTable');
			}
			return $this->openingBalanceEntryTable;
		}
		
		public function getInvestorFundTable() {
			if(!$this->investorFundTable) {
				$sm = $this->getServiceLocator();
				$this->investorFundTable = $sm->get('InvestorService\Model\InvestorFundTable');
			}
			return $this->investorFundTable;
		}
	}
?>