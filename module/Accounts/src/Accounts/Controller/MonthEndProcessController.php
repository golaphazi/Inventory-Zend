<?php
	namespace Accounts\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Accounts\Form\MonthEndProcessForm;
	
	use Accounts\Model\Voucher;
	
	use Zend\Session\Container as SessionContainer;
	
	class MonthEndProcessController extends AbstractActionController {
		protected $dbAdapter;
		protected $coaTable;
		protected $investorManagementTable;
		protected $voucherTable;
		protected $investorFundTable;
		protected $companyTable;
		
		public function indexAction() {
			$userInfo 						= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID						= $userInfo->id;
			
			$this->layout()->leftMenu 		= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Accounts',$USER_ID);
			$this->layout()->controller 	= $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request 	= $this->getRequest();
			$form 		= new MonthEndProcessForm('monthendprocess', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			
			$form->get('submit')->setValue('Add');
			
			if($request->isPost()) {
				$form->setData($request->getPost());
				$postedData 			= $request->getPost();
				//echo "<pre>"; print_r($postedData); die();
				$msg = '';
				$voucher 		= new Voucher();
				$voucherData	= array();
				
				for($i = 1; $i < $postedData->NUMBER_OF_ROWS; $i++) {
					$cOACodeData	= $postedData["COA_CODE{$i}"];
					if(!empty($cOACodeData)) {
						$j	= $i-1;
						$voucherData['COA_CODE'][$j]					= $postedData["COA_CODE{$i}"];
						$voucherData['VOUCHER_TYPE'][$cOACodeData] 		= $postedData["VOUCHER_TYPE{$i}"];
						$voucherData['PAYMENT_AMOUNT'][$cOACodeData] 	= $postedData["PAYMENT_AMOUNT{$i}"];	
					}
				}
				
				$voucherData['BRANCH_ID'] 								= $postedData["BRANCH_ID"];
				$voucherData['TRANSACTION_DATE']						= $postedData["TRANSACTION_DATE"];
				$voucherData['PARTICULARS'] 							= $postedData["PARTICULARS"];
				$voucherData['EFFECTED_AT_BANK'] 						= $postedData["EFFECTED_AT_BANK"];
				$voucherData['AUTO_TRANSACTION'] 						= $postedData["AUTO_TRANSACTION"];
				$voucherData['DRAWNON'] 								= $postedData["drawnOn"];
				$voucherData['CHEQUE_NO'] 								= $postedData["chequeNo"];
				$voucherData['CHEQUE_DATE'] 							= $postedData["chq_date"];
				
				$voucherData['CB_CODE'] 								= $postedData["cb_code"];
				$voucherData['INVOICE_NO'] 								= $postedData["INVOICE_NO"];
				$voucherData['MONEY_RECEIPT_NO'] 						= $postedData["MONEY_RECEIPT_NO"];
				
				
				$voucher->exchangeArray($voucherData);
				//echo 'asdfasdfasd';die();
				//echo "<pre>"; print_r($voucher); die();
				if($msg = $this->getVoucherTable()->insertPaymentEntry($voucher)) {
					$success = true;
				} else {
					$success = false;	
				}
				
				if($success) {
					$this->getInvestorFundTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:100%;'>
															<tr class='valid_msg'>
																<td width='100%' style='text-align:center;'>{$msg}</td>
															</tr>
														</table>");
					return $this->redirect()->toRoute('paymententry');
				} else {
					$this->getInvestorFundTable()->transectionInterrupted();
					throw new \Exception("Payment entry couldn't save properly!");
				}
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
		public function getSuggestCOAPaymentRceiptAction() {
			$frm = strtolower( $_REQUEST['frm'] );
			$fundCode = strtolower($_REQUEST['fundCode']);
			$coa_head = array();
			if($frm=='p') {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (305, 501) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='r') {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (201, 202, 302, 601) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else if($frm=='c') {
				$switch = "SUBSTR(COA_CODE, 0, 3) IN (303,304) AND gs_coa.COMPANY_ID = '".$fundCode."'";
			} else {
				$switch = "SUBSTR(COA_CODE, 0, 3) NOT IN (303,304) AND gs_coa.COMPANY_ID = '".$fundCode."'";
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
				//echo $data;
				exit;
			//}
		}
		
		public function getTrialBalPayRecAmountAction() {
			$amount		= 0;
			$coaCode	= $_REQUEST['coaCode'];
			$frm		= $_REQUEST['frm'];
			$fundCode	= '';
			if($fundCode == 0) {
				throw new \Exception("Invalid id");
			} else {
				$portfolioCOAList 	= $this->getCoaTable()->getTrialBalPayReceiptAmount($coaCode,$frm,$fundCode);				
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
		public function getCompanyListAction() {
			echo $companyData 	= $this->getCompanyTable()->fetchAll();
			$selectOption 	= array();
			$selectData 	= array();
			foreach ($companyData as $selectOption) {
				$selectData[] = array(
								'COMPANY_ID' => $selectOption->COMPANY_ID,
								'COMPANY_NAME' => $selectOption->COMPANY_NAME,
							);
			}
			//echo "<pre>";print_r($selectData);die();
			echo json_encode($selectData);
			//echo $selectData;
		}
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
		
		public function getInvestorManagementTable() {
			if(!$this->investorManagementTable) {
				$sm = $this->getServiceLocator();
				$this->investorManagementTable = $sm->get('InvestorService\Model\InvestorManagementTable');
			}
			return $this->investorManagementTable;
		}
		
		public function getVoucherTable() {
			if(!$this->voucherTable) {
				$sm = $this->getServiceLocator();
				$this->voucherTable = $sm->get('Accounts\Model\VoucherTable');
			}
			return $this->voucherTable;
		}
		
		public function getInvestorFundTable() {
			if(!$this->investorFundTable) {
				$sm = $this->getServiceLocator();
				$this->investorFundTable = $sm->get('InvestorService\Model\InvestorFundTable');
			}
			return $this->investorFundTable;
		}
		public function getCompanyTable() {
			if(!$this->companyTable) {
				$sm = $this->getServiceLocator();
				$this->companyTable = $sm->get('Company\Model\CompanyTable');
			}
			return $this->companyTable;
		}
	}
?>