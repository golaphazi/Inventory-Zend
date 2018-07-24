<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	
	use LocalSetting\Model\Category;
	use LocalSetting\Model\CategoryPrice;
	use GlobalSetting\Model\Coa;
	use LocalSetting\Form\CategoryForm;
	
	use Zend\Paginator\Paginator;
	use Zend\Paginator\Adapter\Iterator as paginatorIterator;
	use Zend\Db\Sql\Select;

	class CategoryController extends AbstractActionController {
		protected $categoryTable;
		protected $CoaTable;
		protected $categoryPriceTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			//$paginator = $this->getCategoryTable()->categoryTableView(true);
			//$paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
			//$paginator->setItemCountPerPage(10);
			
			return new ViewModel(array(
				'categorys' => $this->getCategoryTable()->fetchAll(),
				'categoryTableViews' => $this->getCategoryTable()->categoryTableView(),
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			
			$form = new CategoryForm('category', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			//echo "<pre>"; print_r($this->getRequest()); die();
			if($request->isPost()) {
				$category 	= new Category();
				$form->setInputFilter($category->getInputFilter());
				$form->setData($request->getPost());
				//echo "<pre>"; print_r($request->getPost()); die();
				//if($form->isValid()) {
					$postedData		= $request->getPost();
					$this->getCategoryTable()->transectionStart();
					$category->exchangeArray($request->getPost());
					//echo "<pre>"; print_r($category); die();
					$success = 0;
					//$this->getCategoryTable()->transectionStart();
					if($returnData = $this->getCategoryTable()->saveCategory($category)) {
						if(!empty($returnData['COA_DATA'])){
							//echo "<pre>"; print_r($returnData); die();
							$Coa = new Coa();
							foreach($returnData['COA_DATA']['COA_CODE'] as $index=>$CoaCode) {
								$accountDetailsCoaData = array(
																'COMPANY_ID' 	=> $returnData['COA_DATA']['COMPANY_ID'][$index],
																'PARENT_COA'	=> $returnData['COA_DATA']['PARENT_COA'][$index],
																'COA_NAME' 		=> $returnData['COA_DATA']['COA_NAME'][$index],
																'COA_CODE' 		=> $CoaCode,
																'AUTO_COA' 		=> $returnData['COA_DATA']['AUTO_COA'][$index],
																'CASH_FLOW_HEAD' => $returnData['COA_DATA']['CASH_FLOW_HEAD'][$index],
															);
								$Coa->exchangeArray($accountDetailsCoaData);
								//echo "<pre>"; print_r($Coa); die();
								if($this->getCoaTable()->saveCoa($Coa)){
									$success = 1;
								} else {
									$success = 0;
								}
							}
						} else {
							$success = 1;
						}
						$adddPrice = $request->getPost()->adddPrice;
						if($adddPrice == 'y'){
							$catIdPrice 	= array();
							$catIdPrice 	= array(
								'CATEGORY_ID' 			=> $returnData['CAT_ID'],
								'BUY_PRICE' 			=> $category->BUY_PRICE,
								'SALE_PRICE' 			=> $category->SALE_PRICE,
							);
							$catPriceValue = new CategoryPrice();
							$catPriceValue->exchangeArray($catIdPrice);
							if($this->getCategoryPriceTable()->saveCategoryPrice($catPriceValue)) {
								$success = 1;
							} else {
								$success = 0;
							}
						}
					}
					//echo $success;die(); 
					if($success) {
						$this->getCategoryTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Category submitted properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('category');
					} else {
						$this->getCategoryPriceTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='error_msg'>
																<td colspan='3' style='text-align:center;'><h4>Category couldn't save properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('categoryprice');
					}
					
				//}
			}
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		
		public function deleteAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('category',array('action' => 'Edit'));
			} else {
				$success = 0;
				$this->getCategoryTable()->transectionStart();
				if($this->getCategoryTable()->deleteCategory($id)){
					$success = 1;
				} else {
					$success = 0;
				}
				//echo $success;die();
				if($success) {
					$this->getCategoryTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='valid_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category deleted properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('category');	
				} else {
					$this->getCategoryPriceTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='error_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category couldn't delete properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('category');
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
				return $this->redirect()->toRoute('category',array('action' => 'Edit'));
			}
			
			try {
				$category = $this->getCategoryTable()->getCategoryInfoForEdit($id);
				$allNav = $this->getCategoryTable()->categoryTableView();
				foreach($allNav as $allNavs) {
					$navData[] = array(
								'CATEGORY_ID' 			=> $allNavs->CATEGORY_ID,
								'CDOT' 					=> $allNavs->CDOT,
								'CATEGORY_NAME' 		=> $allNavs->CATEGORY_NAME,
								'LFT' 					=> $allNavs->LFT,
								'ORDER_BY' 				=> $allNavs->ORDER_BY,
								'NODE_DEPTH' 			=> $allNavs->NODE_DEPTH,
							);
				}
				//echo "<pre>"; print_r($category);die();
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('category', array('action' => 'index'));
			}
			$form = new CategoryForm('category', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			//$form->bind($category);
			$form->get('submit')->setAttribute('value','Edit');
			$request = $this->getRequest();
			if($request->isPost()) {
				$category 	= new Category();
				$form->setInputFilter($category->getInputFilter());
				$form->setData($request->getPost());
				$success = 0;
				$this->getCategoryTable()->transectionStart();
				$category->exchangeArray($request->getPost());
				//echo "<pre>"; print_r($category);die();
				if($this->getCategoryTable()->saveEditCategory($category)) {
					$success = 1;
				} else {
					$success = 0;
				}
				//echo $success;die();
				if($success) {
					$this->getCategoryTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='valid_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category updated properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('category');
				} else {
					$this->getCategoryPriceTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='error_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category couldn't update properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('category');
				}
			}
			
			return array(
				'id' 			=> $id,
				'form' 			=> $form,
				'navData' 		=> $navData,
				'category' 		=> $category,
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
		
		public function getCoaTable() {
			if(!$this->CoaTable) {
				$sm = $this->getServiceLocator();
				$this->CoaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->CoaTable;
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