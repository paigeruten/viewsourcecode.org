#!/usr/bin/php
<?php
// Takes the contents of a file, splits it into an array of lines, removes extra
// whitespace in the lines, and returns it back as an array of lines.
function cleanup_program($program) {
	$lines = explode("\n", $program);
	foreach ($lines as $line_number => $line) {
		$line = trim($line);
		$line = preg_replace('/ +/', ' ', $line); // No need for more than one space
		$line = str_replace("\t", '', $line);	
		$lines[$line_number] = $line;
	}
	return $lines;
}

// Displays an error message and quits
function show_error($error_txt, $line_number, $program_file) {
	die ("*Error* $error_txt (Line #$line_number in $program_file)\n");
}

// Compares a string to all the reserved words of the language
function is_reserved($string) {
	$keywords = array('jump', 'if', 'output', 'input', 'myself', 'not', 'or');
	foreach ($keywords as $keyword) {
		if ($keyword == $string) {
			return true;
		}
	}
	return false;
}

// Removes an element from an array
function array_remove(&$array, $element) {
	$new_array = array();
	foreach ($array as $key => $value) {
		if ($value != $element) {
			$new_array[$key] = $value;
		}
	}
	$array = $new_array;
}

// Checks if a variable exists and has joined the channel
function check_variable($var_name, $channel, $line_number, $channel_filename) {
	global $variables;
	if (!isset($variables[$var_name])) {
		show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
	}
	if (!in_array($channel, $variables[$var_name]['channels'])) { show_error('This (un)voiced hasn\'t joined.', $line_number, $channel_filename); }
}

if ($argc != 2) {
	die ("Usage: $argv[0] <inputfile>\n");
}

// Open the main .irc file
if (!file_exists($argv[1])) {
	die ("File '$argv[1]' doesn't exist.\n");
}
$main_file_contents = file_get_contents($argv[1]);
$main_program = cleanup_program($main_file_contents);

// Get the information about the program
$program = array();
$program['channels'] = array();
foreach ($main_program as $line_num => $current_line) {
	if (preg_match('/^\/nick .+$/', $current_line)) {
		$parts = explode(' ', $current_line);
		if (count($parts) > 2) { show_error('Nick cannot contain spaces.', $line_num+1, $argv[1]); }
		$program['name'] = $parts[1];
		if (is_reserved($program['name'])) { show_error('That name is reserved.', $line_num+1, $argv[1]); }
	} else if (preg_match('/^\/join .+$/', $current_line)) {
		// Make sure they do things in the right order
		if (!isset($program['name'])) { show_error('Must set your nick before you can join channels.', $line_num+1, $argv[1]); }
		if (isset($program['error_msg'])) { show_error('You can\'t join channels after /quitting.', $line_num+1, $argv[1]); }
		
		// "/join" is the zeroth part, the channel name is the first part.
		$parts = explode(' ', $current_line);
		if (count($parts) > 2) { show_error('Channel name cannot contain spaces.', $line_num+1, $argv[1]); }
		if ($parts[1][0] != '#') { show_error('Channel name must start with #.', $line_num+1, $argv[1]); }
		array_push($program['channels'], $parts[1]);
	} else if (preg_match('/^\/quit .+$/', $current_line)) {
		if (!isset($program['name'])) { show_error('You can\'t quit until you /nick.', $line_num+1, $argv[1]); }
		$program['error_msg'] = substr($current_line, 6);
	} else if ($current_line != '') {
		show_error('There\'s something wrong with this line.', $line_num+1, $argv[1]);
	}
}

// Make sure we have the information
if (!isset($program['name'])) { show_error('This program doesn\'t have a name. Remember to /nick.', $line_num+1, $argv[1]); }
if (!isset($program['error_msg'])) { show_error('There is no default error message. Remember to /quit.', $line_num+1, $argv[1]); }
if (count($program['channels']) == 0) { die($program['error_msg']."\n"); }

