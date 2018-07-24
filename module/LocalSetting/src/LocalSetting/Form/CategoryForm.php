<?php
	namespace LocalSetting\Form;

	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class CategoryForm extends Form {
		protected $categoryTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct($name);
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return AddCatagoryItem');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'CATEGORY_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'PARENT_CATEGORY',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'PARENT_CATEGORY',
					'class'=>'FormSelectTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					//'label' => 'Controller : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getSystemCategorySelect(),
				),
			));
			$this->add(array(
				'name' => 'CATEGORY_NAME',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'CATEGORY_NAME',
					'class'=>'FormTextTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'input catgory/product name here'
				),
				'options' => array(
					//'label' => 'Sub Controller : ',
				),
			));
			$this->add(array(
				'name' => 'BUY_PRICE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'BUY_PRICE',
					'class'=>'FormTextTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'buy price in BDT'
				),
			));
			$this->add(array(
				'name' => 'SALE_PRICE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'SALE_PRICE',
					'class'=>'FormTextTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'sale price in BDT'
				),
			));
			$this->add(array(
				'name' => 'DESCRIPTION',
				'type' => 'textarea',
				'attributes' => array(
					'id' => 'DESCRIPTION',
					'class'=>'FormTextTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'product description here if available'
				),
			));
			$this->add(array(
				'name' => 'ORDER_BY',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'ORDER_BY',
					'class'=>'FormTextTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'readonly'=>'readonly',
				),
			));
			$this->add(array(
				'name' => 'P_IMAGE',
				'type' => 'file',
				'attributes' => array(
					'class' => 'FormNumericTypeInput', 
					'id' =>'principal_app_photo',
					'onchange' => 'return imageUpload(this.id,"fileDisplayAreaAPP");',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:110%;',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
		
		public function getSystemCategorySelect() {
			$systemNavSql = "
								SELECT 
										C.CATEGORY_ID, 
										rpad(' ',COUNT(C.CATEGORY_NAME)*5,'-')  AS CONTROLLER_DOT, 
										C.CATEGORY_NAME,
										C.LFT
								FROM 
										ls_category C, 
										ls_category P
								WHERE 
										C.LFT BETWEEN P.LFT AND P.RGT
								GROUP BY 
										C.CATEGORY_ID, C.CATEGORY_NAME, C.LFT 
								ORDER BY 
										C.LFT
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['CATEGORY_ID']] = $selectOption['CONTROLLER_DOT'].$selectOption['CATEGORY_NAME'];
			}
			return $selectData;
		}
	}
?>	