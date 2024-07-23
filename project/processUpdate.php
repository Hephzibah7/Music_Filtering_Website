<?php
include_once 'load.php';

use BaseXClient\BaseXException;
use BaseXClient\Session;

try {
	$attribute = "";
    $operator = "";
	$value = "";
	$path = "";
	
	$session = new Session("localhost", 1984, "admin", "admin");
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
			
	$operator_set = ["STARTS_WITH", "=", "!=", ">", "<", "ENDS_WITH"];
	
	$paths = array(
                "INFO" => ["TITLE", "YEAR", "AUTHOR", "LYRIC_LANGUAGE", "NOTATION_SYSTEM", "NOTE_FONT_NAME", "LYRIC_FONT_NAME", "COMPOSITION_YEAR", "GENRE"],
                "TAAL" => ["TAAL_NAME", "BIBHAGA", "MAATRA", "AVARTANA", "BEAT_PATTERN", "TAALI_COUNT", "KHAALI_COUNT", "TAALI_INDEX", "KHAALI_INDEX"],
                "RAAG" => ["RAAG_NAME", "THAAT", "AROHANA", "AVAROHANA", "VADI", "SAMVADI", "JAATI", "PAKAD"]
            );
	
	if ($_POST["query"]) {
		$input = trim($_POST["query"]);
		
		// get the 3 components of the query [attribute] [operator] [value]
		$components = explode(" ", $input);		
		
		if(in_array($components[0], $attribute_set) && in_array($components[1], $operator_set)) {
			$attribute = $components[0];
			$operator = $components[1];
			$value = $components[2];
			$path = "";
			
			$value = str_replace("\"", "", $value);
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
                return (\$title, \$taal, \$raag, \$sheet)";
			
			$query = $session->query($query_str);
			$queryArray = iterator_to_array($query);
            
			$titlesarray = [];
			$raagsarray = [];
			$taalsarray = [];
            $sheetarray = [];
			$k = 0;
			
            $queryArray = iterator_to_array($query);
			
			for ($i = 0; $i < count((array)$queryArray); $i = $i + 4) {
				$titlesarray[$k] = $queryArray[$i];
				$raagsarray[$k] = $queryArray[$i + 2];
				$taalsarray[$k] = $queryArray[$i + 1];
				$sheetarray[$k] = $queryArray[$i + 3];
				$k++;
			}
			
		}
	}
	$session->close();
} catch (BaseXException $e) {
        print $e->getMessage();
}

?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Result</title>
    <link rel="stylesheet" href="resultPage.css" />
    <link rel="stylesheet" href="music_sheet.css">
    <link rel="stylesheet" href="https://webfonts.omenad.net/fonts.css">

    <style>

    </style>
</head>

<body>
	<div id="result_block">
		<p><strong>Search Results:</strong></p>
		<?php
			for ($j = 0; $j < count((array)$titlesarray); $j++) {
				
				$val = htmlspecialchars($sheetarray[$j], ENT_QUOTES);
				$value = urlencode($val);
		?>
		<table id="result">
			<tr><td  colspan="2" class="result_title">Title: <a href="#" onclick="handleClick('<?php echo $value; ?>')">
			<?php
				echo $titlesarray[$j] . "</a></td></tr>";
				echo "<tr><td class=\"result_raag\"> Raag: " . $raagsarray[$j] . "</td>" .
				"<td class=\"result_taal\"> Taal: " . $taalsarray[$j] . "</td></tr>";
				
			
			?>
		</table>
		<?php
			}
		?>
    </div>
        
    
    <table class="list2 music-sheet ome-bhatkhande-hindi" id="myDiv2">

    </table>


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
            var myElement = document.getElementById("result_block");
            // Set the display property to "none"
            myElement.style.display = "none";
            var myElement2 = document.getElementById("myDiv2");
            myElement2.style.display = "block";
            myElement2.classList.add("music-sheets"); // Apply the CSS class
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
</body>

</html>