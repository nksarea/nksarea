<html>
	<head>
		<script>
			
		</script>
	</head>
	<body>
		<span>
			Enter SQL-Command below. Please use right syntax!
		</span>
		<form method="post">
			<input type="text" name="sql-command"/>
			<input type="hidden" name="run_sql" value="1"/>
			<input type="submit">
		</form>
		<span>
			Enter tables you want to backup. Separate more then one table with ",". "*" or "" means BACKUP ALL THE THINGS. 
		</span>
		<form method="post">
			<input type="text" name="tables"/>
			<input type="hidden" name="get_db" value="1"/>
			<input type="submit"/>
		</form>
		<br/><br/>
<?php
/**
 * Get all tables of one database
 * 
 * @author Muffin <muffin@tormail.net>
 */
/*
define('SYSTEM_TABLES_ALL', '*');
define('HOST', 'localhost');
define('USER', 'web208');
define('PASS', 'Perser739!');
define('DB', 'usr_web208_1');

if(isset($_POST['run_sql']) && isset($_POST['sql-command'])){
	$myc = new mysqli(HOST, USER, PASS);
	if (!$myc) {
		echo "Nooope, Chuck Testa! Nd d mysql verbendig hed gfailed: Host: '$host', User: '$user', Pass: '$pass'";
	}
	if (!$myc->select_db(DB)) {
		echo "Nooope, Chuck Testa! Nd d Datebank geds ned: Datebank: '$db'";
	}
	if(!$myc->query($_POST['sql-command'])){
		echo "Nooope, Chuck Testa! Es hed ned klappet D:";
	}
}

else if(isset($_POST['get_db']) && isset($_POST['tables'])){
	echo backup_tables($_POST['tables']);
}

function run_query($myc, $query) {
	if(!$result){
		echo "Nooope, Chuck Testa! Es hed ned klappet D:";
	}
	if (get_class($result) !== 'mysqli_result'){
	
	}
}

function backup_tables($tables = SYSTEM_TABLES_ALL) {
	if ($myc) {
		return "Nooope, Chuck Testa! Nd d mysql verbendig hed gfailed: Host: '$host', User: '$user', Pass: '$pass'";
	}
	$return = "";

	if ($tables == SYSTEM_TABLES_ALL || !$tables) {
		$tables = array();
		$result = $myc->query('SHOW TABLES');
		while ($row = $result->fetch_row()) {
			$tables[] = $row[0];
		}
	} else {
		if (is_array($tables))
			$tables = explode(',', $tables);
		if (is_array($tables))
			return "Nooope, Chuck Testa! Nd dini iigab esch en fail: '$tables'";
	}

	foreach ($tables as $table) {
		$result = $myc->query('SELECT * FROM ' . $table);
		if(!$result)
			continue;
		$return .= 'DROP TABLE ' . $table . ';';

		$row2 = $myc->query('SHOW CREATE TABLE ' . $table);
		$row2 = $row2->fetch_row();
		$return .= "\n\n" . $row2[1] . ";\n\n";

		while ($row = $result->fetch_row()) {
			$return.= 'INSERT INTO ' . $table . ' VALUES(';
			for ($j = 0; $j < $result->field_count; $j++) {
				$row[$j] = $myc->real_escape_string($row[$j]);
				$row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
				if (isset($row[$j])) {
					$return .= '"' . $row[$j] . '"';
				} else {
					$return .= '""';
				}
				if ($j < ($result->field_count - 1)) {
					$return.= ',';
				}
			}
			$return.= ");\n";
		}
		$return.="\n\n\n";
	}

	return $return;
}
 */
?>
	</body>
</html>
