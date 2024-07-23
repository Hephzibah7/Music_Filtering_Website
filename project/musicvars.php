<?php
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
                "RAAG" => ["RAAG_NAME", "THAAT", "AROHANA", "AVAROHANA", "VADI", "SAMVADI", "JAATI", "PAKAD"]);
?>