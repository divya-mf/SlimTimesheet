<?php
/**
 * FileMakerWrapper
 * class that manages all the methods required to interact with database
 *
 */
namespace Src\Api;

class FileMakerWrapper{
    private $fm;
    private $class;
    private $log;

	/**
 	 * Constructor
 	 * initializes the private class variable with configuration object for database connectivity
 	 * initializes the private class variable with logger object for logging errors
 	 */
	public function __construct($container){
        $this->fm=$container->get('db'); //FileMaker connection object
        $this->class=$container->get('Constants')->fileMaker;
        $this->log=$container->get('logger');
        
	}

	/**
	 * getOne
     * finds and fetches the information as per given criteria.
     *
     * @param string $layout The FileMaker layout name.
     * @param array $findData Array of data to find records with.
     * returns {array}
     */
	public function getOne($layout, $findData)
	{
        $findCommand = $this->fm->newFindCommand($layout);
        foreach ($findData as $key => $val) {
            
            $findCommand->addFindCriterion($key,"==$val");
        }
        $result = $findCommand->execute();
        if ($this->class::isError($result)){   
            $status['msg']=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $status['records']=[]; 
             
            $result->code!= 401 ?  $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
            
        }
        else {
            $records = $result->getRecords();
            $status['msg']=array('status'=> "Ok", 'code'=> 200);
            $status['records']=$records;
        }
        return $status;
    }

	/**
	 * getAll
     * finds and fetches all the information from given layout.
     *
     * @param string $layout The FileMaker layout name.
     * returns {array}
     */
	public function getAll($layout)
	{
		$query = $this->fm->newFindAllCommand($layout); 
        $result = $query->execute();
        $records = $result->getRecords();
        $status['records']=$records;
        $status['msg']=array('status'=> "Ok", 'code'=> 200);

        if ($this->class::isError($records)){
            $status['msg']=array('status'=> $records->getMessage(), 'code'=> $records->code);
            $status['records']=[];
            $records->code!= 401 ? $this->log->addInfo($records->code.'=> '.$records->getMessage()) : '';
        } 
        return $status;
    }
    
    /**
	 * getRecordsByRange
     * finds and fetches all the information from given layout and returns limited results as per requested range.
     *
     * @param string $layout The FileMaker layout name.
     * @param array $findCriteria The fields and values to find if any.
     * @param array $range data related mininum and maximum values of range.
     * returns {array}
     */
	public function getRecordsByRange($layout, $findCriteria, $range)
	{
		$findCommand = $this->fm->newFindCommand($layout);
        foreach ($findCriteria as $key => $val) {      
            $findCommand->addFindCriterion($key,"==$val");
        }
        $skip=$range['skip'];
        $max= $range['max'];
        $findCommand->setRange($skip, $max);
        $result = $findCommand->execute();
        $found = $result->getFoundSetCount();
        if ($this->class::isError($result)){   
            $status['msg']=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $status['records']=[]; 
             
            $result->code!= 401 ?  $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
            
        }
        else{
            $records = $result->getRecords();
            $status['msg']=array('status'=> "Ok", 'code'=> 200);
            $status['records']=$records;
            $status['total']=$found;
        }
        return $status;
    }


