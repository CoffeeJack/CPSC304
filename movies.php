<!--Test Oracle file for UBC CPSC304 2011 Winter Term 2
  Created by Jiemin Zhang
  Modified by Simona Radu
  This file shows the very basics of how to execute PHP commands
  on Oracle.  
  specifically, it will drop a table, create a table, insert values
  update values, and then query for values
 
  IF YOU HAVE A TABLE CALLED "Movies" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the 
  OCILogon below to be your ORACLE username and password -->
  
<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

<!-- Reset -->
<form method="POST" action="movies.php">   
<p><input type="submit" value="Reset" name="reset"></p>
</form>

<p>Insert Movies Info into Movies Table below:</p>

<form method="POST" action="movies.php">
<!--refresh page when submit-->
<pre>
Movies ID	<input type="text" name="insMovieID" size="18">
Movies Title	<input type="text" name="insMovieTitle" size="18">
Year		<input type="text" name="insYear" size="18">
Gross Earning	<input type="text" name="insGross" size="18">
Synoposis	<input type="text" name="insSynopsis" size="18">
</pre>

<input type="submit" value="insert" name="insertsubmit"></p>
</form>
<!-- create a form to pass the values. See below for how to 
get the values--> 

<p> Update a movie entry by inserting new values below: </p>

<form method="POST" action="movies.php">
<!--refresh page when submit-->

<pre>
				   Update?
Movies ID	<input type="text" name="index_MovieID" size="18">
Movies Title	<input type="text" name="new_MovieTitle" size="18"> <input type="checkbox" name="formUpdate[]" value="Yes" />
Year		<input type="text" name="new_Year" size="18"> <input type="checkbox" name="formUpdate[]" value="Yes" />
Gross Earning	<input type="text" name="new_Gross" size="18"> <input type="checkbox" name="formUpdate[]" value="Yes" />
Synoposis	<input type="text" name="new_Synopsis" size="18"> <input type="checkbox" name="formUpdate[]" value="Yes" />
</pre>
      
<input type="submit" value="update" name="updatesubmit"></p>
</form>

<p> Delete an entry based on Movie ID: </p>

<form method="POST" action="movies.php">
<!--refresh page when submit-->

<pre>
Movies ID	<input type="text" name="del_MovieID" size="18">
</pre>

<input type="submit" value="delete" name="deletesubmit"></p>
</form>

<?php

//this tells the system that it's no longer just parsing 
//html; it's now parsing PHP

$mylogin = "user";
$mypassword = "password";

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_j6t7", "a67221069", "ug");
//$db_conn = OCILogon($mylogin, $mypassword, "ug");

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For OCIParse errors pass the       
		// connection handle
		echo htmlentities($e['message']);
		$success = False;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		echo htmlentities($e['message']);
		$success = False;
	} else {

	}
	return $statement;

}