// Escape special chars/punctuation chars for use in regex
$program['regex_name'] = preg_replace('/([^a-zA-Z0-9]{1})/', '\\\$1', $program['name']);

// Open all the channels and get them ready for interpreting
$channels = array();
foreach ($program['channels'] as $channel) {
	$channel_filename = substr($channel, 1).'.irc';
	if (!file_exists($channel_filename)) {
		die ("File '$channel_filename' doesn't exist.\n");
	}
	$channel_contents = file_get_contents($channel_filename);
	$channels[$channel]['lines'] = cleanup_program($channel_contents);
	$channels[$channel]['pc'] = 0; // Keeps track of which line it's on
	$channels[$channel]['variables'];
	$channels[$channel]['regex_name'] = preg_replace('/([^a-zA-Z0-9]{1})/', '\\\$1', $channel);
	$channels[$channel]['return_to'] = array();
	
	// Get the channel's labels
	$channels[$channel]['labels'] = array();
	foreach ($channels[$channel]['lines'] as $line_number => $line) {
		if (preg_match('/^\* '.$program['regex_name'].' changes topic to \'(.+)\'$/', $line, $matches)) {
			$channels[$channel]['labels'][$matches[1]] = $line_number+1;
		}
	}

	// Check the first line of the channel code, it should be code to join
	if (!preg_match('/^\* '.$program['regex_name'].' has joined '.$channels[$channel]['regex_name'].'$/', $channels[$channel]['lines'][0])) {
		show_error('Program name must join channel in first line of channel.', 1, $channel_filename);
	}
	$channels[$channel]['pc'] = 1;
}

