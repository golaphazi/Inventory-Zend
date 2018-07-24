<?php
	namespace Inventory\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Inventory\Form\PurchaseEntryEditForm;
	use Report\Model\StockReport;
	
	use Zend\Session\Container as SessionContainer;
	
	class PurchaseEntryEditController extends AbstractActionController {		
		protected $dbAdapter;
		protected $stockReport;
				
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Inventory',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$request = $this->getRequest();
			$form = new PurchaseEntryEditForm('purchaseentryedit', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), ($request->isPost()) ? $request->getPost() : array() );
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$investormanagement = new InvestorManagement();
				$form->setInputFilter($investormanagement->getInputFilter());
				$form->setData($request->getPost());
			}			
			return array('form' => $form);
		}
		
		public function getStockReportAction() {
			date_default_timezone_set("Asia/Dhaka");
			$current_date = date("d-m-Y H:i:s");
			//echo 'aaa';die();
			$parentId 			= $_REQUEST['branch_id'];
			$printView	 		= $_REQUEST['printView'];
			
			$tranDateFrom 		= $_REQUEST['tranDateFrom'];
			$tranDateTo	 		= $_REQUEST['tranDateTo'];
			
			$listOfProduct	 	= $_REQUEST['listOfProduct'];
			$SUPPLIER_ID	 	= $_REQUEST['SUPPLIER_ID'];
			//echo $listOfProduct;die();
			$branchData  = $this->getStockReportTable()->getStockReport($parentId,$printView,$tranDateFrom,$tranDateTo,$listOfProduct,$SUPPLIER_ID);
			//echo "<pre>"; print_r($branchData);die();
			echo $branchData; 
			exit;
		}
		public function getInvoiceDetailsAction() {
			date_default_timezone_set("Asia/Dhaka");
			$current_date = date("d-m-Y H:i:s");
			$invoiceType 		= $_REQUEST['invoiceType'];
			$printView	 		= $_REQUEST['printView'];
			$tranDateFrom 		= $_REQUEST['tranDateFrom'];
			$tranDateTo	 		= $_REQUEST['tranDateTo'];
			$invoiceNo	 		= $_REQUEST['orderNo'];
			$SUPPLIER_ID	 	= $_REQUEST['SUPPLIER_ID'];
			$RETAILER_ID	 	= $_REQUEST['RETAILER_ID'];
			$parentTableID		= $_REQUEST['parentTableID'];			
			$branchData  = $this->getStockReportTable()->getInvoiceDetails($parentTableID,$printView,$tranDateFrom,$tranDateTo,$invoiceType,$SUPPLIER_ID,$RETAILER_ID,$invoiceNo);
			//echo "<pre>"; print_r($branchData);die();
			//echo json_encode($branchData);
			echo $branchData; 
			exit;
		}
		
		
		
		
		public function getSuggestRefCOANameAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$invoiceType = strtolower( $_REQUEST['invoiceType'] );
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getStockReportTable()->fetchInvoiceNumber($input, $invoiceType);
			foreach ($IPAData as $selectOption) {
				$orderID 		= $selectOption['SOID'];
				$OrderNO 		= $selectOption['ORDER_NO'];
				$INVOICE_NO 	= $selectOption['INVOICE_NO'];
				$orderIDNo 		= $orderID.",".$INVOICE_NO;
				$str .= "<div align='left' onClick=\"fill_id('".$orderIDNo."');\"><b>".$INVOICE_NO."</b></div>";
			}
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		public function getStockReportTable() {
			
			if(!$this->stockReport) {
				$sm = $this->getServiceLocator();
				$this->stockReport = $sm->get('Report\Model\StockReportTable');
			}
			return $this->stockReport;
		}
	}
?>