function executeBoundSQL($cmdstr, $list) {
	/* Sometimes a same statement will be excuted for severl times, only
	 the value of variables need to be changed.
	 In this case you don't need to create the statement several times; 
	 using bind variables can make the statement be shared and just 
	 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

	global $db_conn, $success;
	$statement = OCIParse($db_conn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}

	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			//echo $val;
			//echo "<br>".$bind."<br>";
			OCIBindByName($statement, $bind, $val);
			unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

		}
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
	}

}

function printResult($result) { //prints results from a select statement
	echo "<br>Got data from table Movies:<br>";
	//echo "<table>";
	//echo "<table width='600' >";
	echo "<table border='1' bordercolor='#000000' style='background-color:#FFFFFF' width='600' cellpadding='3' cellspacing='3'>";
	echo "<tr>
			<th>ID</th>
			<th>Title</th>
			<th>Year</th>
			<th>Gross Earnings</th>
			<th>Synopsis</th>
		</tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		//echo "<tr><td>" . $row["NID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]" 
		echo 
		"<tr><td>". 
		$row[0]. 
		"</td><td>".
		$row[1].
		"</td><td>".
		$row[2]. 
		"</td><td>".
		$row[3].
		"</td><td>".
		$row[4].
		"</td></tr>";		
	}
	echo "</table>";

}

// Connect Oracle...
if ($db_conn) {

	if (array_key_exists('reset', $_POST)) {
		// Drop old table...
		echo "<br> dropping table <br>";
		executePlainSQL("Drop table Movies");

		// Create new table...
		echo "<br> creating new table <br>";
		executePlainSQL("create table Movies (mid number, title varchar2(30), year number, gross number, synopsis varchar2(30), primary key (mid))");
		OCICommit($db_conn);

	} 
	else if (array_key_exists('insertsubmit', $_POST)) 
	{
		//Getting the values from user and insert data into the table
		//echo "In Here <br>";
		$tuple = array (
			":bind1" => $_POST['insMovieID'],
			":bind2" => $_POST['insMovieTitle'],
			":bind3" => $_POST['insYear'],
			":bind4" => $_POST['insGross'],
			":bind5" => $_POST['insSynopsis']
		);
		$alltuples = array (
			$tuple
		);
		executeBoundSQL("insert into Movies values (:bind1, :bind2, :bind3, :bind4, :bind5)", $alltuples);
		OCICommit($db_conn);

	} 
	else if (array_key_exists('updatesubmit', $_POST)) 
	{
		// Update tuple using data from user
		$tuple = array (
			":bind1" => $_POST['index_MovieID'],
			":bind2" => $_POST['new_MovieTitle'],
			":bind3" => $_POST['new_Year'],
			":bind4" => $_POST['new_Gross'],
			":bind5" => $_POST['new_Synopsis']
		);
		$alltuples = array (
			$tuple
		);			
	
		$updates = $_POST['formUpdate'];
		
		if($updates[0]=='Yes') executeBoundSQL("update Movies set title=:bind2 where mid=:bind1", $alltuples);
		if($updates[1]=='Yes') executeBoundSQL("update Movies set year=:bind3 where mid=:bind1", $alltuples);
		if($updates[2]=='Yes') executeBoundSQL("update Movies set gross=:bind4 where mid=:bind1", $alltuples);
		if($updates[3]=='Yes') executeBoundSQL("update Movies set synoposis=:bind5 where mid=:bind1", $alltuples);
		
		//executeBoundSQL("update Movies set name=:bind2 where name=:bind1", $alltuples);
		OCICommit($db_conn);

	} 
	else if (array_key_exists('deletesubmit', $_POST)) 
	{	
		// Inserting data into table using bound variables
		$tuple = array (
			":bind1" => $_POST['del_MovieID']
		);
		
		$alltuples = array (
			$tuple
		);
		executeBoundSQL("delete from Movies where mid=:bind1", $alltuples); //the function takes a list of lists
		
		OCICommit($db_conn);
	}

	if ($_POST && $success) 
	{
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		//echo "In here\n";
		header("location: movies.php");
	} 
	else 
	{
		// Select data...
		//echo "In here\n";
		$result = executePlainSQL("select * from Movies");
		printResult($result);
	}

	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect<br>";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}

/* OCILogon() allows you to log onto the Oracle database
     The three arguments are the username, password, and database
     You will need to replace "username" and "password" for this to
     to work. 
     all strings that start with "$" are variables; they are created
     implicitly by appearing on the left hand side of an assignment 
     statement */

/* OCIParse() Prepares Oracle statement for execution
      The two arguments are the connection and SQL query. */
/* OCIExecute() executes a previously parsed statement
      The two arguments are the statement which is a valid OCI
      statement identifier, and the mode. 
      default mode is OCI_COMMIT_ON_SUCCESS. Statement is
      automatically committed after OCIExecute() call when using this
      mode.
      Here we use OCI_DEFAULT. Statement is not committed
      automatically when using this mode */

/* OCI_Fetch_Array() Returns the next row from the result data as an  
     associative or numeric array, or both.
     The two arguments are a valid OCI statement identifier, and an 
     optinal second parameter which can be any combination of the 
     following constants:

     OCI_BOTH - return an array with both associative and numeric 
     indices (the same as OCI_ASSOC + OCI_NUM). This is the default 
     behavior.  
     OCI_ASSOC - return an associative array (as OCI_Fetch_Assoc() 
     works).  
     OCI_NUM - return a numeric array, (as OCI_Fetch_Row() works).  
     OCI_RETURN_NULLS - create empty elements for the NULL fields.  
     OCI_RETURN_LOBS - return the value of a LOB of the descriptor.  
     Default mode is OCI_BOTH.  */
?>

