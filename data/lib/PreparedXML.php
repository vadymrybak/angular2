<?php 

function getRowsFromText($text){
	$result = array();

	$text = str_replace("$", "&#36;" , $text);
	$text = str_replace("&", "&#38;" , $text);
	$text = str_replace("<br/>", " <br/>", $text);
	$text = str_replace("  ", " ", $text);
	$text = str_replace("<br/> <br/>", "<br/><br/>", $text);
	$text = preg_replace("/ {2,}/", " ", $text);
	$text = preg_replace("/\t/", "&#8195;", $text);

	//echo "<script>console.log('text', '".$text."');</script>" ;

	$temp_array = explode(" ", $text);

	$counter = 1;
	foreach ($temp_array as $value) {
		$item = "<row label=\"r$counter\" translateable=\"0\">$value</row>";
		array_push($result, $item);
		$counter++;
	}

	return implode("\n", $result);
}

function getCategoryIndex($category_string, $categories){

	foreach ($categories as $key => $value) {
		if (strtolower($value) == strtolower($category_string)){
			return $key;
		}
	}

	return "Category_not_found";
}

function getPreparedXML($template_xml, $json) 
{
	//echo "<script>console.log('json is: ', ".json_encode($json).");</script>";

	$myXML = simplexml_load_string($template_xml);
	$nodes = $myXML->xpath('//*[@label="dCategory"]'); 

	$array =  (array) $nodes[0];
	$categories = $array["row"];

	//Dump($categories);

	//print_r(getCategoryIndex("Snacks", $categories));
	//print_r(getCategoryIndex("In-aisle Cracker Indulgent/Premium", $categories));

	$ARRAY_OF_CATS_INDEXES = array();
	$ARRAY_OF_CAT_INDEXES = array();
	$q25_array  = array();
	$q32_array = array();
	$q33_array = array();
	$concept_names_array = array();
	$concept_image_array = array();
	$text_highlighter_array = array();
	$q25_cond = "";
	$q27_cond = "";
	$q28_cond = "";
	$q29_cond = "";
	$q30_cond = "";
	$q31_cond = "";
	$q32_cond = "";
	$q32_answer_options = array(
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array()
	);
	$q33_cond = "";
	$q33_answer_options = array(
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array()
	);
	$q29_answers_conds = array();
	for ($i=0; $i < 124; $i++) { 
		array_push($q29_answers_conds, "");
	}
	$concept_varieties = array(
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array(),
		array()
	);
	$wholeQ19_str = "";
	$Q19base = "
<select
  label=\"Q19Concept%%count%%En\"
  cond=\"dConcept.r%%count%% and (decLang=='english' or decLang!='canadian')\"
  optional=\"1\"
  translateable=\"0\"
  uses=\"hottext.3\">
  <title>What, if anything, do you <b>LIKE</b> or <b>DISLIKE</b> about this product?</title>
  <comment><i>Use the highlighter to select text in the content below. Change highlighters by selecting a different marker color.</i></comment>
  <choice label=\"ch1\">LIKE</choice>
  <choice label=\"ch2\">DISLIKE</choice>
  %%q19rows%%
</select>\n\n";


	$data = $json[1];
	$concept_array = $data->concept;
	//Dump($data->concept);

	$loop_iteration = 0;
	foreach ($concept_array as $value) {

		$question_bucket_array = $value->question_bucket;

		// getting names of all concepts
			array_push($concept_names_array, $value->concept_name);

		// getting images of all concepts
			array_push($concept_image_array, $value->concept_board);

		// getting indexes of all concepts
			array_push($ARRAY_OF_CATS_INDEXES, $value->conceptID);
			array_push($ARRAY_OF_CAT_INDEXES, getCategoryIndex($value->category, $categories));

		// getting text for highlighting exercise
			$highlighter = htmlspecialchars($value->descriptions);
			$highlighter = str_replace("\n", "<br/>",$value->descriptions);
			$highlighter = rtrim($highlighter, "<br/>");
			$highlighter = rtrim($highlighter, "\t");
			array_push($text_highlighter_array, str_replace("\\", "" , $highlighter));

		// Getting Q25 array
			$current_bucket_iteration = 0;
			$needToAdd = true;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "1") {
					$q25_cond = $q25_cond."dConcept.r".($loop_iteration+ 1 )." or ";
					$slave_options_array = explode(";", $qb->slave_options);
					//Dump($slave_options_array[0]);
					array_push($q25_array, count($slave_options_array));
					for ($i = 0; $i < 20; $i++) { 
						if ($i < count($slave_options_array))
							array_push($concept_varieties[$loop_iteration], str_replace("&", "&#38;" , $slave_options_array[$i]));
						else
							array_push($concept_varieties[$loop_iteration], "NA");
					}
					$needToAdd = false;
				}
				else {
					if ($current_bucket_iteration == count($question_bucket_array) && $needToAdd)
						array_push($q25_array, "0");
				}
			}
			
		// Getting Q32 array
			$current_bucket_iteration = 0;
			$needToAdd = true;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "7") {
					$slave_options_array = explode(";", $qb->slave_options);
					$q32_cond = $q32_cond."dConcept.r".($loop_iteration + 1 )." or ";
					array_push($q32_array, count($slave_options_array));

					for ($i = 0; $i < 6; $i++) { 
						if ($i < count($slave_options_array))
							array_push($q32_answer_options[$loop_iteration], str_replace("&", "&#38;" , $slave_options_array[$i]));
						else
							array_push($q32_answer_options[$loop_iteration], "NA");
					}

					$needToAdd = false;
				}
				else {
					if ($current_bucket_iteration == count($question_bucket_array) && $needToAdd)
						array_push($q32_array, "0");
				}
			}

		// Getting Q33 array
			$current_bucket_iteration = 0;
			$needToAdd = true;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "8") {
					$slave_options_array = explode(";", $qb->slave_options);
					array_push($q33_array, count($slave_options_array));
					$q33_cond = $q33_cond."dConcept.r".($loop_iteration + 1 )." or ";

					for ($i = 0; $i < 10; $i++) { 
						if ($i < count($slave_options_array))
							array_push($q33_answer_options[$loop_iteration], str_replace("&", "&#38;" , $slave_options_array[$i]));
						else
							array_push($q33_answer_options[$loop_iteration], "NA");
					}

					$needToAdd = false;
				}
				else {
					if ($current_bucket_iteration == count($question_bucket_array) && $needToAdd)
						array_push($q33_array, "0");
				}
			}

		// Checking for SB_ID 2
			$current_bucket_iteration = 0;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "2") {
					$q27_cond = $q27_cond."dConcept.r".($loop_iteration+ 1 )." or ";
				}
			}

		// Checking for SB_ID 3
			$current_bucket_iteration = 0;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "3") {
					$q28_cond = $q28_cond."dConcept.r".($loop_iteration+ 1 )." or ";
				}
			}

		// Checking for SB_ID 4
			$current_bucket_iteration = 0;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "4") {
					$slave_options4 = explode(";", $qb->slave_options);
					foreach ($slave_options4 as $value) {
						$q29_answers_conds[$value - 1] = $q29_answers_conds[$value - 1]."dConcept.r".($loop_iteration + 1)." or ";
					}
					$q29_cond = $q29_cond."dConcept.r".($loop_iteration+ 1 )." or ";
				}
			}

		// Checking for SB_ID 5
			$current_bucket_iteration = 0;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "5") {
					$q30_cond = $q30_cond."dConcept.r".($loop_iteration + 1 )." or ";
				}
			}

		// Checking for SB_ID 6
			$current_bucket_iteration = 0;
			foreach ($question_bucket_array as $qb) {
				$current_bucket_iteration++;
				if ($qb->sb_id == "6") {
					$q31_cond = $q31_cond."dConcept.r".($loop_iteration + 1 )." or ";
				}
			}

		$loop_iteration++;
	}

	$cats_indexes_count = count($ARRAY_OF_CATS_INDEXES);
	for ($i=0; $i < 20 - count($cats_indexes_count); $i++) { 
		array_push($ARRAY_OF_CATS_INDEXES, "0");
	}

	$cat_indexes_count = count($ARRAY_OF_CAT_INDEXES);
	for ($i=0; $i < 20 - count($cat_indexes_count); $i++) { 
		array_push($ARRAY_OF_CAT_INDEXES, "0");
	}

	$q25_array_count = count($q25_array);
	for ($i=0; $i < 20 - count($q25_array_count); $i++) { 
		array_push($q25_array, "0");
	}

	$q32_array_count = count($q32_array);
	for ($i=0; $i < 20 - count($q32_array_count); $i++) { 
		array_push($q32_array, "0");
	}

	$q33_array_count = count($q33_array);
	for ($i=0; $i < 20 - count($q33_array_count); $i++) { 
		array_push($q33_array, "0");
	}

	$concept_names_array_count = count($concept_names_array);
	for ($i=0; $i < 20 - $concept_names_array_count; $i++) { 
		array_push($concept_names_array, "NA");
	}

	$concept_image_array_count = count($concept_image_array);
	for ($i=0; $i < 20 - $concept_image_array_count; $i++) { 
		array_push($concept_image_array, "NA");
	}

	for ($i = 0; $i < count($text_highlighter_array); $i++) { 
		$wholeQ19_str = $wholeQ19_str.str_replace("%%count%%", $i + 1, str_replace("%%q19rows%%", getRowsFromText($text_highlighter_array[$i]), $Q19base));
	}

	for ($a = 0; $a < count($concept_varieties); $a++) {
		if (count($concept_varieties[$a]) == 0){
			for ($i=0; $i < 20; $i++) { 
				array_push($concept_varieties[$a], "NA");
			}
		}
	}

	for ($a = 0; $a < count($q32_answer_options); $a++) {
		if (count($q32_answer_options[$a]) == 0){
			for ($i=0; $i < 6; $i++) { 
				array_push($q32_answer_options[$a], "NA");
			}
		}
	}

	for ($a = 0; $a < count($q33_answer_options); $a++) {
		if (count($q33_answer_options[$a]) == 0){
			for ($i=0; $i < 10; $i++) { 
				array_push($q33_answer_options[$a], "NA");
			}
		}
	}

	$q25_cond = rtrim($q25_cond, " or ");
	$q27_cond = rtrim($q27_cond, " or ");
	$q28_cond = rtrim($q28_cond, " or ");
	$q29_cond = rtrim($q29_cond, " or ");
	$q30_cond = rtrim($q30_cond, " or ");
	$q31_cond = rtrim($q31_cond, " or ");
	$q32_cond = rtrim($q32_cond, " or ");
	$q33_cond = rtrim($q33_cond, " or ");

	foreach ($q29_answers_conds as &$value) {
		if ($value == "")
			$value = "0";
		$value = rtrim($value, " or ");
	}

	//Dump($q27_cond);

	$preparedXML = $template_xml;
	$preparedXML = str_replace("#%%ARRAY_OF_CAT_INDEXES%%", "cat_arr = [".implode(",", $ARRAY_OF_CAT_INDEXES)."]", $preparedXML);
	$preparedXML = str_replace("#%%ARRAY_OF_CATS_INDEXES%%", "cats_arr = [".implode(",", $ARRAY_OF_CATS_INDEXES)."]", $preparedXML);
	$preparedXML = str_replace("#%%Q25_ARRAY_CODES%%", "q25_array = [".implode(",", $q25_array)."]", $preparedXML);
	$preparedXML = str_replace("#%%Q32_ARRAY_CODES%%", "q32_array = [".implode(",", $q32_array)."]", $preparedXML);
	$preparedXML = str_replace("#%%Q33_ARRAY_CODES%%", "q33_array = [".implode(",", $q33_array)."]", $preparedXML);

	$preparedXML = str_replace("%%ConceptName1%%", $concept_names_array[0], $preparedXML);
	$preparedXML = str_replace("%%ConceptName2%%", $concept_names_array[1], $preparedXML);
	$preparedXML = str_replace("%%ConceptName3%%", $concept_names_array[2], $preparedXML);
	$preparedXML = str_replace("%%ConceptName4%%", $concept_names_array[3], $preparedXML);
	$preparedXML = str_replace("%%ConceptName5%%", $concept_names_array[4], $preparedXML);
	$preparedXML = str_replace("%%ConceptName6%%", $concept_names_array[5], $preparedXML);
	$preparedXML = str_replace("%%ConceptName7%%", $concept_names_array[6], $preparedXML);
	$preparedXML = str_replace("%%ConceptName8%%", $concept_names_array[7], $preparedXML);
	$preparedXML = str_replace("%%ConceptName9%%", $concept_names_array[8], $preparedXML);
	$preparedXML = str_replace("%%ConceptName10%%", $concept_names_array[9], $preparedXML);
	$preparedXML = str_replace("%%ConceptName11%%", $concept_names_array[10], $preparedXML);
	$preparedXML = str_replace("%%ConceptName12%%", $concept_names_array[11], $preparedXML);
	$preparedXML = str_replace("%%ConceptName13%%", $concept_names_array[12], $preparedXML);
	$preparedXML = str_replace("%%ConceptName14%%", $concept_names_array[13], $preparedXML);
	$preparedXML = str_replace("%%ConceptName15%%", $concept_names_array[14], $preparedXML);
	$preparedXML = str_replace("%%ConceptName16%%", $concept_names_array[15], $preparedXML);
	$preparedXML = str_replace("%%ConceptName17%%", $concept_names_array[16], $preparedXML);
	$preparedXML = str_replace("%%ConceptName18%%", $concept_names_array[17], $preparedXML);
	$preparedXML = str_replace("%%ConceptName19%%", $concept_names_array[18], $preparedXML);
	$preparedXML = str_replace("%%ConceptName20%%", $concept_names_array[19], $preparedXML);

	$preparedXML = str_replace("%%ConceptImage1%%", $concept_image_array[0], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage2%%", $concept_image_array[1], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage3%%", $concept_image_array[2], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage4%%", $concept_image_array[3], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage5%%", $concept_image_array[4], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage6%%", $concept_image_array[5], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage7%%", $concept_image_array[6], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage8%%", $concept_image_array[7], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage9%%", $concept_image_array[8], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage10%%", $concept_image_array[9], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage11%%", $concept_image_array[10], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage12%%", $concept_image_array[11], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage13%%", $concept_image_array[12], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage14%%", $concept_image_array[13], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage15%%", $concept_image_array[14], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage16%%", $concept_image_array[15], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage17%%", $concept_image_array[16], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage18%%", $concept_image_array[17], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage19%%", $concept_image_array[18], $preparedXML);
	$preparedXML = str_replace("%%ConceptImage20%%", $concept_image_array[19], $preparedXML);

	//$preparedXML = str_replace("<note>%%Q19%%</note>", $wholeQ19_str, $preparedXML);
	$preparedXML = preg_replace("/(<note.*?>%%Q19%%<\/note>)/", $wholeQ19_str, $preparedXML);


	for ($i = 0; $i < 20; $i++) { 
		for ($z = 0; $z < 10; $z++) { 
			$preparedXML = str_replace("%%Concept".($i + 1)."_Variety".($z + 1)."%%", $concept_varieties[$i][$z], $preparedXML);
		}
	}

	// Condition will be controlled if any rows are shown
	//$preparedXML = str_replace("%%SHOW_FOR_CONCEPTS_Q25%%", $q25_cond, $preparedXML);

	$preparedXML = str_replace("0 #%%SHOW_FOR_CONCEPTS_Q27%%", strlen($q27_cond) > 0 ? $q27_cond : "0", $preparedXML);
	$preparedXML = str_replace("0 #%%SHOW_FOR_CONCEPTS_Q28%%", strlen($q28_cond) > 0 ? $q28_cond : "0", $preparedXML);
	
	// Condition will be controlled if any rows are shown
	//$preparedXML = str_replace("%%SHOW_FOR_CONCEPTS_Q29%%", $q29_cond, $preparedXML);

	for ($i = 0; $i < 123; $i++) { 
		$preparedXML = str_replace("0 #%%Q29_OPTION".($i + 1)."%%", $q29_answers_conds[$i], $preparedXML);
	}

	$preparedXML = str_replace("0 #%%SHOW_FOR_CONCEPTS_Q30%%", strlen($q30_cond) > 0 ? $q30_cond : "0", $preparedXML);
	$preparedXML = str_replace("0 #%%SHOW_FOR_CONCEPTS_Q31%%", strlen($q31_cond) > 0 ? $q31_cond : "0", $preparedXML);

	// Condition will be controlled if any rows are shown
	//$preparedXML = str_replace("0 #%%SHOW_FOR_CONCEPTS_Q32%%", $q32_cond, $preparedXML);

	for ($i = 0; $i < 20; $i++) { 
		for ($z = 0; $z < 6; $z++) { 
			$preparedXML = str_replace("%%Concept".($i + 1)."_ProductName".($z + 1)."%%", $q32_answer_options[$i][$z], $preparedXML);
		}
	}

	// Condition will be controlled if any rows are shown
	//$preparedXML = str_replace("%%SHOW_FOR_CONCEPTS_Q33%%", $q33_cond, $preparedXML);

	for ($i = 0; $i < 20; $i++) { 
		for ($z = 0; $z < 10; $z++) { 
			$preparedXML = str_replace("%%Concept".($i + 1)."_Attribute".($z + 1)."%%", $q33_answer_options[$i][$z], $preparedXML);
		}
	}
	
	//Dump($q27_cond);

	
	return $preparedXML;
}

 ?>
