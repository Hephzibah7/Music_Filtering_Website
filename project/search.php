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
	
	$result = $session->query($query);
	
	$queryArray = iterator_to_array($result) ;
	$total = count($queryArray);
	echo $total;
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
    function test_input($data)
    {
        $data = trim($data);
        //$data = stripslashes($data);
        //$data = htmlspecialchars($data);
        return $data;
    }

    function splitOnAnd($string, $conjunctions)
    {

        $array = explode(" and ", $string); // returns an array of queries that were separated by and in $input
        array_push($conjunctions, "and");
        return $array;
    }
    function formPaths($paths, $attributes)
    {
        $attr_path = [];
        $path_values = array_values($paths);
        for ($i = 0; $i < sizeof($attributes); ++$i) {
            $j = 0;
            while ($j < sizeof($path_values)) {
                if (in_array($attributes[$i], $path_values[$j])) {
                    array_push($attr_path, array_search($path_values[$j], $paths));
                    break;
                }
                ++$j;
            }
        }
        return $attr_path;
    }


	// This function builds a search query from the search keywords
	function build_query($user_search) {
		try {
	
				$input = test_input($user_search);
	
				// $attribute_set = ["TITLE", "RAAG_NAME", "THAAT", "NO_OF_LINES", "MAATRA", "TAAL", "YEAR", "TAAL_NAME"];
				$attribute_set = [
					"TITLE",
					"COMPOSER",
					"NOTATION-SYSTEM",
					"YEAR",
					"GENRE",
					"ADDITIONAL",
					"TAAL_NAME",
					"BIBHAGA",
					"MAATRA",
					"AVARTANA",
					"BEAT_PATTERN",
					"ALTERNATE_BEAT_PATTERN",
					"TAALI_COUNT",
					"KHAALI_COUNT",
					"TAALI_INDEX",
					"KHAALI_INDEX",
					"RAAG_NAME",
					"THAAT",
					"AROHANA",
					"AVAROHANA",
					"VADI",
					"SAMVADI",
					"JAATI",
					"PAKAD",
					"NO_OF_LINES",
					"TOTAL_LINES",
					"LINES"
				];
				$conjunction_set = ["and", "or"];
				$operator_set = ["STARTS_WITH", "=", "!=", ">", "<", "ENDS_WITH"];
	
				$attributes = [];
				$operators = [];
				$conjunctions = [];
				$values = []; // this will store the values of the attributes on which the operation will be performed
	
	
				// STEP 1: EXTRACT ALL VALUES WITHIN QUOTES FROM INPUT STR, REPLACE VALUES WITH NULL STR
				$i = 0;
				while ($i < strlen($input)) {
	
					$i = strpos($input, "\""); // starting " index 
					if (!$i) {
						break;
					} else {
						$j = strpos($input, "\"", $i + 1); // ending " index
						$value = substr($input, $i + 1, $j - $i - 1); // value str b/w "..."
	
						if (is_numeric($value)) {
							array_push($values, number_format($value));
						} else {
							array_push($values, $value);
						}
						$input = str_replace("\"{$value}\"", "", $input); // replacing the stored "value" with null in the input str
					}
				}
	
	
				// output:
				// values = [ami chini go chini, 15]
				// input => title = "" and maatra > ""
	
	
	
				// STEP 2: BREAK STRING ON AND   
				array_push($conjunctions, "and");
				$queries = splitOnAnd($input, $conjunctions);
	
	
				// STEP 3: GET THE ATTRIBUTES AND OPERATORS FROM EACH $queries[i]
				// 1. separate by space, put in array
				foreach ($queries as $q) {
					$input_terms = explode(" ", $q);
					// input_terms = [title, =] or [maatra, >]
					foreach ($input_terms as $term) {
						if (in_array($term, $attribute_set)) {
							// $i = array_search($term, $input_terms);
	
							array_push($attributes, $term);
						} elseif (in_array($term, $operator_set)) {
							array_push($operators, $term);
						}
					}
				}
				// result => attributes = [title, maatra], operators = [=, >], values = [chini go chini, 15], conjunctions = [and]
	
	
				// STEP 4: FORMING THE XQUERY
				// 4.1: form xpaths for each attribute to be searched           
				$paths = array(
					"INFO" => ["TITLE", "YEAR", "AUTHOR", "LYRIC_LANGUAGE", "NOTATION_SYSTEM", "NOTE_FONT_NAME", "LYRIC_FONT_NAME", "COMPOSITION_YEAR", "GENRE"],
					"TAAL" => ["TAAL_NAME", "BIBHAGA", "MAATRA", "AVARTANA", "BEAT_PATTERN", "TAALI_COUNT", "KHAALI_COUNT", "TAALI_INDEX", "KHAALI_INDEX"],
					"RAAG" => ["RAAG_NAME", "THAAT", "AROHANA", "AVAROHANA", "VADI", "SAMVADI", "JAATI", "PAKAD"]
				);
	
				$xpaths = formPaths($paths, $attributes);
				// xpaths = [info, taal]
	
	
				// 4.2: forming the xquery using attributes[], operators[], conjunctions[], values[]
				// 4.2.1: form all the where clauses depending on sizeof(attributes)
				$where_clauses = "";
				$i = $j = 0;
	
				if (sizeof($conjunctions) % sizeof($attributes) == 1) {
					for ($i = 0; $i < sizeof($attributes); ++$i) {
						$clause = "";
						if (is_numeric($values[$i])) {
							$clause = "\$song/{$xpaths[$i]}/{$attributes[$i]} {$operators[$i]} {$values[$i]}"; // num
						} else {
							$clause = "\$song/{$xpaths[$i]}/{$attributes[$i]}/text() {$operators[$i]} \"{$values[$i]}\""; // str
						}
	
						if ($i == 0) {
							$where_clauses = $clause . " and ";
						} elseif ($i < sizeof($conjunctions)) {
							$where_clauses .= $clause . " and ";
						} else {
							$where_clauses .= $clause;
						}
					}
				} else {
					echo "invalid search input";
				}
	
			   
				$query_str = "for \$song in collection(\"musics\")/swarlipi
					  let \$title := \$song/INFO / TITLE / text ()
					  let \$taal := \$song/TAAL/TAAL_NAME/ text ()
					  let \$raag := \$song/RAAG/RAAG_NAME/ text ()
					  let \$sheet := \$song/SHEET/LINES 
					where {$where_clauses} 
					return (\$title, \$sheet)";
	 
				return $query_str;
			
		} catch (BaseXException $e) {
			// print exception
			print $e->getMessage();
		}
	
		
	}
		// echo $user_search;
		// require_once('musicvars.php');
		// $components = explode(" ", $user_search);
		// $query_str = "";
		
		// if(in_array($components[0], $attribute_set) && in_array($components[1], $operator_set)) {
		// 	$attribute = $components[0];
		// 	$operator = $components[1];
		// 	$value = $components[2];
		// 	$path = "";
			
		// 	//$value = str_replace("'", "", $value);
		// 	$j = 0;
		// 	$path_values = array_values($paths);
			
        //     while ($j < sizeof($path_values)) {
        //         if (in_array($attribute, $path_values[$j])) {
        //             $path = array_search($path_values[$j], $paths);
        //             break;
        //         }
        //         ++$j;
        //     }
			
		// 	$clause = "\$song/{$path}/{$attribute}/text() {$operator} \"{$value}\"";
			
		// 	$query_str = "for \$song in collection(\"musics\")/swarlipi
        //           let \$title := \$song/INFO / TITLE / text ()
        //           let \$taal := \$song/TAAL/TAAL_NAME/ text ()
        //           let \$raag := \$song/RAAG/RAAG_NAME/ text ()
        //           let \$sheet := \$song/SHEET/LINES 
        //         where {$clause} 
        //         return (\$title, \$sheet)";
			
		// }
		
		// return $query_str;
	
	
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