// Start executing channel code
$quit_irc = false;
$variables = array();
while (count($channels) > 0 && !$quit_irc) {
	foreach ($channels as $channel_name => $this_channel) {
		$line = $this_channel['lines'][$this_channel['pc']];
		$channel_filename = substr($channel_name, 1).'.irc';
		$line_number = $this_channel['pc']+1;
	
		// Is it a comment?
		$is_comment = false;
		if (preg_match('/^<([^ ]+)> .+$/', $line, $matches)) {
			$var_name = $matches[1];
			if (!is_reserved($var_name)) {
				check_variable($var_name, $channel_name, $line_number, $channel_filename);
				if (!$variables[$var_name]['voiced']) {
					$is_comment = true;
				}
			}
		}
		
		if ($line == '') {
			$is_comment = true;
		}
		
		if (!$is_comment) {
			// * [voiced] has joined [channel_name]
			if (preg_match('/^\* ([^ ]+) has joined '.$this_channel['regex_name'].'$/', $line, $matches)) {
				$var_name = $matches[1];
				if (is_reserved($var_name)) {
					show_error('This is a reserved word.', $line_number, $channel_filename);
				}
				if (isset($variables[$var_name])) {
					if (in_array($channel_name, $variables[$var_name]['channels'])) { show_error('This (un)voiced has already joined.', $line_number, $channel_filename); }
				} else {
					$variables[$var_name]['voiced'] = false;
					$variables[$var_name]['value'] = 0;
					$variables[$var_name]['channels'] = array();
				}				
				array_push($variables[$matches[1]]['channels'], $channel_name);
				
			// * [voiced] has left [channel_name]
			} else if (preg_match('/^\* ([^ ]+) has left '.$this_channel['regex_name'].'$/', $line, $matches)) {
				$var_name = $matches[1];
				if (isset($variables[$var_name])) {
					if (!in_array($channel_name, $variables[$var_name]['channels'])) { show_error('This (un)voiced hasn\'t joined.', $line_number, $channel_filename); }
				}
				array_remove($variables[$var_name]['channels'], $channel_name);
				if (count($variables[$var_name]['channels']) == 0) {
					unset($variables[$var_name]);
				}
				
			// * [name] has left [channel_name]
			} else if (preg_match('/^\* '.$program['regex_name'].' has left '.$channels[$channel_name]['regex_name'].'$/', $line)) {
				$kill_channel = true;
				
			// * [name] has quit IRC (Quit: [error_message])
			} else if (preg_match('/^\* '.$program['regex_name'].' has quit IRC \(Quit: (.*)\)$/', $line, $matches)) {
				$quit_irc = true;
				$program['error_msg'] = $matches[1];
				
			// * [name] sets mode: +v [voiced]
			} else if (preg_match('/^\* '.$program['regex_name'].' sets mode: \+v (.+)$/', $line, $matches)) {
				$var_name = $matches[1];
				if (isset($variables[$var_name])) {
					if (!in_array($channel_name, $variables[$var_name]['channels'])) { show_error('This (un)voiced hasn\'t joined.', $line_number, $channel_filename); }
				}
				$variables[$var_name]['voiced'] = true;
				
			// * [name] changes topic to '[topic]'
			} else if (preg_match('/^\* '.$program['regex_name'].' changes topic to \'(.+)\'$/', $line, $matches)) {
				$channels[$channel_name]['labels'][$matches[1]] = $channels[$channel_name]['pc'];
				
			// <jump> Let's talk about [topic].
			} else if (preg_match('/^<jump> Let\'s talk about (.+)\.$/', $line, $matches)) {
				$topic = $matches[1];
				if (isset($channels[$channel_name]['labels'][$topic])) {
					$channels[$channel_name]['pc'] = $channels[$channel_name]['labels'][$topic]-1;
				} else {
					show_error('That topic doesn\'t exist.', $line_number, $channel_filename);
				}
				
			// <jump> Off topic: Let's talk about [topic].
			} else if (preg_match('/^<jump> Off topic: Let\'s talk about (.+)\.$/', $line, $matches)) {
				$topic = $matches[1];
				if (isset($channels[$channel_name]['labels'][$topic])) {
					$jump_to = $channels[$channel_name]['labels'][$topic];
					array_push($channels[$channel_name]['return_to'], $channels[$channel_name]['pc']);
					$channels[$channel_name]['pc'] = $jump_to-1;
				} else {
					show_error('That topic doesn\'t exist.', $line_number, $channel_filename);
				}
				
			// <jump> They're talking about [topic] in [channel].
			} else if (preg_match('/^<jump> They\'re talking about (.+) in (\#[^ ]+)\.$/', $line, $matches)) {
				$topic = $matches[1];
				$channel = $matches[2];
				
			// <jump> Let's get back on topic.
			} else if (preg_match('/^<jump> Let\'s get back on topic.$/', $line)) {
				$return_to = array_pop($channels[$channel_name]['return_to']);
				if (!$return_to) {
					show_error('There\'s no topic to get back onto.', $line_number, $channel_filename);
				}
				$channels[$channel_name]['pc'] = $return_to;
			
			// <if> [voiced], are you [condition]?
			} else if (preg_match('/^<if> ([^ ]+), are you (.+)\?$/', $line, $matches)) {
				$var_name = $matches[1];
				$condition = $matches[2];
				
				if (!isset($variables[$var_name])) {
					show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
				}
				
				// not equal to [value]
				if (preg_match('/^not equal to ([^ ]+)$/', $condition, $matches)) {
					$var2_name = $matches[1];
					if (!isset($variables[$var2_name]) && !is_numeric($var2_name)) {
						show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
					}
					if (is_numeric($var2_name)) {
						$condition_result = !($variables[$var_name]['value'] != intval($var2_name));
					} else {
						$condition_result = !($variables[$var_name]['value'] != $variables[$var2_name]['value']);
					}
					if ($condition_result == true) {
						$channels[$channel_name]['pc']++;
					}
				
				// equal to [value]
				} else if (preg_match('/^equal to ([^ ]+)$/', $condition, $matches)) {
					$var2_name = $matches[1];
					if (!isset($variables[$var2_name]) && !is_numeric($var2_name)) {
						show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
					}
					if (is_numeric($var2_name)) {
						$condition_result = !($variables[$var_name]['value'] == intval($var2_name));
					} else {
						$condition_result = !($variables[$var_name]['value'] == $variables[$var2_name]['value']);
					}
					if ($condition_result == true) {
						$channels[$channel_name]['pc']++;
					}
				
				// equal to or greater than [value]
				} else if (preg_match('/^equal to or greater than ([^ ]+)$/', $condition, $matches)) {
					$var2_name = $matches[1];
					if (!isset($variables[$var2_name]) && !is_numeric($var2_name)) {
						show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
					}
					if (is_numeric($var2_name)) {
						$condition_result = !($variables[$var_name]['value'] >= intval($var2_name));
					} else {
						$condition_result = !($variables[$var_name]['value'] >= $variables[$var2_name]['value']);
					}
					if ($condition_result == true) {
						$channels[$channel_name]['pc']++;
					}
				
				// equal to or less than [value]
				} else if (preg_match('/^equal to or less than ([^ ]+)$/', $condition, $matches)) {
					$var2_name = $matches[1];
					if (!isset($variables[$var2_name]) && !is_numeric($var2_name)) {
						show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
					}
					if (is_numeric($var2_name)) {
						$condition_result = !($variables[$var_name]['value'] <= intval($var2_name));
					} else {
						$condition_result = !($variables[$var_name]['value'] <= $variables[$var2_name]['value']);
					}
					if ($condition_result == true) {
						$channels[$channel_name]['pc']++;
					}
				
				// greater than [value]
				} else if (preg_match('/^greater than ([^ ]+)$/', $condition, $matches)) {
					$var2_name = $matches[1];
					if (!isset($variables[$var2_name]) && !is_numeric($var2_name)) {
						show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
					}
					if (is_numeric($var2_name)) {
						$condition_result = !($variables[$var_name]['value'] > intval($var2_name));
					} else {
						$condition_result = !($variables[$var_name]['value'] > $variables[$var2_name]['value']);
					}
					if ($condition_result == true) {
						$channels[$channel_name]['pc']++;
					}
				
				// less than [value]
				} else if (preg_match('/^less than ([^ ]+)$/', $condition, $matches)) {
					$var2_name = $matches[1];
					if (!isset($variables[$var2_name]) && !is_numeric($var2_name)) {
						show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename);
					}
					if (is_numeric($var2_name)) {
						$condition_result = !($variables[$var_name]['value'] < intval($var2_name));
					} else {
						$condition_result = !($variables[$var_name]['value'] < $variables[$var2_name]['value']);
					}
					if ($condition_result == true) {
						$channels[$channel_name]['pc']++;
					}
				
				// None of the above.
				} else {
					show_error('Bad condition.', $line_number, $channel_filename);
				}
				
			// <output> What's your value, [voiced]?
			} else if (preg_match('/^<output> What\'s your value, ([^ ]+)\?$/', $line, $matches)) {
				$var_name = $matches[1];
				if (!isset($variables[$var_name])) { show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename); }
				echo $variables[$var_name]['value'];
				
			// <output> What's your character, [voiced]?
			} else if (preg_match('/^<output> What\'s your character, ([^ ]+)\?$/', $line, $matches)) {
				$var_name = $matches[1];
				if (!isset($variables[$var_name])) { show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename); }
				echo chr($variables[$var_name]['value']);
				
			// <input> Hey, [voiced].
			} else if (preg_match('/^<input> Hey, ([^ ]+)\.$/', $line, $matches)) {
				$var_name = $matches[1];
				if (!isset($variables[$var_name])) { show_error('This (un)voiced doesn\'t exist!', $line_number, $channel_filename); }
				$variables[$var_name]['value'] = ord(fread(STDIN, 1));
				
			// <[voiced]> I'm not [value].
			} else if (preg_match('/^<([^ ]+)> I\'m not ([^ ]+)\.$/', $line, $matches)) {
				$var_name = $matches[1];
				$value = $matches[2];
				check_variable($var_name, $channel_name, $line_number, $channel_filename);
				if (is_numeric($value)) {
					$variables[$var_name]['value'] = ~intval($value);
				} else {
					if (!isset($variables[$value])) { show_error('This variable doesn\'t exist.', $line_number, $channel_filename); }
					$variables[$var_name]['value'] = ~$variables[$value]['value'];
				}
				
			// <[voiced]> I'm [value].
			} else if (preg_match('/^<([^ ]+)> I\'m ([^ ]+)\.$/', $line, $matches)) {
				$var_name = $matches[1];
				$value = $matches[2];
				check_variable($var_name, $channel_name, $line_number, $channel_filename);
				if (is_numeric($value)) {
					$variables[$var_name]['value'] = intval($value);
				} else {
					if (!isset($variables[$value])) { show_error('This variable doesn\'t exist.', $line_number, $channel_filename); }
					$variables[$var_name]['value'] = $variables[$value]['value'];
				}
				
			// <[voiced]> I'm [value] [operation] [value].
			} else if (preg_match('/^<([^ ]+)> I\'m ([^ ]+) ([a-z ]+) ([^ ]+)\.$/', $line, $matches)) {
				$var_name = $matches[1];
				$value1 = $matches[2];
				$operation = $matches[3];
				$value2 = $matches[4];
				
				check_variable($var_name, $channel_name, $line_number, $channel_filename);
				
				if ($value1 == 'myself') { $value1 = $variables[$var_name]['value']; }
				if ($value2 == 'myself') { $value2 = $variables[$var_name]['value']; }
				
				if (!is_numeric($value1)) {
					if (!isset($variables[$value1])) { show_error('This variable doesn\'t exist.', $line_number, $channel_filename); }
					$value1 = $variables[$value1]['value'];
				}
				if (!is_numeric($value2)) {
					if (!isset($variables[$value2])) { show_error('This variable doesn\'t exist.', $line_number, $channel_filename); }
					$value2 = $variables[$value2]['value'];
				}
				
				switch ($operation) {
					case 'plus':
						$variables[$var_name]['value'] = $value1 + $value2;
						break;
					case 'minus':
						$variables[$var_name]['value'] = $value1 - $value2;
						break;
					case 'times':
						$variables[$var_name]['value'] = $value1 * $value2;
						break;
					case 'divided by':
						if ($value2 == 0) { show_error('Division by zero.', $line_number, $channel_filename); }
						$variables[$var_name]['value'] = $value1 / $value2;
						break;
					case 'to the power of':
						$variables[$var_name]['value'] = pow($value1, $value2);
						break;
					case 'and':
						$variables[$var_name]['value'] = $value1 & $value2;
						break;
					case 'or':
						$variables[$var_name]['value'] = $value1 | $value2;
						break;
					case 'xor':
						$variables[$var_name]['value'] = $value1 ^ $value2;
						break;
				}
				
			// None of the above.
			} else {
				show_error('There\'s something wrong with this line.', $line_number, $channel_filename);
			}
		}
		
		$channels[$channel_name]['pc']++;
		
		if (count($channels[$channel_name]['lines']) < $channels[$channel_name]['pc'] || $kill_channel) {
			unset($channels[$channel_name]);
			$kill_channel = false;
			
			// Clean up variables that were in this channel
			foreach ($variables as $variable => $variable_info) {
				array_remove($variables[$variable]['channels'], $channel_name);
				if (count($variables[$variable]['channels']) == 0) {
					unset($variables[$variable]);
				}
			}
		}
	}
}

echo $program['error_msg']."\n";
?>
