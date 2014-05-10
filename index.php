<?PHP
// session_start();

// Prevent caching.
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 01 Jan 1996 00:00:00 GMT');

// The JSON standard MIME header.
// header('Content-type: application/json');

//error_reporting(1);
require("curl.php");

getAgents(); //	//first, get list of ALL agents, including admins and owners

// Replace the variable values below
// with your specific database information.
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass ='zend001!';
$db = 'rrobin';
$conn = mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db($db);

$query = "SELECT * FROM agents";
$result = mysql_query($query);


/*
while($person = mysql_result_assoc($result, 0));
{
	var_dump(mysql_error());

	var_dump($person);
	echo $person['id'];
}
*/

try {
        $dbh = new PDO('mysql:host=localhost;dbname=rrobin', 'root', 'zend001!', array( PDO::ATTR_PERSISTENT => false));

        $stmt = $dbh->prepare("CALL getname()");

        // call the stored procedure
        $stmt->execute();

        echo "<B>outputting...</B><BR>";
        while ($rs = $stmt->fetch(PDO::FETCH_OBJ)) {
            echo "output: ".$rs->name."<BR>";
        }
        echo "<BR><B>".date("r")."</B>";
    
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }



exit;


function getAgents()
{
	$data = curlWrap("/users.json?role[]=admin&role[]=agent", null, "GET");
	
	// separate headers from data
	list($header, $body) = explode("\r\n\r\n", $data, 2);
	$f_header = http_parse_headers($header);
	$f_body = json_decode($body);
	
	//var_dump($f_body); exit;
	
	foreach ($f_body as $val) { 					
		if(is_array($val))
		{
			for($x=0; $x<count($val); $x++)
			{
				echo "Agent: " . $val[$x]->name . " (" . $val[$x]->id . ") -- " . $val[$x]->role . "<br>";
			}	
		}
	}
}

function deleteTopic($id)
{
	$data = curlWrap("/topics/" . $id . ".json", null, "DELETE");
}

//var_dump($data); exit; // <--------------- UNCOMMENT THIS LINE TO GET JSON RAW DATA
/*


//echo $f_body->ticket->url;


var_dump($f_body->ticket);
echo "<br><br><br>";


foreach ($f_body as $val) { 

    echo "ID: " . $val->id . "<br>";
    echo "Requester: " . getUser($val->requester_id) . "<br>";
    echo "CC(s): ";
    
    for($x=0; $x < count($val->collaborator_ids); $x++)
    {
        echo getUser($val->collaborator_ids[$x]);   
    }
}

function getUser($id)
{
    $data = curlWrap("/users/" . $id . ".json", null, "GET");

    // separate headers from data
    list($header, $body) = explode("\r\n\r\n", $data, 2);
    $f_header = http_parse_headers($header);
    $f_body = json_decode($body);
    
    
    return $f_body->user->name . "(" . $f_body->user->email . ")";
    //var_dump($f_body);
}
*/
?>
