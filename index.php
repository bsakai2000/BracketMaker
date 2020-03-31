<?php
// Identifies the minimum number of comparisons among bracket members who
// have not yet lost
function findMin($bracketMembers)
{
	// Set min high
	$min = PHP_INT_MAX;
	foreach($bracketMembers as $member)
	{
		// If this member has lost, get rid of it
		if($member["hasLost"] != 0)
		{
			continue;
		}
		// If this member has less comparisons than min, it becomes min
		if($member["comparisons"] < $min)
		{
			$min = $member["comparisons"];
		}
	}
	return $min;
}

// Identifies the second minimum number of comparisons, which may be equal to
// the minimum number
function findSecondMin($bracketMembers, $min)
{
	// Set secondMin high
	$secondMin = PHP_INT_MAX;
	// If we find min once, that's fine. If we find it twice, secondmin == min
	$foundMin = false;
	foreach($bracketMembers as $member)
	{
		// If this member has lost, get rid of it
		if($member["hasLost"] != 0)
		{
			continue;
		}
		if($member["comparisons"] == $min)
		{
			// If we see min twice, secondmin == min
			if($foundMin)
			{
				return $min;
			}
			$foundMin = true;
		}
		// If this is less than secondMin, it becomes secondMin
		else if($member["comparisons"] < $secondMin)
		{
			$secondMin = $member["comparisons"];
		}
	}
	return $secondMin;
}

// Check for a winner. A winner exists when no other contenders exist
function checkWinner($bracketMembers)
{
	$winner = -1;
	// Loop through looking for members which have not lost
	for($i = 0; $i < count($bracketMembers); ++$i)
	{
		// If we find a second member which has not lost, there
		// is no winner
		if($bracketMembers[$i]["hasLost"] == 0)
		{
			if($winner != -1)
			{
				return -1;
			}
			$winner = $i;
		}
	}
	return $winner;
}

// Read encodedString and set bracketMembers accordingly
function decodeBracket(&$bracketMembers, $encodedString)
{
	$encodedArr = explode(",", $encodedString);
	$encodedIndex = 0;
	// encodedString is, in order, comparisons then hasLost for each member
	for($i = 0; $i < count($bracketMembers); ++$i)
	{
		$bracketMembers[$i]["comparisons"] = $encodedArr[$encodedIndex++];
		$bracketMembers[$i]["hasLost"] = $encodedArr[$encodedIndex++];
	}
}

// Creates an encoded string for this bracket
function encodeBracket($bracketMembers, $loser)
{
	$encoded = "";
	// Loop through members, append comparisons and hasLost to encoded
	for($i = 0; $i < count($bracketMembers); ++$i)
	{
		$comparisons = $bracketMembers[$i]["comparisons"];
		$hasLost = $bracketMembers[$i]["hasLost"];
		// If this index is the loser index, hasLost is true by definition
		if($i == $loser)
		{
			$hasLost = 1;
		}
		$encoded .= $comparisons . "," . $hasLost . ",";
	}
	return $encoded;
}

// Each element represents one entity in the bracket
$bracketMembers = [
	array("name" => "Google", "url" => "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png", "comparisons" => 0, "hasLost" => 0),
	array("name" => "Amazon", "url" => "https://images-na.ssl-images-amazon.com/images/G/01/gno/images/general/navAmazonLogoFooter._CB485934996_.gif", "comparisons" => 0, "hasLost" => 0),
	array("name" => "Apple", "url" => "https://www.apple.com/newsroom/images/default_LP_wide.jpg.large.jpg", "comparisons" => 0, "hasLost" => 0),
	array("name" => "Mozilla", "url" => "https://www.mozilla.org/media/contentcards/img/home-2019/card_1/master.b8b7b1df67c3.png", "comparisons" => 0, "hasLost" => 0),
	array("name" => "Github", "url" => "https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png", "comparisons" => 0, "hasLost" => 0)
];

// If we have already started this bracket, read it in
if(isset($_GET["bracket"]))
{
	// Read the bracket string into bracketMembers
	decodeBracket($bracketMembers, $_GET["bracket"]);
	// Check if we have a winner already
	$winner = checkWinner($bracketMembers);
	if($winner != -1)
	{
		echo "Winner! " . $bracketMembers[$winner]["name"];
		exit();
	}
}

// Find the two minimum number of comparisons in our bracket
$min = findMin($bracketMembers);
$secondMin = findSecondMin($bracketMembers, $min);

$first = -1;
$second = -1;
// Identify the elements we'll be comparing
for($i = 0; $i < count($bracketMembers) && ($first == -1 || $second == -1); ++$i)
{
	// If this member is out of the bracket, don't check it
	if($bracketMembers[$i]["hasLost"])
	{
		continue;
	}
	// If this member matches min and we don't have a min, it's our first member
	if($bracketMembers[$i]["comparisons"] == $min && $first < 0)
	{
		$first = $i;
	}
	// If this member matches secondmin and we don't have a secondMin, it's our second member
	else if($bracketMembers[$i]["comparisons"] == $secondMin && $second < 0)
	{
		$second = $i;
	}
}

// If we didn't find first or second, something went wrong
if($first == -1 || $second == -1)
{
	echo "Something went wrong!";
	exit();
}

// We'll be comparing first and second, so up their number of comparisons
++$bracketMembers[$first]["comparisons"];
++$bracketMembers[$second]["comparisons"];
// Encode a string for the two possible outcomes
$firstEncoded = encodeBracket($bracketMembers, $second);
$secondEncoded = encodeBracket($bracketMembers, $first);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<style>
			.center {
				text-align: center;
			}
			img {
				/* Adjust to a size that fits your images */
				min-height: 0px;
				min-width: 0px;
			}
		</style>
	</head>
	<body>
		<table>
			<tbody>			
				<tr>
					<td>
						<!-- Show the image, and the link sends you to the next round of the bracket
							where second has lost -->
						<a href="?bracket=<?php echo $firstEncoded ?>">
							<img src="<?php echo $bracketMembers[$first]["url"]; ?>">
						</a>
					</td>
					<td>
						<!-- Show the image, and the link sends you to the next round of the bracket
							where first has lost -->
						<a href="?bracket=<?php echo $secondEncoded ?>">
							<img src="<?php echo $bracketMembers[$second]["url"]; ?>">
						</a>
					</td>
				</tr>
				<tr>
					<!-- Show the names associated with the images -->
					<td class="center">
						<?php echo $bracketMembers[$first]["name"]; ?>
					</td>
					<td class="center">
						<?php echo $bracketMembers[$second]["name"]; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>
