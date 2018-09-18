<?php
/**
 * AuthController
 * class that manages all the login and registration of the user.
 *
 */

namespace Src\Controllers;

class AuthController extends Controller
 { 
    private $class;
    private $fmMethodsObj;
    private $log;

    public function __construct( $container)
    {
        $this->fmMethodsObj = $container->get('FileMakerWrapper'); //FileMaker connection object
        $this->log = $container->get('logger');
        $this->class = $container->get('Constants')->fileMaker;
	}
    
    /**
	 * signUp
     * registers user
     *
     * returns {json object}
     */
    public function signUp($request, $response)
    {
        //array to store the values to pass into the database
        $data=$request->getParsedBody();
        $email = $data['email'];
        $password =  password_hash($data['password'], PASSWORD_DEFAULT);

        $userDetails = array(
                    'firstName' => $data['firstName'],
                    'lastName' => $data['lastName'],
                    'email' => $email,
                    'password'=> $password

                );
        $checkEmail = array(
                    'email'=> $email
                );
        $result=$this->fmMethodsObj->getOne('USR',$checkEmail);
       

        //to check if there are no records with similar email id.
        if (empty ($result['records'])) 
        {
            $newUser = $this->fmMethodsObj->createRecord('USR', $userDetails);
           // $passStatus = $this->fmMethodsObj->performScript('USR', 'hashPassword',$password);  
            $res = array('description'=>"registered successfully");  
            $http_status_code = 200;
             
        }
        else
        { 
            $res = array('description'=>"email already exists");
            $http_status_code = 400;        
        }

        return $response->withJson($res)
                        ->withStatus($http_status_code);
      
    
    }

    /**
	 * login
     * user authentication for login
     *
     * returns {json object}
     */
    public function login($request, $response)
    {
        $email = $request->getParsedBody()['email'];
        $pw =  $request->getParsedBody()['password'];
        $loginData = array(
                'email'=> $email
        );
        $result = $this->fmMethodsObj->getOne('USR',$loginData);

        if (empty ($result['records']) )
        { 
            $res = array('description'=>"incorrect credentials");
            $http_status_code = 400;
        }
        else
        {
            if(password_verify($pw,$result['records'][0]->getField('password')))
            {
                $_SESSION['id'] = $result['records'][0]->getField('id');
                $_SESSION['role'] = $result['records'][0]->getField('role');
            
                $res = array('description'=>"login successful",'id'=>$_SESSION['id']);
                $http_status_code = 200;
            }
            else
            {
                $res = array('description'=>"incorrect credentials");
                $http_status_code = 400;
            }
          
        }
        return $response->withJson($res)
                        ->withStatus($http_status_code);
       
    }


}