<?php

	class Classifier {
		var $state = array();
		
		// basic constructor that takes a path to the model file
		function __construct($file) {
			$this->file = $file;
		}
		
		// does the classifier exist?
		function exists() {
			return file_exists($this->file);
		}
		
		// save classifier to disk
		function save() {
			$data = serialize($this->state);
			//$data = gzcompress ($data, 9);
			file_put_contents($this->file, $data);
		}
	
		// load classifier from disk
		function load() {
			if( !$this->exists() ) {
				throw new Exception("Classifier->load '{$this->file}' does not exist!"); 
			}
			$data = file_get_contents($this->file);
			$this->state = unserialize($data);
		}
		
		// train a file on this classifier for $class
		function train($class, $file) {
			$this->model($class, file_get_contents($file));
		}

		// train 1 datum for modelling as $class
		function model($class, $data) {
		}

		// return predicted $class for datum
		function predict($data) {			
		}

	}
	
	define('DEPTH', 750);
	// we are looking at ngrams from 1 .. N
	define('N', 4);
	class NGramProfiles extends Classifier {

		// get top max_ngs ngrams from 1 to max_n for data
		function ngrams($data, $max_ngs = DEPTH, $max_n = N) {
			$data = preg_replace("/[^a-zA-Z\s]/", "", $data);
			$words = str_word_count(strtolower($data), 1);
			$ngrams = array();
			// split words into ngramgs
			foreach($words as $word) {
				$word = $word;
				$length = strlen($word);
				for( $index = 0; $index < $length; $index++ ) {
					for( $n = 1; $n <= $max_n; $n++ ) {
						$ngram = substr($word, $index, $n);
						if( strlen($ngram) < $n )
							continue;
						$ngrams[] = $ngram; 
					}
				}
			}
			// determine ngram frequency counts
			$frequencies = array_count_values($ngrams); 
			arsort($frequencies); 
			// throw away all but the top 750 most frequently occuring ngrams
			$frequencies = array_slice($frequencies, 0, $max_ngs); 
			return $frequencies;
		}
		
		// model $data as $class
		function model($class, $data) {
			$ngrams = $this->ngrams($data);
			if( !isset($this->state[$class]) ) {
				$this->state[$class] = $ngrams;
			} else {
				foreach($ngrams as $ngram => $count) {
					if( !isset($this->state[$class][$ngram]) )
						$this->state[$class][$ngram] = $count;
					else
						$this->state[$class][$ngram] += $count;
				}
			}
		}

		// get index number of a key from an array
		private function index($key, $array) {
			$index = 0;
			foreach($array as $k => $v) {
				if( $k == $key ) 
					return $index;
				$index++;
			}
			return -1;
		}
		
		// predict a class for $data, using cut-off $max_delta
		function predict($data, $max_delta = 140000) {
			$ngrams = $this->ngrams($data);
			$result = array();
			foreach( $this->state as $class => $language ) {
				$delta = 0;
				$index = 0;
				foreach( $ngrams as $ngram => $count ){
					if( isset($language[$ngram]) ) {
						// this ngram exists
						$index2 = $this->index($ngram, $language);
						if($index2 == -1)
							die('error: ngram should be there, but was not found');
						$delta += abs($index - $index2);
					} else {
						// ngram not found in model
						$delta += DEPTH;
					}
					$index++;
					// abort: this language already differs too much
					if( $delta > $max_delta )
						break;
				} 
				if ($delta < $max_delta )
					$result[$class] = $delta;
			} 
			asort($result);
			return key($result);
		}
	}
