<?php
	namespace Inventory\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class RETStockDetails implements InputFilterAwareInterface {
		public $RET_STOCK_DIST_DETAILS_ID;
		public $RET_STOCK_DIST_ID;
		public $CATEGORY_ID;
		public $CAT_PRICE_ID;
		public $RET_QUANTITY;
		public $RET_BUY_PRICE;
		public $RET_SALE_PRICE;
		public $RET_TOTAL_AMOUNT;
    	public $RET_AVG_RATE;    
		public $RET_DISCOUNT;
    	public $RET_NET_AMOUNT;
		public $STATUS;
		public $ORDER_NO;
		public $BRANCH_ID;
		public $INVOICE_NO;
		public $RET_DISCOUNT_RECEIVE;
		public $REMARKS;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->RET_STOCK_DIST_DETAILS_ID 	= (!empty($data['RET_STOCK_DIST_DETAILS_ID'])) ? $data['RET_STOCK_DIST_DETAILS_ID'] : null;
			$this->RET_STOCK_DIST_ID 			= (!empty($data['RET_STOCK_DIST_ID'])) ? $data['RET_STOCK_DIST_ID'] : null;
			$this->CATEGORY_ID 					= (!empty($data['CATEGORY_ID'])) ? $data['CATEGORY_ID'] : null;
			$this->CAT_PRICE_ID 				= (!empty($data['CAT_PRICE_ID'])) ? $data['CAT_PRICE_ID'] : null;
			$this->RET_QUANTITY 				= (!empty($data['RET_QUANTITY'])) ? $data['RET_QUANTITY'] : null;
			$this->RET_BUY_PRICE 				= (!empty($data['RET_BUY_PRICE'])) ? $data['RET_BUY_PRICE'] : null;
			$this->RET_SALE_PRICE 				= (!empty($data['RET_SALE_PRICE'])) ? $data['RET_SALE_PRICE'] : null;
			$this->RET_TOTAL_AMOUNT 			= (!empty($data['RET_TOTAL_AMOUNT'])) ? $data['RET_TOTAL_AMOUNT'] : null;
			$this->RET_AVG_RATE 				= (!empty($data['RET_AVG_RATE'])) ? $data['RET_AVG_RATE'] : null;
			$this->RET_DISCOUNT 				= (!empty($data['RET_DISCOUNT'])) ? $data['RET_DISCOUNT'] : null;
			$this->RET_NET_AMOUNT 				= (!empty($data['RET_NET_AMOUNT'])) ? $data['RET_NET_AMOUNT'] : null;
			$this->STATUS 						= (!empty($data['STATUS'])) ? $data['STATUS'] : null;
			$this->ORDER_NO 					= (!empty($data['ORDER_NO'])) ? $data['ORDER_NO'] : null;
			$this->BRANCH_ID 					= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->INVOICE_NO 					= (!empty($data['INVOICE_NO'])) ? $data['INVOICE_NO'] : null;			
			$this->RET_DISCOUNT_RECEIVE 		= (!empty($data['RET_DISCOUNT_RECEIVE'])) ? $data['RET_DISCOUNT_RECEIVE'] : null;
			$this->REMARKS				 		= (!empty($data['REMARKS'])) ? $data['REMARKS'] : null;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
		
		public function setInputFilter(InputFilterInterface $inputFilter) {
			throw new \Exception("Not used");
		}
		
		public function getInputFilter() {
			if(!$this->inputFilter) {
				$inputFilter = new InputFilter();
				$factory	 = new InputFactory();
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	