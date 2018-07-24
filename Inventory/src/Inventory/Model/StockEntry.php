<?php
	namespace Inventory\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class StockEntry implements InputFilterAwareInterface {
		public $BRANCH_ID;
		public $STOCK_DETAILS_ID;
		public $STOCK_ORDER_ID;
		public $CATEGORY_ID;
		public $CATEGORY_NAME;
		public $SUPPLIER_INFO_ID;
		public $QUANTITY;
		public $RQUANTITY;
		public $BUY_PRICE;
		public $SALE_PRICE;
		public $TOTAL_AMOUNT;
		public $AVG_RATE;		
		public $DISCOUNT;
		public $DISCOUNTMODE;
		public $INDVDISCOUNTHIDDEN;
		public $NET_AMOUNT;
		public $STATUS;
		public $RECORD_DATE;
		public $DUE;
		public $NET_PAYMENT;
		public $CAT_PRICE_ID;
		public $NumberOfRows;
		public $ORDER_NO;
		public $INVOICE_NO;
		public $REMARKS;
		
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->BRANCH_ID 			= (!empty($data['BRANCH_ID'])) ? $data['BRANCH_ID'] : null;
			$this->STOCK_DETAILS_ID 	= (!empty($data['STOCK_DETAILS_ID'])) ? $data['STOCK_DETAILS_ID'] : null;
			$this->STOCK_ORDER_ID 		= (!empty($data['STOCK_ORDER_ID'])) ? $data['STOCK_ORDER_ID'] : null;
			$this->CATEGORY_ID 			= (!empty($data['CATEGORY_ID'])) ? $data['CATEGORY_ID'] : null;
			$this->CATEGORY_NAME 		= (!empty($data['CATEGORY_NAME'])) ? $data['CATEGORY_NAME'] : null;
			$this->SUPPLIER_INFO_ID 	= (!empty($data['SUPPLIER_INFO_ID'])) ? $data['SUPPLIER_INFO_ID'] : null;
			$this->QUANTITY 			= (!empty($data['QUANTITY'])) ? $data['QUANTITY'] : null;
			$this->RQUANTITY 			= (!empty($data['RQUANTITY'])) ? $data['RQUANTITY'] : null;
			$this->BUY_PRICE 			= (!empty($data['BUY_PRICE'])) ? $data['BUY_PRICE'] : null;
			$this->SALE_PRICE 			= (!empty($data['SALE_PRICE'])) ? $data['SALE_PRICE'] : null;			
			$this->TOTAL_AMOUNT 		= (!empty($data['TOTAL_AMOUNT'])) ? $data['TOTAL_AMOUNT'] : null;
			$this->AVG_RATE 			= (!empty($data['AVG_RATE'])) ? $data['AVG_RATE'] : null;
			$this->DISCOUNT 			= (!empty($data['DISCOUNT'])) ? $data['DISCOUNT'] : null;
			$this->DISCOUNTMODE 		= (!empty($data['DISCOUNTMODE'])) ? $data['DISCOUNTMODE'] : null;
			$this->INDVDISCOUNTHIDDEN 	= (!empty($data['INDVDISCOUNTHIDDEN'])) ? $data['INDVDISCOUNTHIDDEN'] : null;
			$this->NET_AMOUNT 			= (!empty($data['NET_AMOUNT'])) ? $data['NET_AMOUNT'] : null;
			$this->STATUS 				= (!empty($data['STATUS'])) ? $data['STATUS'] : null;			
			$this->RECORD_DATE 			= (!empty($data['RECORD_DATE'])) ? $data['RECORD_DATE'] : null;			
			$this->NumberOfRows 		= (!empty($data['NumberOfRows'])) ? $data['NumberOfRows'] : null;
			$this->DUE 					= (!empty($data['DUE'])) ? $data['DUE'] : null;
			$this->NET_PAYMENT 			= (!empty($data['NET_PAYMENT'])) ? $data['NET_PAYMENT'] : null;
			$this->CAT_PRICE_ID 		= (!empty($data['CAT_PRICE_ID'])) ? $data['CAT_PRICE_ID'] : null;
			$this->ORDER_NO 			= (!empty($data['ORDER_NO'])) ? $data['ORDER_NO'] : null;
			$this->INVOICE_NO 			= (!empty($data['INVOICE_NO'])) ? $data['INVOICE_NO'] : null;
			$this->REMARKS 				= (!empty($data['REMARKS'])) ? $data['REMARKS'] : null;
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