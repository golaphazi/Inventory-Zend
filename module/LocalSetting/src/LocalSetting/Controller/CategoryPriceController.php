<?php
	namespace LocalSetting\Controller;
	
	use Zend\Mvc\Controller\AbstractActionController;
	use Zend\View\Model\ViewModel;
	use LocalSetting\Model\CategoryPrice;
	use LocalSetting\Form\CategoryPriceForm;
	use Zend\Session\Container as SessionContainer;

	class CategoryPriceController extends AbstractActionController {
		protected $categoryPriceTable;
		protected $coaTable;
		
		public function indexAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			return new ViewModel(array(
				'controls' => $this->getCategoryPriceTable()->fetchAll(),
				'flashMessages' => $this->flashMessenger()->getMessages(),
			));
		}
		
		public function addAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$form = new CategoryPriceForm();
			$form->get('submit')->setValue('Add');
			
			$request = $this->getRequest();
			if($request->isPost()) {
				$categoryPrice = new CategoryPrice();
				$form->setInputFilter($categoryPrice->getInputFilter());
				$form->setData($request->getPost());
				if($form->isValid()) {
					$this->getCategoryPriceTable()->transectionStart();
					$categoryPrice->exchangeArray($request->getPost());
					$postedData 					= $request->getPost();
					$catPriceData = array();
					for($i=0;$i<sizeof($postedData['CATEGORY_ID']);$i++){
						if(!empty($postedData["CATEGORY_ID"][$i])){
							$catPriceData['CATEGORY_ID'][] 				= $postedData["CATEGORY_ID"][$i];
						 	$catPriceData['BUY_PRICE'][] 				= $postedData["buyPrice{$i}"];
							$catPriceData['SALE_PRICE'][] 				= $postedData["salePrice{$i}"];
						}
					}
					$catPData = array();
					for($i=0;$i<sizeof($catPriceData['CATEGORY_ID']);$i++){
						$catPData 	= array(
									'CATEGORY_ID' 					=> $catPriceData['CATEGORY_ID'][$i],
									'BUY_PRICE' 					=> $catPriceData['BUY_PRICE'][$i],
									'SALE_PRICE' 					=> $catPriceData['SALE_PRICE'][$i],
									);	
						
						$catPrData = new CategoryPrice();
						$catPrData->exchangeArray($catPData);	
						//echo "<pre>"; print_r($catPrData);die();	
						if($this->getCategoryPriceTable()->saveCategoryPrice($catPrData)) {
							$status = 1;
						} else {
							$success = 0;
						}
					}
					//echo $status;die();
					if($status) {
						$this->getCategoryPriceTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Category Price submitted properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('categoryprice');
					} else {
						$this->getCategoryPriceTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='error_msg'>
																<td colspan='3' style='text-align:center;'><h4>Category Price couldn't save properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('categoryprice');
					}
				}
			}
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
		
		public function deleteAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			//echo $id;die();
			$success	= 0;
			if($id == 0) {
				throw new \Exception("Invalid id");
			} else {
				$this->getCategoryPriceTable()->transectionStart();
				if($this->getCategoryPriceTable()->deleteCategoryPrice($id)){
					$success = 1;
				} else {
					$success = 0;
				}
				//echo $success;die();
				if($success) {
					$this->getCategoryPriceTable()->transectionEnd();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='valid_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category Price deleted properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('categoryprice');
				} else {
					$this->getCategoryPriceTable()->transectionInterrupted();
					$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
														<tr class='error_msg'>
															<td colspan='3' style='text-align:center;'><h4>Category Price couldn't deleted properly!</h4></td>
														</tr>
													</table>");
					return $this->redirect()->toRoute('categoryprice');
				}
			}
			return array('form' => $form,'flashMessages' => $this->flashMessenger()->getMessages());
		}
	    
		public function getSuggestCatIdAction() {
			$input = strtolower( $_REQUEST['queryString'] );
			$no = strtolower( $_REQUEST['no'] );
			if(!empty($input)){
				$IPAData = $this->getCategoryPriceTable()->fetchCategoryModelName($input);			
				$str = '';
				$coaCode = '';
				foreach ($IPAData as $selectOption) {
					$coaCode = $selectOption['CATEGORY_ID'];
					
				}
				if(strlen($coaCode)>0){
					$str = $coaCode;
				}else{
					$str = 0;
				}
				
			}else{
				$str='';
				
				$IPAData = $this->getCategoryPriceTable()->fetchCategoryModelName($input='');			
				$i=0;
				foreach ($IPAData as $selectOption) {
					$coaCode = $selectOption['CATEGORY_ID'];
					$coaHead = $selectOption['CATEGORY_NAME'];
					$coaCodeHead = $coaCode.",".$coaHead;
					$str .= "<option value='".$coaHead."' onclick=\"fill_id('".$coaCodeHead."','".$i."');\"></option>";
				 $i++;
				}
			
			}
			
			echo $str;
			exit;
		}
		/*
		public function getSuggestCatIdAction() {
			$input  = strtolower( $_REQUEST['queryString'] );
			$no 	= strtolower( $_REQUEST['no'] );
			$str='';
			//$investorInfoArray 	= array();
			$IPAData = $this->getCategoryPriceTable()->fetchCategoryModelName($input);			
			$i=0;
			//$str .='<datalist id="CATEGORYL'.$no.'">';
			foreach ($IPAData as $selectOption) {
				$coaCode = $selectOption['CATEGORY_ID'];
				$coaHead = $selectOption['CATEGORY_NAME'];
				$coaCodeHead = $coaCode.",".$coaHead;
				//$str .= "<div align='left' onClick=\"fill_id('".$coaCodeHead."','".$no."');\"><b>".$coaHead."</b></div>";
				$str .= "<option value='".$coaHead."' onClick=\"fill_id('".$coaCodeHead."','".$no."');\"></option>";
			 $i++;
			}
			//$str .='</datalist>';
			//echo json_encode($investorInfoArray);
			echo $str;
			exit;
		}
		*/
		
		public function editAction() {
			$userInfo 					= \Zend\Json\Json::decode($this->getServiceLocator()->get('IdentityManager')->hasIdentity());
			$USER_ID					= $userInfo->id;
			
			$this->layout()->leftMenu 	= $this->getServiceLocator()->get('SystemNavTable')->getSubModules('Local Setting',$USER_ID);
			
			$this->layout()->controller = $this->getServiceLocator()->get('SystemNavTable')->getControllers();
			$id = (int) $this->params()->fromRoute('id',0);
			if(!$id) {
				return $this->redirect()->toRoute('categoryprice',array('action' => 'add'));
			}
			
			try {
				$categoryName 		= $this->getCategoryPriceTable()->getCatName();
				$categoryPrice 		= $this->getCategoryPriceTable()->getCategoryPrice($id);
				$catPriceData = array();
				foreach ($categoryName as $categorys) {
					$catPriceData['CATEGORY_ID'][] 				= $categorys['CATEGORY_ID'];
					$catPriceData['CAT_NAME'][] 				= $categorys['CONTROLLER_DOT'].$categorys['CATEGORY_NAME'];
				}
				//echo "<pre>"; print_r($categoryPrice);die();
			} catch(\Exception $ex) {
				return $this->redirect()->toRoute('categoryprice', array('action' => 'index'));
			}
			$form = new CategoryPriceForm('categoryprice', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
			//$form->bind($categoryprice);
			$form->get('submit')->setAttribute('value','Edit');
			$request = $this->getRequest();
			if($request->isPost()) {
				//echo "<pre>"; print_r($request->getPost());die();
				$form->setData($request->getPost());
				if($form->isValid()) {
					//echo "<pre>"; print_r($request->getPost());die();
					$categoryP = new CategoryPrice();
					$this->getCategoryPriceTable()->transectionStart();
					$categoryP->exchangeArray($request->getPost());	
					//echo "<pre>"; print_r($catPrData);die();	
					if($this->getCategoryPriceTable()->saveCategoryPrice($categoryP)) {
						$status = 1;
					} else {
						$success = 0;
					}
					
					//echo $status;die();
					if($status) {
						$this->getCategoryPriceTable()->transectionEnd();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='valid_msg'>
																<td colspan='3' style='text-align:center;'><h4>Category Price Updated properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('categoryprice');
					} else {
						$this->getCategoryPriceTable()->transectionInterrupted();
						$this->flashmessenger()->addMessage("<table align='center' cellpadding='2' cellspacing='2' border='0' width='100%' style='font-size:85%;'>
															<tr class='error_msg'>
																<td colspan='3' style='text-align:center;'><h4>Category Price couldn't update properly!</h4></td>
															</tr>
														</table>");
						return $this->redirect()->toRoute('categoryprice');
					}
				}
			}
			
			return array(
				'id' 					=> $id,
				'form' 					=> $form,
				'catPriceData' 			=> $catPriceData,
				'categoryPrice' 		=> $categoryPrice,
			);
		}
		
		public function getCategoryPriceTable() {
			if(!$this->categoryPriceTable) {
				$sm = $this->getServiceLocator();
				$this->categoryPriceTable = $sm->get('LocalSetting\Model\CategoryPriceTable');
			}
			return $this->categoryPriceTable;
		}
		
		public function getCoaTable() {
			if(!$this->coaTable) {
				$sm = $this->getServiceLocator();
				$this->coaTable = $sm->get('GlobalSetting\Model\CoaTable');
			}
			return $this->coaTable;
		}
	}
?>