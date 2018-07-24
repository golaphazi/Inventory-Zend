<?php
	namespace Accounts\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Accounts\Form\JournalEntryForm;
	
	use Accounts\Model\Master;
	use Accounts\Model\Child;
	use Accounts\Model\Voucher;
	
	use Zend\Session\Container as SessionContainer;
	
	class JournalEntryController extends AbstractActionController {
		protected $dbAdapter;
		protected $coaTable;
		protected $voucherTable;
		protected $masterTable;
		protected $childTable;
		protected $trialBalanceTable;
		protected $companyTable;
		protected $moneyMarketOrganizationTable;
		
		function returnDates($fromdate, $todate) {
			$fromdate = \DateTime::createFromFormat('d-m-Y', $fromdate);
			$todate = \DateTime::createFromFormat('d-m-Y', $todate);
			return new \DatePeriod(
				$fromdate,
				new \DateInterval('P1D'),
				$todate->modify('+1 day')
			);
		}
		
		public function indexAction() {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("d-m-Y", strtotime($businessDate));
			
			
			$userInfo 						= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID						= $userInfo->id;
			
			$this->layout()->leftMenu 		= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Accounts',$USER_ID);
			$this->layout()->controller 	= $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new JournalEntryForm('journalentry', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			
			$form->get('submit')->setValue('Add');
			
			if($request->isPost()) {
				$form->setData($request->getPost());
				$postedData 			= $request->getPost();
				//echo "<pre>"; print_r($postedData); die();
				$v_voucher_no_in_out  		= '';
				$v_max_transaction_no 		= 0;
				$v_chq_effected_dt    		= '';
				$v_chq_dt             		= '';
				$v_voucher_type       		= '';
				$v_temp_voucher_type  		= '';
				$v_temp_voucher_no    		= '';
				
				$msg = '';
				$voucher 		= new Voucher();
				$voucherData	= array();
				
				$tm_drawn_on		= (isset($postedData["drawnOn"])) ? $postedData["drawnOn"]:'';
				$tm_auto_tran 		= $postedData["AUTO_TRANSACTION"];
				
				$transStatus   		= (int) 0;
				$msg				= '';
				$flag				= 0;
				$tmTransectionDate	= date("d-m-Y", strtotime($postedData["TRANSACTION_DATE"]));
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
				$tcBranchId			= $postedData["BRANCH_ID"];
				$tcNarration 		= $postedData["PARTICULARS"];
				
				$cbCode				= $postedData["cb_code"];
				$tmInvoiceNo		= $postedData["INVOICE_NO"];
				$tmMoneyReceiptNo 	= $postedData["MONEY_RECEIPT_NO"];
				$backDateFlag			= '';
				$transactionMonthYear = date("m-Y", strtotime($tmTransectionDate));
				$businessMonthYear 	  = date("m-Y", strtotime($postedData["chq_date"]));
				if($transactionMonthYear < $businessMonthYear ) {
					$backDateFlag = 'Y';
				}
				
				for($i = 1; $i < $postedData->NUMBER_OF_ROWS; $i++) {
					$coaCode	= $postedData["COA_CODE{$i}"];
					if(isset($postedData["COA_CODE{$i}"]) && isset($postedData["PAYMENT_AMOUNT{$i}"])) {
						$tcAccCode	= $postedData["COA_CODE{$i}"];
						$tcAmount   = $postedData["PAYMENT_AMOUNT{$i}"];
						$tcNtr		= (isset($postedData["VOUCHER_TYPE{$i}"])) ? $postedData["VOUCHER_TYPE{$i}"]:'';
						if(!empty($tmTransectionDate) 
						   && !empty($tcAccCode) 
						   && !empty($tcBranchId) 
						   && !empty($tcAmount)
						   && !empty($tcNtr)) {
							$tcAccCodes[]		= $tcAccCode;
							$tcNtrs[]       	= $tcNtr;
							$tcNarrations[] 	= $tcNarration;
							$tcAmounts[]    	= str_replace(",", "", $tcAmount);
							$flag 				= 1;
						}
					}
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
						} else if($v_cb_code_cond == 'cash') {
							$v_voucher_type = 'CP';
						} else {
							$v_voucher_type = 'CV';
						}
					} else {
						if ($v_cb_code_cond == 'bank') {
							$v_voucher_type = 'BR';
						}else if ($v_cb_code_cond == 'cash') {
							$v_voucher_type = 'CR';
						} else {
							$v_voucher_type = 'CV';
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
				if($returnVoucher 	= $this->getVoucherTable()->saveVoucher($voucher)) {
					$msg 			= $returnVoucher;
					// Master Table Data Insert Start BY Akhand
					$masterData		= array();
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
				// Voucher Table Data Insert End BY Akhand
				if($success) {
					$datePeriod = $this->returnDates($tmTransectionDate, $businessDate);
					$totDate = array();
					foreach($datePeriod as $date) {
						$totDate[] = $date->format('d-m-Y');
					}
					//echo "<pre>"; print_r($totDate);die();
					for($i=0;$i<sizeof($totDate);$i++){
						$bDate = $totDate[$i];
						if($this->getTrialBalanceTable()->updateTrialBalance($bDate)) {
							$success	= true;
						} else {
							$success	= false;
						}
					}
					if($success) {
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
															<tr class='valid_msg'>
																<td width='100%' style='text-align:center;'>Journal entry successfully saved.".$msg.'&nbsp;<a onclick="if(confirm(\'Are you sure you want to print this vourcher no. '.substr($msg,-16).' \')){return true;} else {return false;};" href="/branchvoucherprint/branchvoucherprint?tranDateFrom=&tranDateTo=&vType=&voucherData='.substr($msg,-16).'&branchID='.$postedData["BRANCH_ID"].'" target="_blank">Print this Voucher</a>'."</td>																
															</tr>
														</table>");
						return $this->redirect()->toRoute('journalentry');
					} else {
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
															<tr class='valid_msg'>
																<td width='100%' style='text-align:center;'>Journal entry couldn't saved.".$msg.'&nbsp;<a onclick="if(confirm(\'Are you sure you want to print this vourcher no. '.substr($msg,-16).' \')){return true;} else {return false;};" href="/branchvoucherprint/branchvoucherprint?tranDateFrom=&tranDateTo=&vType=&voucherData='.substr($msg,-16).'&branchID='.$postedData["BRANCH_ID"].'" target="_blank">Print this Voucher</a>'."</td>																
															</tr>
														</table>");
						return $this->redirect()->toRoute('journalentry');
					}
				} else {
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
															<tr class='error_msg'>
																<td width='100%' style='text-align:center;'>Journal entry couldn't saved.".$msg.'&nbsp;<a onclick="if(confirm(\'Are you sure you want to print this vourcher no. '.substr($msg,-16).' \')){return true;} else {return false;};" href="/branchvoucherprint/branchvoucherprint?tranDateFrom=&tranDateTo=&vType=&voucherData='.substr($msg,-16).'&branchID='.$postedData["BRANCH_ID"].'" target="_blank">Print this Voucher</a>'."</td>																
															</tr>
														</table>");
					return $this->redirect()->toRoute('journalentry');
				}
				// General Accounts Entry End BY Akhand
			}
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		
		public function getNDAInvestorListAction() {
			if(!$this->getOperatorTable()->getBusinessDateInfo()){return $this->redirect()->toRoute('login');}		
			$portfolioTypeID = $_REQUEST['portfolioTypeID'];
			if($portfolioTypeID == 0) {
				throw new \Exception("Invalid id");
			} else {
				$ndaInvestorList = $this->getInvestorManagementTable()->getNDAInvestor($portfolioTypeID);				
				$data = array();
				if($ndaInvestorList) {
					foreach($ndaInvestorList as $row) {
						$data[] = array(
										'PORTFOLIO_CODE' => $row->PORTFOLIO_CODE,
										'INVESTOR_NAME' => $row->INVESTOR_NAME
									);
					}
				}
				echo json_encode($data);
				exit;
			}
		}
		public function getSuggestIPANoAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$cond = '';
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getInvestorManagementTable()->fetchIPANo($input,$cond);			
			foreach ($IPAData as $selectOption) {
				$ipaNo = $selectOption['PORTFOLIO_CODE'];
				$mailingAddress = '';
				$investorName = $selectOption['INVESTOR_NAME'];
				$str .= "<div align='left' onClick=\"fill_id_code('".$ipaNo."','".$mailingAddress."','".$investorName."');\"><b>".$ipaNo.'('.$investorName.')'."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		public function getSuggestBankNameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$id	= $_REQUEST['id'];
			$cond = '';
			$str='';
			$investorInfoArray 	= array();
			$BANKData = $this->getMoneyMarketOrganizationTable()->fetchBankNameForSuggestion($input,$cond);			
			foreach ($BANKData as $selectOption) {
				$orgName = $selectOption['ORG_NAME'];
				$str .= "<div align='left' onClick=\"fill_bank_name('".$orgName."','".$id."');\"><b>".$orgName."</b></div>";
			}
			//echo json_encode($str);exit;
			echo $str;exit;
		}
		public function getSuggestBankNameForCIHAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$id	= $_REQUEST['id'];
			$cond = '';
			$str='';
			$investorInfoArray 	= array();
			$BANKData = $this->getMoneyMarketOrganizationTable()->fetchBankNameForSuggestion($input,$cond);			
			foreach ($BANKData as $selectOption) {
				$orgName = $selectOption['ORG_NAME'];
				$str .= "<div align='left' onClick=\"fill_bank_nameCIH('".$orgName."','".$id."');\"><b>".$orgName."</b></div>";
			}
			//echo json_encode($str);exit;
			echo $str;exit;
		}
		public function getSuggestCOAPaymentRceiptAction() {
			$frm = strtolower( $_REQUEST['frm'] );
			$fundCode = strtolower( $_REQUEST['fundCode'] );
			$coa_head = array();
			if($frm=='j') {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (305, 501) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='r') {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (201, 202, 302, 601) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='c') {
				$switch = "SUBSTR(COA_CODE, 0, 3) IN (303,304) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (303) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			}
			/*if($fundCode == 0) {
				throw new \Exception("Invalid id");
			} else {*/
				$COAList = $this->getCoaTable()->getCOAListForPaymentEntry($switch);				
				$data = array();
				if($COAList) {
					foreach($COAList as $row) {
						$data[] = array('id' => $row->COA_CODE,'text' => $row->COA_NAME);
					}
				}
				//echo '<pre>';print_r($data);die();
				echo json_encode($data);
				exit;
			//}
		}
		
		public function getTrialBalPayRecAmountAction() {
			$amount		= 0;
			if($fundCode == 0) {
				throw new \Exception("Invalid id");
			} else {
				$portfolioCOAList 	= $this->getPortfolioCoaTable()->getTrialBalPayReceiptAmount($coaCode,$frm,$fundCode);				
				$data 				= array();
				if($portfolioCOAList) {
					foreach($portfolioCOAList as $row) {
						$data[] = array(
										'AMOUNT' => $row->AMOUNT,
									);
					}
				}
				echo number_format($amount,2);
				//echo json_encode($data);
				exit;
			}			
		}
		
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
		
		public function getMoneyMarketOrganizationTable() {
			if(!$this->moneyMarketOrganizationTable) {
				$sm = $this->getServiceLocator();
				$this->moneyMarketOrganizationTable = $sm->get('GlobalSetting\Model\MoneyMarketOrganizationTable');
			}
			return $this->moneyMarketOrganizationTable;
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
		
		public function getTrialBalanceTable() {
			if(!$this->trialBalanceTable) {
				$sm = $this->getServiceLocator();
				$this->trialBalanceTable = $sm->get('Accounts\Model\TrialBalanceTable');
			}
			return $this->trialBalanceTable;
		}
	}
?>