<?php
        require("class-IXR.php");  
        //$client = new IXR_Client('http://login.tozny.com/wordpress/xmlrpc.php');
        $client = new IXR_Client('http://localhost/wordpress/xmlrpc.php');
     
        $USER = 'demo';
        $PASS = 'fphFYDghS7&Y';
        //$PASS = md5('Authenticated through SimpleSAML');
        //$PASS = md5('me7fdjmghdugsyuijfroijfd874387rfry7r4ijf4eq8feijooij');

        if (!$client->query('tozny.getTestPW','', $USER,$PASS))
        {  
            echo('Error occured during category request.' . $client->getErrorCode().":".$client->getErrorMessage());  
        }
        $out = $client->getResponse();
       
        if(!empty($out))
        {
          echo $out;
          //foreach($cats as $_cat) echo $_cat['categoryName'];
        }

?>
