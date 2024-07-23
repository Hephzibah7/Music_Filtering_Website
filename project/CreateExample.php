<?php
/*
 * This example shows how new databases can be created.
 *
 * Documentation: https://docs.basex.org/wiki/Clients
 *
 * (C) BaseX Team 2005-24, BSD License
 */
include_once 'load.php';    

use BaseXClient\BaseXException;
use BaseXClient\Session;

try{
    // create session
    $session = new Session("localhost", 1984, "admin", "admin");

    // create new database
    // $session->create("database", "<x>Hello World!</x>");
    // print $session->info();
    $xquery = "for \$x in collection(\"imdb\")/imdb/movies/movie/name
    return <result>{\$x}</result>";
    $query = $session->query($xquery);
    foreach($query as $result) {
        echo $result."<br>";
    }


    $session->close();
}
catch (BaseXException $e) {
    // print exception
    print "Caught exception: " . $e->getMessage() . "\n";
    print "Error code: " . $e->getCode() . "\n";
    print "Exception object: " . var_export($e, true) . "\n";

}
