<?php
/**
 * This file is responsible for creating and managing routes within the app.
 * 
*/

use Firebase\JWT\JWT;
use Tuupola\Base62;

/**
 * Adds a new User to db
 * 
 */
$app->post('/register','AuthController:signUp');

/**
 * Fetches users from db
 * 
 */
$app->get('/users','UserActivitiesController:getAllUsers');

/**
 * Fetches all the activities as per logged user.
 * 
 */
$app->post('/activities','UserActivitiesController:getAllActivities');

/**
 * Adds a new activity.
 * 
 */
$app->post('/addActivity','UserActivitiesController:addActivity');

/**
 * Adds a new note to specified activity.
 * 
 */
$app->post('/addNoteToActivity','UserActivitiesController:addNoteToActivity');

/**
 * Updates the status of an activity.
 * 
 */
$app->post('/updateStatus','UserActivitiesController:updateStatus');

/**
 * Fetches the details of requested user.
 * 
 */
$app->post('/getUserDetails','UserActivitiesController:getUserDetails');

/**
 * Fetches the notes of different activities.
 * 
 */
$app->post('/getActivityNotes','UserActivitiesController:getActivityNotes');

/**
 * Deletes the selected activity.
 * 
 */
$app->post('/deleteActivity','UserActivitiesController:deleteActivity');

/**
 * Updates the details of selected activity.
 * 
 */
$app->post('/updateActivity','UserActivitiesController:updateActivity');

/**
 * Updates the deatails of selected User.
 * 
 */
$app->post('/updateUser','UserActivitiesController:updateUser');

/**
 * login
 * user authentication for login
 * @param \Psr\Http\Message\ServerRequestInterface $request
 * @param \Psr\Http\Message\ResponseInterface $response
 * returns {json object}
 */
$app->post("/login",  function ($request, $response)
{
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
                $id = $result['records'][0]->getField('id');
                $role= $result['records'][0]->getField('role');
                $userName = $result['records'][0]->getField('firstName');
                /* generating and return JWT token to the client. */
                $now = new DateTime();
                $future = new DateTime("+30 minutes");
                $jti = (new Base62)->encode(random_bytes(16));
                $payload = [
                    "iat" => $now->getTimeStamp(),
                    "exp" => $future->getTimeStamp(),
                    "jti" => $jti,
                    "user" => $userName
                ];
                
                $secret = getenv('JWT_SECRET');
                $token = JWT::encode($payload, $secret, "HS256");
                $res = array('description'=>"login successful",'id'=>$id,"user"=>$userName, "token"=> $token, "expires"=>$future->getTimeStamp());
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
