<pre>
<?php
	include 'classifier.php';
	
	// train or load an NGram profile, note a model will not be able to deal with languages it has not seen during training and 
	// will gladly misclassify any such language as one it has seen during training.
	$classifier = new NGramProfiles('etc/classifiers/ngrams.dat');
	if( !$classifier->exists() ) {
		$classifier->train('en', 'etc/data/english.raw');
		$classifier->train('nl', 'etc/data/dutch.raw');
		$classifier->train('fr', 'etc/data/french.raw');
		$classifier->save();
	} else {
		$classifier->load();
	}
	
	// simple prediction function that takes a classifier and a text and echo's the most likely language
	function predict($classifier, $text) {
		$language = $classifier->predict($text);
		echo "{$language} = '{$text}'\n";
	}
	
	predict($classifier, "Dit is een nederlandse text.");
	predict($classifier, "This is an english text.");
	predict($classifier, "Ceci n'est pas une pipe.");