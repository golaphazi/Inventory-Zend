<?php
	namespace LocalSetting\Model;
		
	use Zend\InputFilter\Factory as InputFactory;
	use Zend\InputFilter\InputFilter;
	use Zend\InputFilter\InputFilterAwareInterface;
	use Zend\InputFilter\InputFilterInterface;
	
	class Category implements InputFilterAwareInterface {
		public $CATEGORY_ID;
		public $PARENT_CATEGORY;
		public $CATEGORY_NAME;
		public $ORDER_BY;
		public $LFT;
		public $RGT;
		public $DESCRIPTION;
		public $BUY_PRICE;
		public $SALE_PRICE;
		public $NODEDEPTH;
		public $LAST_LEVEL;
		public $P_COA_CODE;
		public $P_CAT_NAME;
		public $P_CODE;
		public $UNIT_CAL_IN;
		public $P_IMAGE;
		public $NODE_DEPTH;
		public $CDOT;
		public $UNDER_MODEL; 
		protected $inputFilter;
		protected $imgExt = array("0"=>"jpeg","1"=>"JPEG","2"=>"jpg","3"=>"JPG","4"=>"gif","5"=>"GIF","6"=>"png","7"=>"PNG");
		protected $fileExt = array("0"=>"csv","1"=>"CSV","2"=>"pdf","3"=>"PDF","4"=>"doc","5"=>"DOC","6"=>"docx","7"=>"DOCX","8"=>"jpeg","9"=>"JPEG","10"=>"jpg","11"=>"JPG","12"=>"gif","13"=>"GIF","14"=>"png","15"=>"PNG");
		function getFileExtension($strr) 
		{
			$i = strrpos($strr,".");
			if (!$i) { return ""; }
		
			$l = strlen($strr) - $i;
			$ext = substr($strr,$i+1,$l);
			return $ext;
		}
		function uploadFile($fieldName, $path)
		{
			if(move_uploaded_file($_FILES[$fieldName]['tmp_name'], $path))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		public function exchangeArray($data) {
			$this->CATEGORY_ID 			= (!empty($data['CATEGORY_ID'])) ? $data['CATEGORY_ID'] : null;
			$this->PARENT_CATEGORY 		= (!empty($data['PARENT_CATEGORY'])) ? $data['PARENT_CATEGORY'] : null;
			$this->CATEGORY_NAME 		= (!empty($data['CATEGORY_NAME'])) ? $data['CATEGORY_NAME'] : null;
			$this->ORDER_BY 			= (!empty($data['ORDER_BY'])) ? $data['ORDER_BY'] : null;
			$this->LFT 					= (!empty($data['LFT'])) ? $data['LFT'] : null;
			$this->RGT 					= (!empty($data['RGT'])) ? $data['RGT'] : null;
			$this->DESCRIPTION 			= (!empty($data['DESCRIPTION'])) ? $data['DESCRIPTION'] : null;
			$this->BUY_PRICE 			= (!empty($data['BUY_PRICE'])) ? $data['BUY_PRICE'] : null;
			$this->SALE_PRICE 			= (!empty($data['SALE_PRICE'])) ? $data['SALE_PRICE'] : null;
			$this->NODEDEPTH 			= (!empty($data['NODEDEPTH'])) ? $data['NODEDEPTH'] : null;
			$this->LAST_LEVEL 			= (!empty($data['LAST_LEVEL'])) ? $data['LAST_LEVEL'] : null;
			$this->P_COA_CODE 			= (!empty($data['P_COA_CODE'])) ? $data['P_COA_CODE'] : null;
			$this->P_CAT_NAME 			= (!empty($data['P_CAT_NAME'])) ? $data['P_CAT_NAME'] : null;
			
			$this->P_CODE 				= (!empty($data['P_CODE'])) ? $data['P_CODE'] : null;
			$this->UNIT_CAL_IN 			= (!empty($data['UNIT_CAL_IN'])) ? $data['UNIT_CAL_IN'] : null;
			$this->NODE_DEPTH 			= (!empty($data['NODE_DEPTH'])) ? $data['NODE_DEPTH'] : null;
			$this->CDOT 				= (!empty($data['CDOT'])) ? $data['CDOT'] : null;
			$this->UNDER_MODEL 			= (!empty($data['UNDER_MODEL'])) ? $data['UNDER_MODEL'] : null;
			
			
			$p_photo = '';
			if(isset($_FILES['P_IMAGE']['name'])){
				if(strlen($_FILES['P_IMAGE']['name'])>0) {
					$ext_empphoto= $this->getFileExtension($_FILES['P_IMAGE']['name']);	
					if(in_array($ext_empphoto,$this->fileExt)) {
						$mkempphotoname = 'Product_Photo_'.$data['CATEGORY_NAME'];//.'_'.$mkempphotoname;
						$path_empphoto = '/uploaddir/product/'.$mkempphotoname.".".$ext_empphoto;
						$uploaded_cv = $this->uploadFile("P_IMAGE",'public'.$path_empphoto);
						if($uploaded_cv) {
							$p_photo = $path_empphoto;
						} else {
							$p_photo = "/img/no_image.png";
						}
					} else {
						$p_photo = "/img/no_image.png";
					}
				} else {
					$p_photo = "/img/no_image.png";
				}
			}
			$this->P_IMAGE = $p_photo;
		}
		
		public function getArrayCopy() {
			return get_object_vars($this);
		}
		
		public function setInputFilter(InputFilterInterface $inputFilter) {
			throw new \Exception("Not used");
		}
		
		public function getInputFilter() {
			//echo 'hiii';die();
			if(!$this->inputFilter) {
				$inputFilter = new InputFilter();
				$factory	 = new InputFactory();
				
				$this->inputFilter = $inputFilter;
				return $this->inputFilter;
			}
		}
	}
?>	