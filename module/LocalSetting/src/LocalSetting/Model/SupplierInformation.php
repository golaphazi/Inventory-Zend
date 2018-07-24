<?php
	namespace LocalSetting\Model;
	
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class SupplierInformation implements InputFilterAwareInterface {
		public $SUPPLIER_INFO_ID;
		public $NAME;
		public $SHORT_NAME;
		public $ADDRESS;
		public $PHONE;
		public $FAX;
		public $MOBILE;
		public $WEB;
		public $EMAIL;
		public $BUSINESS_DATE;
		protected $inputFilter;
		
		public function exchangeArray($data) {
			$this->SUPPLIER_INFO_ID	= (!empty($data['SUPPLIER_INFO_ID'])) ? $data['SUPPLIER_INFO_ID'] : null;
			$this->NAME 			= (!empty($data['NAME'])) ? $data['NAME'] : null;
			$this->SHORT_NAME 		= (!empty($data['SHORT_NAME'])) ? $data['SHORT_NAME'] : null;
			$this->ADDRESS 			= (!empty($data['ADDRESS'])) ? $data['ADDRESS'] : null;
			$this->PHONE 			= (!empty($data['PHONE'])) ? $data['PHONE'] : null;
			$this->FAX 				= (!empty($data['FAX'])) ? $data['FAX'] : null;
			$this->MOBILE 			= (!empty($data['MOBILE'])) ? $data['MOBILE'] : null;
			$this->WEB 				= (!empty($data['WEB'])) ? $data['WEB'] : null;
			$this->EMAIL 			= (!empty($data['EMAIL'])) ? $data['EMAIL'] : null;
			$this->BUSINESS_DATE	= (!empty($data['BUSINESS_DATE'])) ? $data['BUSINESS_DATE'] : null;
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
				
				$inputFilter->add($factory->createInput(array(
					'name' 		=> 'SUPPLIER_INFO_ID',
					'required'	=> true,
					'filters'	=> array(
						array('name' => 'int'),
					),
				)));
				$inputFilter->add($factory->createInput(array(
					'name' => 'NAME',
					'required' => true,
					'filters' => array(
						array('name' => 'StripTags'),
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						array(
						  'name' =>'NotEmpty', 
							'options' => array(
								'messages' => array(
									\Zend\Validator\NotEmpty::IS_EMPTY => 'Supplier Name can not be empty!' 
								),
							),
						),
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 150,
								'messages' => array(
									'stringLengthTooShort' => 'Please enter type!', 
									'stringLengthTooLong' => 'Please enter type!',
								),
							),
						),
					),
				)));
				
				$this->inputFilter = $inputFilter;
				
				return $this->inputFilter;
			}
		}
	}
?>	