<?php
/**
 * This file is responsible for creating and managing routes within the app.
 * 
*/
use Firebase\JWT\JWT;
use Tuupola\Base62;

$app->post('/register','AuthController:signUp');
$app->get('/users','UserActivitiesController:getAllUsers');
$app->post('/activities','UserActivitiesController:getAllActivities');
$app->post('/addActivity','UserActivitiesController:addActivity');
$app->post('/addNoteToActivity','UserActivitiesController:addNoteToActivity');
$app->post('/updateStatus','UserActivitiesController:updateStatus');
$app->post('/getUserDetails','UserActivitiesController:getUserDetails');
$app->post('/getActivityNotes','UserActivitiesController:getActivityNotes');
$app->post('/deleteActivity','UserActivitiesController:deleteActivity');

//$app->get('/allActivities','UserActivitiesController:allActivities');

    /**
	 * login
     * user authentication for login
     *
     * returns {json object}
     */
    $app->post("/login",  function ($request, $response){

   
        $this->fmMethodsObj =$this->get("FileMakerWrapper");
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
                /* generating and return JWT token to the client. */
                $now = new DateTime();
                $future = new DateTime("+30 minutes");
                //$server = $request->getServerParams();
                $jti = (new Base62)->encode(random_bytes(16));
                $payload = [
                    "iat" => $now->getTimeStamp(),
                    "exp" => $future->getTimeStamp(),
                    "jti" => $jti,
                    "user" => $userName
                ];
                
                $secret = getenv('JWT_SECRET');
                $token = JWT::encode($payload, $secret, "HS256");
                $res = array('description'=>"login successful",'id'=>$_SESSION['id'],"user"=>$userName, "token"=> $token, "expires"=>$future->getTimeStamp());
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
       
    
    

});
