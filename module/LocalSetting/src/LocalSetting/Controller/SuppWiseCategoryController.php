<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	
	use LocalSetting\Model\Category;
	use LocalSetting\Model\CategoryPrice;
	use LocalSetting\Model\SuppWiseCategory;
	use GlobalSetting\Model\Coa;
	use LocalSetting\Form\CategoryForm;
	use LocalSetting\Form\SuppWiseCategoryForm;
	use Zend\Session\Container as SessionContainer;
	class SuppWiseCategoryController extends AbstractActionController {
		protected $categoryTable;
		protected $CoaTable;
		protected $categoryPriceTable;
		protected $suppWiseCategoryTable;
		protected $supplierInformationTable;
		
		public function indexAction() {
			//echo 'hi htere';die();
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();			
			$paginator = $this->getSuppWiseCategoryTable()->fetchAll(true);
			$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			$paginator->setItemCountPerPage(10);
			$form = new SuppWiseCategoryForm('suppwisecategory', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			return new ViewModel(array(
				'form' => $form,
				'categories' => $paginator,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$form = new SuppWiseCategoryForm('suppwisecategory', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$request = $this->getRequest();
			$form->get('submit')->setValue('Add');
			if($request->isPost()) {
				$form->setData($request->getPost());
				$postedData  = $request->getPost();
				//echo "<pre>"; print_r($this->getRequest()); die();
				//if($form->isValid()) {
					$this->getSuppWiseCategoryTable()->transectionStart();
					for($i=0;$i<sizeof($postedData['CATEGORY_ID']);$i++) {
						$CATEGORY_ID							= $postedData["CATEGORY_ID"][$i];
						$data['SUPPLIER_INFO_ID']				= $postedData["SUPPLIER_INFO_ID"];
						$data['NAME']							= $postedData["SUPPLIER_INFO_NAME"];
						$data['CATEGORY_ID'] 					= $postedData["CATEGORY_ID"][$i];
						$data['CATEGORY_NAME'] 					= $postedData["CATEGORY_NAME_{$CATEGORY_ID}"];
						$data['IS_SUPPLY'] 						= $postedData["IS_SUPPLY_{$CATEGORY_ID}"];
						$suppwisecategory = new SuppWiseCategory();
						$suppwisecategory->exchangeArray($data);
						if($postedData["IS_SUPPLY_{$CATEGORY_ID}"] == 'yes'){
							if($returnData = $this->getSuppWiseCategoryTable()->saveSuppWiseCategory($suppwisecategory)) {
								$success = 1;
							} else {
								$success = 0;
							}
						}
					}
					//echo $success;die();
					
					if($success) {
						$this->getSuppWiseCategoryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Supplier wise Product added properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('suppwisecategory');
					} else {
						$this->getSuppWiseCategoryTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='error_msg'>
																<td colspan='3' style='text-align:center;'><h4>Supplier wise Product couldn't save properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('suppwisecategory');
					}
					
				//}
			}
			
			//return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
			
			return new ViewModel(array(
				'form' => $form,
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function deleteAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new SuppWiseCategoryForm('suppwisecategory', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$id = (int) $this->params()->fromRoute('id',0);
			$this->getSuppWiseCategoryTable()->transectionStart();
			if(!$id) {
				return $this->redirect()->toRoute('suppwisecategory',array('action' => 'Edit'));
			} else {
				$success = 0;
				//$request = $this->getRequest();
				//if($request->isPost()) {
					//$del = $request->getPost('del','No');					
					//if($del == 'Yes') {
						//$id = (int) $request->getPost('id');										
						if($success = $this->getSuppWiseCategoryTable()->deleteCategoryUnderSupplier($id)){
							$success = 1;
						} else {
							$success = 0;
						}
					//}
				//}
				if($success) {
					$this->getSuppWiseCategoryTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='valid_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category Under Supplier deleted properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('suppwisecategory');	
				} else {
					$this->getSuppWiseCategoryTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='error_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category Under Supplier couldn't delete properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('suppwisecategory');
				}
			}
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('suppwisecategory',array('action' => 'Edit'));
			}
			try {
				$suppWiseCategoryList = $this->getSuppWiseCategoryTable()->getSupplierWiseCategoryTable($id);
				foreach($suppWiseCategoryList as $allNavs) {
					$navData[] = array(
											'NAME' 			=> $allNavs->NAME,
											'CATEGORY_NAME' => $allNavs->CATEGORY_NAME,
											'IS_SUPPLY' 	=> $allNavs->IS_SUPPLY,
											'CATEGORY_ID' 	=> $allNavs->CATEGORY_ID,
											'SUPPLIER_INFO_ID' => $allNavs->SUPPLIER_INFO_ID,
											'SUPP_WISE_CATEGORY_ID' => $allNavs->SUPP_WISE_CATEGORY_ID,
										);
				}
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('suppwisecategory', array('action' => 'index'));
			}
			$form = new CategoryForm('suppwisecategory', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setAttribute('value','Edit');
			$request = $this->getRequest();
			if($request->isPost()) {
				$suppwisecategory 	= new SuppWiseCategory();
				$form->setInputFilter($suppwisecategory->getInputFilter());
				$form->setData($request->getPost());
				$success = 0;
				$this->getSuppWiseCategoryTable()->transectionStart();
				$suppwisecategory->exchangeArray($request->getPost());
				if($success = $this->getSuppWiseCategoryTable()->saveEditSuppWiseCategory($suppwisecategory)) {
					$success = 1;
				} else {
					$success = 0;
				}
				if($success) {
					$this->getSuppWiseCategoryTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='valid_msg'>
															<td colspan='3' style='text-align:center;'><h4>Supplier wise product supply status updated properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('suppwisecategory');
				} else {
					$this->getSuppWiseCategoryTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='error_msg'>
															<td colspan='3' style='text-align:center;'><h4>Supplier wise product supply status couldn't update properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('suppwisecategory');
				}
			}
			
			return array(
				'id' 			=> $id,
				'form' 			=> $form,
				//'navData' 		=> $navData,
				'category' 		=> $navData,
			);
		}
		
		public function getCategoryPriceTable() {
			if(!$this->categoryPriceTable) {
				$sm = $this->getServiceLocator();
				$this->categoryPriceTable = $sm->get('LocalSetting\Model\CategoryPriceTable');
			}
			return $this->categoryPriceTable;
		}
		
		public function getCategoryTable() {
			if(!$this->categoryTable) {
				$sm = $this->getServiceLocator();
				$this->categoryTable = $sm->get('LocalSetting\Model\CategoryTable');
			}
			return $this->categoryTable;
		}
		public function getSupplierInformationTable() {
			if(!$this->supplierInformationTable) {
				$sm = $this->getServiceLocator();
				$this->supplierInformationTable = $sm->get('LocalSetting\Model\SupplierInformationTable');
			}
			return $this->supplierInformationTable;
		}
		
		
		public function getSuppWiseCategoryTable() {
			if(!$this->suppWiseCategoryTable) {
				$sm = $this->getServiceLocator();
				$this->suppWiseCategoryTable = $sm->get('LocalSetting\Model\SuppWiseCategoryTable');
			}
			return $this->suppWiseCategoryTable;
		}
		
		public function getCoaTable() {
			if(!$this->CoaTable) {
				$sm = $this->getServiceLocator();
				$this->CoaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->CoaTable;
		}
		public function getSuggestRefCATNameAction() {
			$input = strtolower($_REQUEST['queryString']);
			//$no = strtolower( $_REQUEST['no'] );
			$str='';
			$investorInfoArray 	= array();
			$IPAData = $this->getCoaTable()->fetchManufecturedProduct($input);			
			foreach ($IPAData as $selectOption) {
				$catID = $selectOption['CATEGORY_ID'];
				$catName = $selectOption['CATEGORY_NAME'];
				$catIDName = $catID.",".$catName;
				$str .= "<div align='left' onClick=\"fill_id('".$catID."','".$catName."');\"><b>".$catName."</b></div>";
			}
			echo json_encode($str);
			//echo $str;
			exit;
		}
		public function getProductInfoAction() {
			$SUPPLIER_INFO_ID = $_REQUEST['SUPPLIER_INFO_ID'];
			$SUPPLIER_INFO_NAME = $_REQUEST['SUPPLIER_INFO_NAME'];
			$counter 		= 1;
			$generateTable 	= "";
			$generateTable .= "<table align='center' border='0' cellpadding='5' cellspacing='1' width='100%' class='tablesorter' style='font-size:100%;'>
									<tr class='oddRow'>
										<th width='70%' align='left' >PRODUCT</th>
										<th width='30%' align='left' >STATUS</th>
									</tr>
									<tbody>
							 ";
				//echo $cond; die();
				$viewInfo  = $this->getSuppWiseCategoryTable()->categoryTableView();
				//echo "<pre>"; print_r($viewInfo); die();
				$sv = 1;
				$vDate 	 			= '';
				$vNumber 			= '';
				$class 				= '';
				$effDate 			= '';
				$deffDebitCredit 	= 0;
				$class = 'oddRow';		
				$placeProduct = '';	
				$style = '';
				$radioCond = '';
				foreach ($viewInfo as $viewInfoData) :
				//echo "<pre>"; print_r($viewInfoData); die();
					$CATEGORY_ID			= $viewInfoData->CATEGORY_ID;
					$CDOT					= $viewInfoData->CDOT;
					$CATEGORY_NAME			= $viewInfoData->CATEGORY_NAME;
					$LFT					= $viewInfoData->LFT;
					$ORDER_BY				= $viewInfoData->ORDER_BY;
					$NODE_DEPTH				= $viewInfoData->NODE_DEPTH;
					$ACTIVE_INACTIVE		= $viewInfoData->ACTIVE_INACTIVE;
					$style 					= ($NODE_DEPTH == 2) ? 'style="font-weight:bold;"' : '';
					$placeProduct			= str_replace('-','&nbsp;',$CDOT);					
					if($class == 'evenRow') {
						$class = 'oddRow';
					} else {
						$class = 'evenRow';
					}
					if(($NODE_DEPTH == '4')){
						$radioCond = "<input type='hidden' name='CATEGORY_ID[]' id='CATEGORY_ID_{$sv}' value='".$CATEGORY_ID."' />
									  <input type='hidden' name='CATEGORY_NAME_{$CATEGORY_ID}' id='CATEGORY_NAME_{$sv}' value='".$CATEGORY_NAME."' />
						<input type='radio' name='IS_SUPPLY_{$CATEGORY_ID}' id='' value='yes'>&nbsp;Yes&nbsp;<input type='radio' name='IS_SUPPLY_{$CATEGORY_ID}' id='' value='no' checked='checked'>&nbsp;No&nbsp;";
					} else {
						$radioCond = "&nbsp;";
					}
					$generateTable .= "<tr class='{$class}'>
										<td valign='top' align='left'>
											".$placeProduct.$CATEGORY_NAME."
										</td>
										<td valign='top' align='left' >
											{$radioCond}
										</td>
									  </tr>
									   ";
				$sv ++;	
				endforeach;
				$generateTable .= "<tr>
									<td valign='top' align='center'>
										<input type='hidden' name='SUPPLIER_INFO_ID' id='SUPPLIER_INFO_ID' value='".$SUPPLIER_INFO_ID."' />
										<input type='hidden' name='SUPPLIER_INFO_NAME' id='SUPPLIER_INFO_NAME' value='".$SUPPLIER_INFO_NAME."' />
										<input type='submit' name='submit' id='submit' value='Done'>&nbsp;<input type='reset' name='reset' id='' value='Reset'></td>
								  </tr>
								   ";
			$generateTable .= "</tbody></table>";
			$finalGenerateTable = '';
			$finalGenerateTable .= $generateTable;
			echo $finalGenerateTable; 
			exit;
		}
		public function getAdvanceSearchAction() {
			$this->session = new SessionContainer('post_supply');
			$portfolioName = '';
			$businessDate = $this->session->businessdate;	
			$CATEGORY_ID 		= $_REQUEST['CATEGORY_ID'];
			$SUPPLIER_INFO_ID 	= $_REQUEST['SUPPLIER_INFO_ID'];
			$statusShow			= '';
			$status 			= $_REQUEST['status'];
			$productName		= $_REQUEST['productName'];
			if($status == 'yes'){
				$statusShow = 'Yes';
			}else if ($status == 'no'){
				$statusShow = 'No';
			} else if ($status == 'all'){
				$statusShow = 'All';
			} else {
				$statusShow = 'No';
			}
			$cond = '';
			if ((!empty($status)) && ($status != 'all')) {
				$cond .= " AND suppcat.IS_SUPPLY = '".$status."'";
			} else {
				$cond .= '';
			}
			if (!empty($CATEGORY_ID)) {
				$cond .= " AND suppcat.CATEGORY_ID = '".$CATEGORY_ID."'";
				$productCond = $productName;
			} else {
				$productCond = 'All Product';
			}
			$suppName = '';
			if (!empty($SUPPLIER_INFO_ID)) {
				$cond .= " AND suppcat.SUPPLIER_INFO_ID ='".$SUPPLIER_INFO_ID."'";
				$suppData  = $this->getSupplierInformationTable()->getSupplierDetails($SUPPLIER_INFO_ID);
				foreach ($suppData as $selectGroupOption) {
						$suppName = $selectGroupOption['NAME'];
				}
			} else {
				$cond .= '';
				$suppName = 'All Supplier';
			}
			$table = '';
			$table .= "<table border='0' cellpadding='5' cellspacing='5' width='100%'>";
			$table .= "<tr class=''>
								<td valign='top' colspan='1'> 
										<table border='0' cellpadding='5' cellspacing='5' width='100%' style='font-size:85%;border:1px dotted #888;font-family:Tahoma, Geneva, sans-serif;'>
											<tr style='border:1px dotted #888;font-size:120%;'>
												<td valign='top' colspan='3' align='left'>
													&nbsp;
												</td>
											</tr>
											<tr style='border:1px dotted #888;font-size:120%;'>
												<td valign='top' colspan='3' align='left'>
													Search Result: Supplier - ".$suppName.", Product - ".$productCond."
												</td>
											</tr>
										</table>
								 </td>
						</tr>
						</table>";
			$instrumentCategoryName = '';
			$instrumentCategoryId = '';
			$cond1 = '';
			$serialNo = 0;
			$instrumentCategory  = $this->getSuppWiseCategoryTable()->getDistinctSuppInfo();
			$foundFlag = 0;
			$totalFound = 0;
			foreach($instrumentCategory as $instrumentCategoryData) {
				$instrumentCategoryName  = $instrumentCategoryData["NAME"];
				$instrumentCategoryId  	 = $instrumentCategoryData["SUPPLIER_INFO_ID"];	
				if (!empty($instrumentCategoryId)) {
					$cond1 = " AND suppcat.SUPPLIER_INFO_ID = '".$instrumentCategoryId."'";	
				}
				$instrumentDataDetails = array();
				$instrumentDataDetails  = $this->getSuppWiseCategoryTable()->fetchSuppWiseProductStatus($cond,$cond1);	
				
				if(sizeof($instrumentDataDetails) > 0) {
					$foundFlag = 1;
					$table .= "<fieldset style='width:97%;border:0px;border-top:1px solid #2b2d93;'>
								<legend style='border:0px;'>Supplier:&nbsp;".$instrumentCategoryName."</legend>";
									$table .= "
											  <table border='0' cellpadding='5' cellspacing='10' width='100%' style='font-size:85%;border:1px dotted #888;font-family:Tahoma, Geneva, sans-serif;'>
												<tr valign='top' height='35px;' style='font-weight:bold;'>
													<td align='left' width='5%'  style='border-bottom:1px dotted #888;'>Sl</td>
													<td  align='left' width='30%' style='border-bottom:1px dotted #888;'>Product</td>
													<td  align='left' width='20%' style='border-bottom:1px dotted #888;'>Supplier</td>
													<td  align='center' width='15%' style='border-bottom:1px dotted #888;'>Supply Start At</td>
													<td  align='center' width='15%' style='border-bottom:1px dotted #888;'>Supply Ends At</td>
													<td  align='center' width='10%' style='border-bottom:1px dotted #888;'>Is Supply ?</td>
													<td  align='center' width='5%' style='border-bottom:1px dotted #888;'>Action</td>
												</tr>
											  ";
												$serialNo = 1;
												$StatusLNL = '';
												$mnmStatus = '';
												$lnlStatus = '';
												$totalRowsReturn = 0;
												$endDateStatus = '';
												$actionStatus = '';
												if(sizeof($instrumentDataDetails) > 0) {
													for($i = 0;$i<sizeof($instrumentDataDetails['SUPP_WISE_CATEGORY_ID']);$i++){
														if($i%2==0) {
															$class="evenRow";
														}
														else {
															$class="oddRow";
														}
														if($instrumentDataDetails['IS_SUPPLY'][$i] == 'yes') {
															$mnmStatus = 'Yes';
														}
														else {
															$mnmStatus = 'No';
														}
														$endDateStatus = $instrumentDataDetails['END_DATE'][$i];
														if($endDateStatus == '0000-00-00'){
															$endDateStatus = 'Continue';
															$actionStatus = "<a onclick='if(confirm(\"Are you sure you want to edit information ?\")){return true;} else {return false;};' href='/suppwisecategory/edit/".$instrumentDataDetails['SUPP_WISE_CATEGORY_ID'][$i]."' target='_blank'>Edit</a>";
														} else {
															$endDateStatus = date('d-m-Y',strtotime($endDateStatus));
															$actionStatus = "#";
														}
														$table .= "
															<tr valign='top' class='{$class}'>
																<td valign='top'  align='center'>".$serialNo.'.&nbsp;'."</td>
																<td  align='left'>".ucwords($instrumentDataDetails['CATEGORY_NAME'][$i])."</td>
																<td align='left'>".$instrumentDataDetails['NAME'][$i]."</td>
																<td align='center'>".$instrumentDataDetails['START_DATE'][$i]."</td>
																<td align='center'>".$endDateStatus."</td>
																<td align='center'>{$mnmStatus}</td>
																<td align='center'>																	
																	{$actionStatus}
																</td>
															</tr>";
														$serialNo++;
													}
												} else {
													$table .= "
															<tr valign='top' class='errorMsg'>
																<td valign='top' colspan='7'  align='center'>Not Found</td>
															</tr>";
												}
												
									$table .= "</table>";
					$table .= "</fieldset>";
				}
				$totalFound += $serialNo - 1;
			}
			if($foundFlag == 0){
				$table .= "
							  <table border='0' cellpadding='5' cellspacing='10' width='100%' style='font-size:85%;border:1px dotted #888;'>
								<tr valign='top' height='35px;' style='font-weight:bold;'>
									<td align='left' width='5%'  style='border-bottom:1px dotted #888;'>Sl</td>
									<td  align='left' width='30%' style='border-bottom:1px dotted #888;'>Product</td>
									<td  align='left' width='20%' style='border-bottom:1px dotted #888;'>Supplier</td>
									<td  align='center' width='15%' style='border-bottom:1px dotted #888;'>Supply Start At</td>
									<td  align='center' width='15%' style='border-bottom:1px dotted #888;'>Supply End At</td>
									<td  align='center' width='10%' style='border-bottom:1px dotted #888;'>Supply Status</td>
									<td  align='center' width='5%' style='border-bottom:1px dotted #888;'>Action</td>
								</tr>
								<tr valign='top' height='35px;' style='font-weight:bold;' class='errorMsg' >
									<td  align='center' colspan='7'  style='border-bottom:1px dotted #888;'>Not Found</td>
								</tr>
							  ";
			}
			//echo $table;die();
			echo json_encode($table);exit;
		}
		public function getCategoryOrderAction() {
			$id 	= (int) $this->params()->fromQuery('id',0);
			$data 	= array();
			if(($id == 0)) {
				throw new \Exception("Invalid id");
			} else {
				$maxOrderNumberData = $this->getCategoryTable()->getCategoryOrder($id);
				$mon	= 0;
				foreach($maxOrderNumberData as $maxOrderNumber) {
					$mon = $maxOrderNumber->NODE_DEPTH;
				}
				
				$maxNodData = $this->getCategoryTable()->categoryNodeDepth($id);
				$nod	= 0;
				foreach($maxNodData as $maxNodDatas) {
					$nod 		= $maxNodDatas->NODE_DEPTH;
					$coaCode 	= $maxNodDatas->COA_CODE;
					$catName 	= $maxNodDatas->CATEGORY_NAME;
				}
				
				$NODE_DEPTH = array('NODE_DEPTH' => $nod, 'ORDER_BY' => $mon, 'COA_CODE' => $coaCode, 'CATEGORY_NAME' => $catName);
				//echo print_r($data); die();
				if(empty($NODE_DEPTH)){
					echo json_encode($NODE_DEPTH);
					exit;
				}else{
					echo json_encode($NODE_DEPTH);
					exit;	
				}
			}
		}
	}
?>