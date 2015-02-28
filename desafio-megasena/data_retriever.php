<?php //Author:: vkwasinski@gmail.com
/*
	Scraps a page and retrieves the information below {
		Which state has more winners, V
		What is the awards average for which state,
		Throws an ordered list from the tens cases,
	}

*/

include 'class/include.php';


$url = isset($argv[1])? $argv[1]: null;
if (!$url)  
	exit( 'URL missing'."\n");

$html_parser = new simple_html_dom(); 
$page_scrapper = new MyScrapper($url);
	
print 'scraping from '.$url."\n";
$scrapped_page = $page_scrapper->curl_scrap();

if (!$scrapped_page)
	exit('Something went wrong while scrapping: '. $url. "\n");
print 'Page Scrapped, checking and save if necessary'."\n";
$scrap_isOk = $page_scrapper->check_n_save_page($scrapped_page['scrapped_page'], $scrapped_page['filetime']);

print 'Retrieving data from file...'."\n";
//let's retrieve the data required!
$html_parser->load_file('megasena.html');
$tr = $html_parser->find('tr');

$all_states = array();
foreach ($tr as $element) {

	$state_td = $element->children(11);	
	preg_match("/\<td\>(\w\w)/", $state_td, $matches);
	if (!isset($matches[1]))
		continue;
	$state = $matches[1];
	if (!array_key_exists($state, $all_states))
		$all_states[$state] = array();

	$total_winners = 0;
	foreach (array('sena_winners' => 9, 'quina_winners' => 13, 'quadra_winners' => 15) as $game => $column_number) {
		$winners_per_game = $element->children($column_number);
		preg_match("/\>(\d+)/", $winners_per_game, $matches);

		if(isset($matches[1]))
			$winners_per_game = (int) $matches[1];

		if (isset($all_states[$state][$game])) {
			$all_states[$state][$game] += $winners_per_game;
			$all_states[$state]['total_winners'] += $winners_per_game;

		} else {
			$all_states[$state][$game] = $winners_per_game;	
			$all_states[$state]['total_winners'] = $winners_per_game;

		}
		$total_winners += $winners_per_game;
	}

}

$array_ordered_tens = array();
foreach ($tr as $element) {
	foreach (array(
		'first_tens' => 2,
		'seccond_tens' => 3,
		'third_tens' => 4,
		'fourth_tens' => 5,
		'fifth_tens' => 6,
		'sixth_tens' => 7,
	 ) as $dozen => $number) {

		$winners_per_game = $element->children($number);
		preg_match("/\>(\d+)/", $winners_per_game, $matches);	

		if (!isset($matches[1]))
			continue;

		$dozen_number = $matches[1];
		if (isset($array_ordered_tens[$dozen])) {
				$array_ordered_tens[$dozen][] = $dozen_number;
		} else {
			$array_ordered_tens[$dozen][] = $dozen_number;
		}
		
	}
}

//retriveing the state with more winners by grabbing the array with the highest value number and retrieving the key
$state_with_more_winners = array_keys($all_states, max($all_states)); 
$state_with_more_winners = $state_with_more_winners[0];

//average between the games quina, sena and quadra.
$game_statistics = array();
foreach ($all_states as $state => $properties) {
	$game_statistics[$state]['average_winners'] = ($properties['quina_winners'] + $properties['sena_winners'] + $properties['quadra_winners']) / 3;
}

$file_handle = fopen('result.txt', 'w');

ob_start();
var_dump($game_statistics);
$game_statistics = ob_get_clean(); //getting the buffer output of var_dump()

ob_start();
var_dump($array_ordered_tens);
$array_ordered_tens = ob_get_clean();

fwrite($file_handle, 'Candidate: Vinícius Kwasinski'."\n\n");
fwrite($file_handle, 'The state with the most winners is: '. $state_with_more_winners."\n\n");
fwrite($file_handle, 'The list with the awards average for each state : '."\n". $game_statistics."\n\n");
fwrite($file_handle, 'An ordered list this tens case: '. $array_ordered_tens);
fclose($file_handle);

print 'Successful, please open \'result.txt\'  and find the answers.'."\n";

//thank you for the challenge.