<?php
/**
 * UserActivitiesController
 * class that manages all the operations related to users and activities.
 *
 */

namespace Src\Controllers;

class UserActivitiesController extends Controller
 { 
    private $fmMethodsObj;
    private $id; 
	private $role;

    public function __construct($container){
        //include __DIR__ .'/../api/FileMakerWrapper.php';
		$this->fmMethodsObj= $container->get('FileMakerWrapper');
		
	}
    
    /**
	 * getAllUsers
     * fetches the information of all the users.
     *
     * returns json object
     */
	public function getAllUsers($request, $response)
	{
	    $result = $this->fmMethodsObj->getAll('USR'); 
        $records=$result['records'];
	    $users=array();
	    $i=0;
	    foreach ($records as $record) { 
	    	$users[] = array(
	    	);
	        $users[$i]['id']=$record->getField('id');
	        $users[$i]['first_name']=$record->getField('firstName');
	        $users[$i]['last_name']=$record->getField('lastName');

	        $i++;
	    }
        $userInfo['msg']= $result['msg'];
        $userInfo['users']=$users;
        
        return $response->withStatus(200)
                        ->withJson($userInfo);
	}

	/**
	 * getUserDetails
     * fetches the information of requested user.
     *
     * returns json object
     */
	public function getUserDetails($request, $response)
	{
		$userDetails = array(
	                    'id' =>$request->getParsedBody()['id'],
	                    );
	    $result = $this->fmMethodsObj->getOne('USR', $userDetails); 
        $records=$result['records'][0];
	    $user=array();
	    $user['id']=$records->getField('id');
	    $user['first_name']=$records->getField('firstName');
	    $user['last_name']=$records->getField('lastName');
	    $user['email']=$records->getField('email');
	    $user['role']=$records->getField('role');
        $userInfo['msg']= $result['msg'];
        $userInfo['user']=$user;
        
        return $response->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->write(json_encode($userInfo));
	}


	/**
	 * addActivity
     * adds an activity to the database
     *
     * returns {json object}
     */
	public function addActivity($request, $response)
	{
	    if($request->getParsedBody()['description']!=''){

			$date = date("m-d-Y", strtotime($request->getParsedBody()['date']));
	        $activityDetails = array(
	                    'description' =>$request->getParsedBody()['description'],
	                    '__fk_user_id' =>$request->getParsedBody()['user_id'],
	                    'priority'=>$request->getParsedBody()['priority'],
	                    'dueDate'=>$date
	                    );

	        $result=$this->fmMethodsObj->createRecord('ACT',$activityDetails);

	        return $response->withStatus(200)
                            ->withJson("Activity added successfully");
	    }
	    else
	        {   
				$res = array('status'=> "BAD REQUEST", 'code'=> 400,'description'=>"Fill in all the fields");
				
           		 return $response->withStatus(400)
                            	 ->withJson($res);
            }
	}


	/**
	 * getAllActivities
     * fetches all the activities
     *
     * returns {json object}
     */
	public function getAllActivities($request, $response)
	{

		$activities=array();
		$id=$request->getParsedBody()['dataToSend']['id'];
		$role=$request->getParsedBody()['dataToSend']['role'];
		$userDetails = array(
	                    '__fk_user_id' =>$id,
	                    );
		if(isset($request->getParsedBody()['dataToSend']['allANDs']) || isset($request->getParsedBody()['dataToSend']['allORs'])){

			$allANDs=$request->getParsedBody()['dataToSend']['allANDs'];
			$allORs=$request->getParsedBody()['dataToSend']['allORs'];
		}
	    if($role == 'Project Head' || $role == 'Developer')
	    {
	    	if(isset($allANDs) || isset($allORs))
	    	{
	           	$records = $this->fmMethodsObj->getSearchResult('ACT',$allANDs,$allORs);
	        }
	        else
	        {

	        	$records = $this->fmMethodsObj->getAll('ACT');
	    	}
	    }
	    else
	    {
	    	if(isset($allANDs)|| isset($allORs))
	    	{
	    		
	          $allANDs = array_merge($allANDs, $userDetails);
	          $records = $this->fmMethodsObj->getSearchResult('ACT',$allANDs,$allORs);
	          	
	        }
	        else
	        {
	        	$records = $this->fmMethodsObj->getOne('ACT',$userDetails);
	        }
	    }

	    $i=0;
	    if(!empty($records['records']))
	    {
            $result=$records['records'];
		    foreach ($result as $record) { 

		        $activities[$i]['id']=$record->getField('id');
		        $activities[$i]['description']=$record->getField('description');
		        $activities[$i]['status']=$record->getField('status');
		        $activities[$i]['user_id']=$record->getField('userName1');
		        $activities[$i]['priority']=$record->getField('priority');
		        $activities[$i]['creationDate']=$record->getField('createdDate');
		        $activities[$i]['dueDate']=$record->getField('dueDate');

		        $i++;
			}

            $httpResponseCode=200;
            $res=$activities;
        }
        else
        {
        	if(isset($records['flag']) )
        	{
        		$httpResponseCode=200;
            	$res=$records['flag'];
        	}
        	else
        	{
        		$httpResponseCode=400;
        	    $res=$activities;
        	}
        }

        return $response->withStatus($httpResponseCode)
                        ->withJson($res);
        
	}

	/**
	 * updateStatus
     * updates the status of an activity in the database
     *
     * returns {json object}
     */
	public function updateStatus($request, $response)
	{
			
	        $activityDetails = array(
	                    'id' =>$request->getParsedBody()['id'],
	                    'status' =>$request->getParsedBody()['status'],
	                
	                    );

	        $result=$this->fmMethodsObj->updateRecord('ACT',$activityDetails);

	        return $response->withJson($result);
	    
	}



}