# BracketMaker

Creates a bracket from an array of entries. Each page shows two images. Clicking one will select that as the winner of the pair, and move on to the next comparison. State is stored in the `bracket` GET parameter, and pages are served by doing all work on server side in PHP and serving a static HTML page with no javascript.
