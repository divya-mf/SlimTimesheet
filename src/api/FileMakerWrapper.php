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
 	 * includes the configuration file for database connectivity
 	 * initializes the private class variable with FileMaker object 
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
     * @param string $field The FileMaker field name.
     * @param string $value The value that needs to be searched in the field.
     * returns {array}
     */
	public function getOne($layout, $loginData)
	{
        $findCommand = $this->fm->newFindCommand($layout);
        foreach ($loginData as $key => $val) {
            
            $findCommand->addFindCriterion($key,"==$val");
        }
        $result = $findCommand->execute();
        if ($this->class::isError($result))
        {   
            $status['msg']=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $status['records']=[]; 
             if($result->code!= 401)
            {
                $this->log->addInfo($result->code.'=> '.$result->getMessage());
            }
        }
        else
        {
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
        if ($this->class::isError($records))
        {
            $status['msg']=array('status'=> $records->getMessage(), 'code'=> $records->code);
            $status['records']=[];
            $this->log->addInfo($records->code.'=> '.$records->getMessage());
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

        foreach ($allORs as $key => $val) {

            ${'findRequest' . $i} = $this->fm->newFindRequest($layout);

            if($val!='')
            {
                ${'findRequest' . $i}->addFindCriterion($key, "*$val*");
            }
            
            foreach ($allANDs as $field => $value)
            {
                if($value!='')
                {
                    ${'findRequest' . $i}->addFindCriterion($field, "==$value");
                }
            }
            if(empty($val) && empty($value))
            {
                $status['flag']=0;
            }
            else
            {
                $findCommand->add($i, ${'findRequest' . $i});
                $i++;
            }

            

        }

        $result = $findCommand->execute();
        if ($this->class::isError($result))
        {
            $status['msg']=array('status'=> $result->getMessage(), 'code'=> $result->code);
            $status['records']=[];

            $this->log->addInfo($result->code.'=> '.$result->getMessage());
        }
        else
        {
            $records = $result->getRecords(); 
            $status['records']=$records;

        
            if ($this->class::isError($records))
            {
                $status['msg']=array('status'=> $records->getMessage(), 'code'=> $records->code);
                $status['records']=[];
                $this->log->addInfo($records->code.'=> '.$records->getMessage());
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
        $scriptName="hashPasssword";
        $rec = $this->fm->createRecord($layout, $data);
        $result = $rec->commit();
        
       if ($this->class::isError($result)) 
       {
            $status=array('status'=> $result->getMessage(), 'code'=> $result->code);
            if($result->code!= 401)
            {
                $this->log->addInfo($result->code.'=> '.$result->getMessage());
            }
            return $status;
       }
         // Execute the script
       $scriptObject = $this->fm->newPerformScriptCommand($layout, $scriptName);
       $result = $scriptObject->execute();

       return  $status=array('status'=> "Ok", 'code'=> 200, 'description'=> "Added successfully");
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
       if ($this->class::isError($result)) 
       {
            $status=[];
            if($result->code!= 401)
            {
                $this->log->addInfo($result->code.'=> '.$result->getMessage());
            }
            return $status;
       }

         return $status=array('status'=> "Ok", 'code'=> 200, 'description'=> "Successful");
         
    }


}


?>