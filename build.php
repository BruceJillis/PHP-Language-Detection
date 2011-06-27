<pre>
<?php
	include 'classifier.php';
	
	$classifier = new NGramProfiles('etc/classifiers/full.dat');
	$classifier->train('en', 'etc/data/english.raw');
	$classifier->train('nl', 'etc/data/dutch.raw');
	$classifier->train('fr', 'etc/data/french.raw');
	$classifier->train('de', 'etc/data/german.raw');
	$classifier->train('id', 'etc/data/indonesian.raw');
	$classifier->train('jp', 'etc/data/japanese.raw');
	$classifier->train('pt', 'etc/data/portugese.raw');
	$classifier->train('es', 'etc/data/spanish.raw');
	$classifier->save();
	
	// simple prediction function that takes a classifier and a text and echo's the most likely language
	function predict($classifier, $text, $result) {
		$language = $classifier->predict($text);
		echo "{$language} = {$result} @ '{$text}'\n";
	}
	
	predict($classifier, "Dit is een nederlandse text.", 'nl');
	predict($classifier, "This is an english text.", 'en');
	predict($classifier, "Ceci n'est pas une pipe.", 'fr');
	predict($classifier, "dies ist ein Satz auf Deutsch", 'de');
	predict($classifier, "esta es una frase en alemán", 'es');