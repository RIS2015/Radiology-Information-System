<?php
  	 include_once("PHPconnectionDB.php");
    function search_keyword($keyWord, $sdate, $edate, $sortBy, $orderBy){        	
        //establish connection
        $conn = connect();
        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        //echo $sdate;
			//$sdate = DateTime::createFromFormat('Y-m-j', $sdate);
       	//$edate = DateTime::createFromFormat('Y-m-j', $edate);
					
			//implement KEYWORD /compsci/webdocs/zioueche/web_doc
			if (!$keyWord){
				$sql = 'SELECT p.first_name, p.last_name, test_date, test_type, r.record_id as rid
						FROM radiology_record r, persons p
						WHERE p.person_id = r.patient_id';
				}
			else {$sql = 'SELECT 6*(score(1)+score(2))+3*score(3)+score(4) as rank, p.first_name, p.last_name, test_date, test_type, r.record_id as rid
						FROM radiology_record r, persons p
						WHERE p.person_id = r.patient_id 
						AND (contains(first_name, \'%s\', 1)>0 
						OR contains(last_name, \'%s\', 2) > 0 
						OR contains(diagnosis, \'%s\', 3) > 0 
						OR contains(description, \'%s\', 4) > 0
						)';}
			
			if ($sdate) $sql =' '.$sql.' AND test_date >= \''.$sdate.'\'';		
			if ($edate) $sql = ' '.$sql.' AND test_date <= \''.$edate.'\'';
			
			if ($sortBy != "0") $rest_of_query = ' ORDER BY (6*(score(1)+score(2))+3*score(3)+score(4))';
			else $rest_of_query = 'order by test_date';
			
			if (!$orderBy != "0") $rest_of_query = $rest_of_query." ASC";
			else $rest_of_query = $rest_of_query." DESC";
			
			$sql2 = sprintf($sql, $keyWord,$keyWord,$keyWord,$keyWord);
			$sql = $sql2.$rest_of_query;	

			?>
			<div style="display: inline-block; height:600px; overflow:auto;">
			<table border="1" class="clickable-row" >
				<th align='center' valign='middle' width='100'>Result</th>
				<th align='center' valign='middle' width='100'>First Name</th>
				<th align='center' valign='middle' width='100'>Last Name</th>
				<th align='center' valign='middle' width='100'>Test Date</th>
				<th align='center' valign='middle' width='100'>Test Type</th>
				<th align='center' valign='middle' width='100'>Record</th>
				<th align='center' valign='middle' >Images</th>

		<?php
			//prep connection
		  $stid = oci_parse($conn, $sql);
        //Execute a statement returned from oci_parse()
        $res = oci_execute($stid);
        //if error, retrieve the error using the oci_error() function & output an error message
        if (!$res) {
            $err = oci_error($stid);
            echo htmlentities($err['message']);
        } else {
        		$pos = 0;
        		//$part1 = $_SERVER['REQUEST_URI'];
        		//$part2 = $_SERVER['QUERY_STRING'];
        		//echo $_SERVER['REQUEST_URI'];
            while ($record = oci_fetch_array($stid)) {
            	$pos += 1;	
            ?>
            
					<tr onMouseover="this.bgColor='#ADD8E6'" onMouseout="this.bgColor='#FFFFFF'">
					<td align='center' valign='middle'<?php echo 'onclick="selectRecord(\''.$record["RID"].'\')"'; ?>
	        				><?php echo $pos ?></td>

               <td <?php echo 'onclick="selectRecord(\''.$record["RID"].'\')"'; ?>
	        				><?php echo $record["FIRST_NAME"] ?></td>
               <td <?php echo 'onclick="selectRecord(\''.$record["RID"].'\')"'; ?>
	        				><?php echo $record["LAST_NAME"] ?></td>
               <td align='center' valign='middle' <?php echo 'onclick="selectRecord(\''.$record["RID"].'\')"'; ?>
	        				><?php echo $record["TEST_DATE"] ?></td>
               <td <?php echo 'onclick="selectRecord(\''.$record["RID"].'\')"'; ?>
	        				><?php echo $record["TEST_TYPE"] ?></td>
               <td <?php echo 'onclick="selectRecord(\''.$record["RID"].'\')"'; ?>
	        				><?php echo $record["RID"] ?></td>
               <?php
              
               $sql = 'SELECT pc.thumbnail, pc.image_id 
               			FROM pacs_images pc
               			WHERE pc.record_id = '.$record["RID"];
               
               $test = oci_parse($conn, $sql);
               $result = oci_execute($test);
               if (!$result) {
            		$err = oci_error($stid);
            		echo htmlentities($err['message']);
						
       			 } else{	
       			 //echo $sql."<br>";		
       			 ?>	
					  		<td> 	 
       			 <?php if ($row = oci_fetch_array($test)){
       			 	while ($row){
       				 ?>
       			 		<a href="index.jpeg" target="_Blank">
    						<img src="index.jpeg" alt="Put something more exciting here." width="32" height="32" />
							</a>
							<a href="Ball_python_lucy.JPG" target="_Blank" >
							<img src="Ball_python_lucy.JPG" alt="Put something more exciting here." width="32" height="32" />


						<?php
						$row = oci_fetch_array($test);
							}}
					else{
						echo "No images available for viewing";					
						}
							?>
					</td>
               
               </tr>
               <?php
            }
            

            }
            ?>
            
             </table>
             </div>
             <?php
        // Free the statement identifier when closing the connection
        oci_free_statement($stid);
        oci_close($conn);
    }}

?>

<script language="JavaScript">

function openWin(img) {
	window.open(img);

}
function selectRecord(RID) {
		document.getElementById('rid').value = RID;
		//alert("HI");
		document.forms['search'].submit();
	}
</script>