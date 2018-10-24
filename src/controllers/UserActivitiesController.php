<?php
/**
 * UserActivitiesController
 * class that manages all the operations related to users and activities.
 */

namespace Src\Controllers;

class UserActivitiesController extends Controller
{

 
    private $fmMethodsObj;
    private $id; 
    private $role;

    public function __construct($container)
    {
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
		$userInfo['auth'] =$_SERVER["HTTP_AUTHORIZATION"];
		
        return $response->withStatus(200)
                        ->withJson($userInfo);
    }


    /**
     * addActivity
     * adds an activity to the database
     *
     * returns {json object}
     */
    public function addActivity($request, $response)
    {
        if($request->getParsedBody()['description']!='') {

            $date = date("m-d-Y", strtotime($request->getParsedBody()['date']));
            $activityDetails = array(
                        'description' =>$request->getParsedBody()['description'],
                        'fk_user_id' =>$request->getParsedBody()['user_id'],
                        'priority'=>$request->getParsedBody()['priority'],
                        'dueDate'=>$date
                        );

            $result=$this->fmMethodsObj->createRecord('ACT', $activityDetails);

            return $response->withStatus(200)
                            ->withJson("Activity added successfully");
        }
        else{   
            $res = array('status'=> "BAD REQUEST", 'code'=> 400,'description'=>"Fill in all the fields");
                
            return $response->withStatus(400)
                            ->withJson($res);
        }
    }

    /**
     * addNoteToActivity
     * adds a note to an activity 
     *
     * returns {json object}
     */
    public function addNoteToActivity($request, $response)
    {
        if($request->getParsedBody()['noteData']['note']!='') {

            $noteDetails = array(
                        'note' =>$request->getParsedBody()['noteData']['note'],
                        'timeSpent' =>$request->getParsedBody()['noteData']['timeSpent'],
                        'activityID'=>$request->getParsedBody()['aId']
                        );

            $result=$this->fmMethodsObj->createRecord('activityNotes', $noteDetails);

            return $response->withStatus(200)
                            ->withJson("Note added successfully");
        }
        else{   
            $res = array('status'=> "BAD REQUEST", 'code'=> 400,'description'=>"Fill in all the fields");
                
            return $response->withStatus(400)
                            ->withJson($res);
        }
    }

    /**
     * deleteActivity
     * deletes activity from the database
     *
     * returns {json object}
     */
    public function deleteActivity($request, $response)
    {         
        $id=$request->getParsedBody()['id'];
        $result=$this->fmMethodsObj->deleteRecord('ACT', $id);

        return $response->withStatus(200)
                        ->withJson("deleted successfully");
        
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
                        'fk_user_id' =>$id,
                        );
        if(isset($request->getParsedBody()['dataToSend']['allANDs']) || isset($request->getParsedBody()['dataToSend']['allORs'])) {

            $allANDs=$request->getParsedBody()['dataToSend']['allANDs'];
            $allORs=$request->getParsedBody()['dataToSend']['allORs'];
        }
        if($role == 'Project Head' || $role == 'Developer') {
            if(isset($allANDs) || isset($allORs)) {
                $records = $this->fmMethodsObj->getSearchResult('ACT', $allANDs, $allORs);
            }
            else{
                if(isset($request->getParsedBody()['dataToSend']['status']) && $request->getParsedBody()['dataToSend']['status']!= '' ) {
                       $status = array(
                       'status'=>$request->getParsedBody()['dataToSend']['status']
                      );
                      $range = array(
                       'max'=> $request->getParsedBody()['dataToSend']['max'],
                       'skip'=> $request->getParsedBody()['dataToSend']['skip']
                      );
                      $records = $this->fmMethodsObj->getRecordsByRange('ACT', $status, $range);
                }
                else{
                       $records = $this->fmMethodsObj->getAll('ACT');
                }
            }
        }
        else{
            if(isset($allANDs)|| isset($allORs)) {
                
                 $allANDs = array_merge($allANDs, $userDetails);
                 $records = $this->fmMethodsObj->getSearchResult('ACT', $allANDs, $allORs);
                  
            }
            else{
                $records = $this->fmMethodsObj->getOne('ACT', $userDetails);
            }
        }

        $i=0;
        if(!empty($records['records'])) {
            $result=$records['records'];
            foreach ($result as $record) { 
                $activities[$i]['id']=$record->getField('id');
                $activities[$i]['description']=$record->getField('description');
                $activities[$i]['status']=$record->getField('status');
                $activities[$i]['fk_user_id']=$record->getField('fk_user_id');
                $activities[$i]['user_id']=$record->getField('userName1');
                $activities[$i]['priority']=$record->getField('priority');
                $activities[$i]['creationDate']=$record->getField('createdDate');
                $activities[$i]['dueDate']=$record->getField('dueDate');

                $i++;
            }
            $httpResponseCode=200;
                $res=$activities;

            if(isset($records['total'])) {
                $activitiesData['found']=$records['total'];
                $activitiesData['activities']=$activities;
                $res=$activitiesData;
            }
            
        }
        else{
            if(isset($records['flag']) ) {
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
        $id = $request->getParsedBody()['id'];
        $activityDetails = array(
                    'status' =>$request->getParsedBody()['status'],
                    );
        $result=$this->fmMethodsObj->updateRecord('ACT', $id, $activityDetails);
        
        return $response->withJson($result);
    }

    /**
     * updateActivity
     * updates the details of an activity in the database
     *
     * returns {json object}
     */
    public function updateActivity($request, $response)
    {
        $id = $request->getParsedBody()['id'];
        $activityDetails = array(
        'description' =>$request->getParsedBody()['activityData']['description'],
        'priority' =>$request->getParsedBody()['activityData']['priority'],
        'dueDate' =>$request->getParsedBody()['activityData']['date']
                    );

        $result=$this->fmMethodsObj->updateRecord('ACT', $id, $activityDetails);

        return $response->withJson($result);    
    }
    /**
     * updateUser
     * updates the details of an activity in the database
     *
     * returns {json object}
     */
    public function updateUser($request, $response)
    {
        $id = $request->getParsedBody()['id'];
        $userDetails = array(
        'firstName' =>$request->getParsedBody()['userData']['fname'],
        'lastName' =>$request->getParsedBody()['userData']['lname'],
        'email' =>$request->getParsedBody()['userData']['email']
                    );

        $result=$this->fmMethodsObj->updateRecord('USR', $id, $userDetails);

        return $response->withJson($result);   
    }

    /**
     * getActivityNotes
     * fetches all the notes assigned to an activity.
     *
     * returns json object
     */
    public function getActivityNotes($request, $response)
    {
        $notes=array();
        $actDetails = array(
                        'activityID' =>$request->getParsedBody()['aId'],
                        );
        $result = $this->fmMethodsObj->getOne('activityNotes', $actDetails); 
        
        $i=0;
        if(!empty($result['records'])) {
            $result=$result['records'];
            foreach ($result as $record) { 

                $notes[$i]['id']=$record->getField('id');
                $notes[$i]['note']=$record->getField('note');
                $notes[$i]['timeSpent']=$record->getField('timeSpent');
                $notes[$i]['AddedBy']=$record->getField('AddedBy');
                $i++;
            }

            $httpResponseCode=200;
            $res=$notes;
        }
        else{
            $httpResponseCode=200;
            $res="";
            
        }

        return $response->withStatus($httpResponseCode)
                        ->withJson($res);
        
    }
    
}
