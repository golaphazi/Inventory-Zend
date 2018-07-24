<?php
//filename : module/Ibcia/src/Ibcia/Form/LoginForm.php
namespace Ibcia\Form;
 
use Zend\Form\Form;
use Zend\InputFilter;
 
class SignupForm extends Form
{
    public function __construct()
    {
        parent::__construct('signup');
          
        $this->setAttribute('method', 'post');
        $this->setAttribute('onsubmit', 'return check()');
        $this->add(array(
            'name' => 'OPERATOR_NAME',
            'type' => 'Text',
			'attributes' => array(
				'placeholder' => 'User Name',
				'id' => 'username',
				'required' => true,
				'autocomplete' => 'off',
			),
        ));
         
        $this->add(array(
            'name' => 'OPERATOR_PASSWORD',
            'type' => 'Password',
			'attributes' => array(
				'placeholder' => 'Password',
				'id' => 'password',
				'required' => true,
			),
        ));
		
		$this->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'REMEMBER_ME',
			'options' => array(
				'label' => 'Remember me?',
				'use_hidden_element' => true,
				'checked_value' => 'Y',
				'unchecked_value' => 'N',
			),
			'attributes' => array(
				'class' => 'remember-me',
			),
		));
         
         $this->add(array(
            'name' => 'btnLogin',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Login',
				'class' => 'submit'
            ),
        ));
          
        $this->setInputFilter($this->createInputFilter());
    }
    
    public function createInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
 
        //username
        $username = new InputFilter\Input('OPERATOR_NAME');
        $username->setRequired(true);
        $inputFilter->add($username);
         
        //password
        $password = new InputFilter\Input('OPERATOR_PASSWORD');
        $password->setRequired(true);
        $inputFilter->add($password);
 
        return $inputFilter;
    }
}