    /**
     * getSearchResult
     * finds and fetches all the information from given layout as per the search.
     *
     * @param string $layout The FileMaker layout name.
     * @param array $allANDs fields that needs AND operation
     * @param array $allORs fields that needs OR operation.
     * returns {array}
     */
    public function getSearchResult($layout,$allANDs,$allORs)
    {
        $findCommand = $this->fm->newCompoundFindCommand($layout);
        $i=1;

        foreach ($allORs as $key => $val) 
        {
            ${'findRequest' . $i} = $this->fm->newFindRequest($layout);

            if($val!='') {
                ${'findRequest' . $i}->addFindCriterion($key, "*$val*");
            }
            
            foreach ($allANDs as $field => $value){
                if($value!=''){
                    ${'findRequest' . $i}->addFindCriterion($field, "==$value");
                }
            }
            if(empty($val) && empty($value)){
                $status['flag']=0;
            }
            else{
                $findCommand->add($i, ${'findRequest' . $i});
                $i++;
            } 
        }

        $result = $findCommand->execute();
        if ($this->class::isError($result)){
            $status['msg']=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $status['records']=[];

            $result->code!= 401 ? $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
        }
        else
        {
            $records = $result->getRecords(); 
            $status['records']=$records;

            if ($this->class::isError($records)){
                $status['msg']=array('status'=> $records->getMessage(), 'code'=> $records->code);
                $status['records']=[];
                $records->code!= 401 ? $this->log->addInfo($records->code.'=> '.$records->getMessage()) : '';
            }
        }
        return $status;
    }
    

	/**
	 * createRecord
     * creates/adds record to database.
     *
     * @param string $layout The FileMaker layout name.
     * @param string $data The data to insert in the database.
     * returns {boolean value}
     */
	public function createRecord($layout, $data)
	{
        $rec = $this->fm->createRecord($layout, $data);
        $result = $rec->commit();
        
       if ($this->class::isError($result)) {
            $status=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $result->code!= 401 ? $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
            
            return $status;
       }

       return  $status=array('status'=> "Ok", 'code'=> 200, 'description'=> "Added successfully");
    }


    /**
	 * deleteRecord
     * deletes a record from the database.
     *
     * @param string $layout The FileMaker layout name.
     * @param string $id The id of the record to delete from the database.
     * returns {boolean value}
     */
	public function deleteRecord($layout, $id)
	{

        $findCommand =$this->fm->newFindCommand($layout);
        $findCommand->addFindCriterion('id', "==$id");;
        $result = $findCommand->execute(); 
        $records = $result->getRecords(); 
        $records->delete();
        
       if ($this->class::isError($result)) {
            $status=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $result->code!= 401 ? $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
            
            return $status;
       }
        

       return  $status=array('status'=> "Ok", 'code'=> 200, 'description'=> "Deleted successfully");
    }


    /**
     * updateRecord
     * updates record in database.
     *
     * @param string $layout The FileMaker layout name.
     * @param string $data The data to update in the database.
     * returns {boolean value}
     */
    public function updateRecord($layout,$id,$data)
    {
        $findCommand = $this->fm->newFindCommand($layout);
        
        $findCommand->addFindCriterion('id',"==$id");
        $result = $findCommand->execute();
        
       if ($this->class::isError($result)) {
            $status=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $result->code!= 401 ? $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
            
            return $status;
       }
        $rec_ID = $result->getLastRecord()->getRecordID();
        $rec = $this->fm->getRecordById($layout, $rec_ID);
        foreach ($data as $field => $value)
            {
                if($value!=''){
                    $rec->setField($field, $value);
                }
            }
       
        $result = $rec->commit();
        if ($this->class::isError($result)) {
            $status=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $result->code!= 401 ? $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
            
            return $status;
        }
            
       return  $status=array('status'=> "success", 'code'=> 200);
    }

   /**
     * performScript
     * executes scripts.
     *
     * @param string $layout The FileMaker layout name.
     * @param string $scriptName The name of the script.
     * @param string $scriptParameter The parameters to pass.
     * returns {boolean value}
     */
    public function performScript($layout, $scriptName,$scriptParameter)
    {
       $scriptObject = $this->fm->newPerformScriptCommand($layout, $scriptName,$scriptParameter);
       $result = $scriptObject->execute(); 
       if ($this->class::isError($result)) {
            $status=[];
            $result->code!= 401 ? $this->log->addInfo($result->code.'=> '.$result->getMessage()) : '';
            
            return $status;
       }

         return $status=array('status'=> "Ok", 'code'=> 200, 'description'=> "Successful");
         
    }


}


?>