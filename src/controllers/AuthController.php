<?php
/**
 * AuthController
 * class that manages all the login and registration of the user.
 *
 */
use Firebase\JWT\JWT;
use Tuupola\Base62;
use \Datetime;
namespace Src\Controllers;

class AuthController extends Controller
 { 
    private $class;
    private $fmMethodsObj;
    private $contain;
    private $log;
    private $middleware;

    public function __construct( $container)
    {
        $this->fmMethodsObj = $container->get('FileMakerWrapper'); //FileMaker connection object
        $this->log = $container->get('logger');
        $this->class = $container->get('Constants')->fileMaker;
        $this->middleware = $container->get('Controller');

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
                $userName=$result['records'][0]->getField('firstName');
                $now = new DateTime();
                $future = new DateTime("+10 minutes");
                //$server = $request->getServerParams();
                $jti = (new Base62)->encode(random_bytes(16));
                $payload = [
                    "iat" => $now->getTimeStamp(),
                    "exp" => $future->getTimeStamp(),
                    "jti" => $jti,
                    "user" => $userName
                ];
                print_r($payload);exit;
                $secret = getenv('JWT_SECRET');
                $token = JWT::encode($payload, $secret, "HS256");
                $data = array();
                $data["token"] = $token;
                $data["expires"] = $future->getTimeStamp();
               
                $res = array('description'=>"login successful",'id'=>$_SESSION['id'],"user"=>$userName);
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