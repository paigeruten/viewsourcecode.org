<?php

/* about.php
 *
 * Information about the site's purpose and who I am.
 */

$page_title = 'About';
include 'include/header.php';

?>

<p style="text-align: center;"><img src="/images/flag.png" /></p>

<p style="text-align: center; font-size: 8pt;">...our humble flag...</p>

<p>imagine you are standing in the parking lot in pouring rain. you gave away your umbrella to a little kid, so you're soaked. you walk into wal-mart and the place is just bustling with animals, walking talking and shopping. a couple giraffes are picking out their first vacuum cleaner. an elephant in business attire bumps past you, shouting angrily into his cell phone. oh and look, there's the town bear. just walking up and down the building, snapping photos, non stop.</p>

<p>well, you decide it's too much for you. you just can't handle these crowds. so you resolve to buy your candle holders and move on. but when you go to checkout, you discover the checker is merely a banana. she's just sitting there on the stool, on her side, neat and yellow. unmoving. you wait politely for her to take notice of you. you wait all day, all night, and she still hasn't budged.</p>

<p>you wait three days more. she's turning dark and squishy. still, you determine to be by her side to the end. until one day you awaken and there's just a mote of dirt on the chair: her remains. you lean in close to say goodbye. instead you hear a faint whispering coming from the mouth of the dust: "eight-sixty-two please."</p>

<p>you reach in your pocket and count out the change. you extend the money in your palm towards her, but in doing so you create a very windy day for her and she floats away in more directions than one.</p>

<p>you look around for the first time in weeks and the place is deserted. see, you're not in wal-mart anymore. you're in...</p>

<p style="font-weight: bold; font-size: 14pt; text-align: center;"><em>~ Jeremy Ruten's abode. ~</em></p>

<?php

$lines = array(
	'now let\'s all go to',
	'<a href="mailto:jeremy.ruten@gmail.com">the email trail.</a>',
	'<a href="http://github.com/yjerem">the code road.</a>',
	'<a href="http://stackoverflow.com/users/813/yjerem">the stack track.',
	'<a href="http://news.ycombinator.com/user?id=Jeremysr">the news cruise.</a>',
	'<a href="http://twitter.com/yjerem">the tweet street.</a>',
	'<a href="http://viewsourcecode.org/about">the roundabout.</a>'
);

$margin = 0;
foreach ($lines as $line) {
	echo "<p style='margin-left: {$margin}px;'>$line</p>";
	$margin += 30;
}

include 'include/footer.php';

?>

