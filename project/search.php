<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Search</title>
	<link rel="stylesheet" href="musicsheet.css">
	<link rel="stylesheet" href="result.css">
	<link rel="stylesheet" href="https://webfonts.omenad.net/fonts.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        function handleClick(sheet) {
            console.log(sheet);
            var decodedSheet = decodeURIComponent(sheet);

            decodedSheet = decodedSheet.replace(/&lt;/g, '<').replace(/&gt;/g, '>');

            // Replace &quot; with ""
            decodedSheet = decodedSheet.replace(/&quot;/g, '"');

            // Replace + with " "
            decodedSheet = decodedSheet.replace(/\+/g, ' ');

            console.log(decodedSheet);
            // console.log(cleanedSheet);
            sheetwork(decodedSheet);
        }
		
		function sheetwork(sheet) {
            //var myElement = document.getElementById("result");
            // Set the display property to "none"
            //myElement.style.display = "none";
            var myElement2 = document.getElementById("myDiv2");
            //myElement2.style.display = "n";
            myElement2.classList.add("musicsheet"); // Apply the CSS class
            var xmlData = sheet; // Paste the provided XML data here
            var parser = new DOMParser();
            var xmlDoc = parser.parseFromString(xmlData, "text/xml");
            console.log(xmlDoc);
            if (xmlDoc !== null) {
                // var title = xmlDoc.querySelector("TITLE").textContent; 
                var lines = xmlDoc.getElementsByTagName("LINE");
                var table = document.createElement("table");
                table.classList.add("music-sheet");

                
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i];
                    var row = table.insertRow(i);
                    var cols = line.getElementsByTagName("COL");
                    for (var j = 0; j < cols.length; j++) {
                        var content = cols[j].getElementsByTagName("CONTENT")[0].textContent;
                        var cell = row.insertCell(j);
                        cell.innerHTML = content;
                    }
                }
                myElement2.innerHTML = ""; // Clear existing content
                myElement2.appendChild(table);
            } else {
                console.log("Error parsing XML data.");
            }
        }


    
    </script>
</head>
<body>
	<h3>Search in Bhatkhande Dataset</h3>
  <form method="get" action="search.php">
	<div class="tableRow">
		<p>
			<label for="usersearch">Input your search phrase here:</label><br />
		</p>
		<p>
			<input type="text" id="usersearch" name="usersearch" /><br />
		</p>
		<p>
			<input type="submit" name="submit" value="Search" />
		</p>
	</div>
    
    
    
  </form>
  <h3>Search Results</h3>
	<div class="result">
<?php

	// Grab the search keywords from the URL using GET
	$user_search = $_GET['usersearch'];
	
	// Calculate pagination information
	$cur_page = isset($_GET['page']) ? $_GET['page'] : 1;
	$results_per_page = 10;  // number of results per page
	$skip = (($cur_page - 1) * $results_per_page);
	
	// connect to the database
	require_once('connectvars.php');
	include_once 'load.php';
	

	use BaseXClient\BaseXException;
	use BaseXClient\Session;
	

	$session = new Session(DB_HOST, DB_PORT, DB_USER, DB_PASSWORD);
	 
	// Query to get the total results 
	$query = build_query($user_search);
	$result = $session->query($query) or die("Error");
	
	$queryArray = iterator_to_array($result) or die("Error");
	$total = count($queryArray);
	$num_pages = ceil($total / $results_per_page);
	
	$suffix = "for \$filtered at \$count in subsequence(\$total_result, " . $skip + 1 . ", " . $results_per_page . ") return \$filtered";
	$query = "let \$total_result := " . $query . " " . $suffix;
	$result = $session->query($query) or die("Error");
	
	$queryArray = iterator_to_array($result) or die("Error");
	$titlesarray = [];
			// $raagsarray = [];
			// $taalsarray = [];
    $sheetarray = [];
	$k = 0;			
	for ($i = 0; $i < count((array)$queryArray); $i = $i + 2) {
		$titlesarray[$k] = $queryArray[$i];
		$sheetarray[$k] = $queryArray[$i + 1];
		$val = htmlspecialchars($sheetarray[$k], ENT_QUOTES);
		$value = urlencode($val);
		?>
		
			<div class="tableRow"><p>Title: <a href="#" onclick="handleClick('<?php echo $value; ?>')">
			<?php
				echo $titlesarray[$k] . "</a></p></div>";				
			
			?>
		<?php
		$k++;
	}
	
	?>
	
		</div>
	<br>
	<div class="musicsheet ome-bhatkhande-hindi" id="myDiv2">
		
    </div>
	<?php
	
	// Generate navigational page links if we have more than one page
	if ($num_pages > 1) {
		echo '<br><div class="pagelinks">' . generate_page_links($user_search, $cur_page, $num_pages) . "</div>";
	}

	$session->close();


	// This function builds a search query from the search keywords
	function build_query($user_search) {
		require_once('musicvars.php');
		$components = explode(" ", $user_search);
		$query_str = "";
		
		if(in_array($components[0], $attribute_set) && in_array($components[1], $operator_set)) {
			$attribute = $components[0];
			$operator = $components[1];
			$value = $components[2];
			$path = "";
			
			//$value = str_replace("'", "", $value);
			$j = 0;
			$path_values = array_values($paths);
			
            while ($j < sizeof($path_values)) {
                if (in_array($attribute, $path_values[$j])) {
                    $path = array_search($path_values[$j], $paths);
                    break;
                }
                ++$j;
            }
			
			$clause = "\$song/{$path}/{$attribute}/text() {$operator} \"{$value}\"";
			
			$query_str = "for \$song in collection(\"musics\")/swarlipi
                  let \$title := \$song/INFO / TITLE / text ()
                  let \$taal := \$song/TAAL/TAAL_NAME/ text ()
                  let \$raag := \$song/RAAG/RAAG_NAME/ text ()
                  let \$sheet := \$song/SHEET/LINES 
                where {$clause} 
                return (\$title, \$sheet)";
			
		}
		
		return $query_str;
	}
	
	// This function builds navigational page links based on the current page and the number of pages
	function generate_page_links($user_search, $cur_page, $num_pages) {
		$page_links = '';
		
		// If this page is not the first page, generate the "previous" link
		if ($cur_page > 1) {
		  $page_links .= '<p><a href="' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&page=' . ($cur_page - 1) . '"><-</a></p> ';
		}
		else {
		  $page_links .= '<p><- </p>';
		}

		// Loop through the pages generating the page number links
		for ($i = 1; $i <= $num_pages; $i++) {
		  if ($cur_page == $i) {
			$page_links .= '<p> ' . $i . '</p>';
		  }
		  else {
			$page_links .= '<p><a href="' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&page=' . $i . '"> ' . $i . '</a></p>';
		  }
		}

		// If this page is not the last page, generate the "next" link
		if ($cur_page < $num_pages) {
		  $page_links .= '<p><a href="' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&page=' . ($cur_page + 1) . '">-></a></p>';
		}
		else {
		  $page_links .= '<p> -></p>';
		}

		return $page_links;
	}


	


?>

</body>
</html>