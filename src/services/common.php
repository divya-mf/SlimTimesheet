<?php
namespace Src\Services;

class Common
{
  function sanitize($data) 
  {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data,ENT_QUOTES);

      return $data;
  }

}

?>