<div class='content' id='search'>

<table name="searchtable">
<tr><td>

<h2>Search</h2>

<form name="articlequery" action="search.php" method="get">
<table class="searchform" border="0">
<tr><th>Keyword</th><td class="input"><input type="text" size="40" name="keyword" value="<?php print $kw ?>"></td></tr>
<tr><th>Exact Phrase</th><td class="input"><input type="text" size="40" name="exact" value="<?php print $phrase ?>"></td></tr>
<tr><th>Title</th><td class="input"><input type="text" size="40" name="title" value="<?php print $title ?>"></td></tr>
<tr><th>Author</th><td class="input"><input type="text" size="40" name="author" value="<?php print $author ?>"></td></tr>
<tr><th>Article Date</th><td class="input"><input type="text" size="40" name="date" value="<?php print $date ?>"></td></tr>

<tr><td></td><td><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>
</table>
</form>

<!--
<h2>Specialized Search</h2>
<form name="advancedquery" action="search.php" method="get">
<table class="searchform" border="0">
<tr><th>Enter word or phrase:</th><td class="input"><input type="text" size="40" name="keyword"></td></tr>
<tr><th>Type of search:</th><td><input type="radio" name="mode" value="phonetic">Phonetic
<input type="radio" name="mode" value="exact">Exact Phrase -->
<!-- This requires a defined dictionary which we do not have yet<input type="radio" name="mode" value="synonym">Synonym</td>-->
<!-- </tr>
<tr><td></td><td><input type="submit" value="Submit"><input type="reset" value="Reset"></td></tr> 
</table>
</form>
</td>
-->
<td class="searchtips">
<ul class ="searchtips"><b>Search tips:</b>
<li>Search terms are matched against <i>whole words</i></li>
<li>Multiple words are allowed.</li>
<li>Asterisks may be used when using a part of a word or words. <br/>
For example, enter <b>resign*</b> to match <b>resign</b>, <b>resigned</b>, and
<b>resignation</b>. </li>
<li> Use several categories to narrow your search. For example, use author, keyword and<br/>
title to match a particular sermon.</li>
<li>When searching on a state, try the abbreviated form as well. For example, use NY and New York
to see search results for both.</li>
</ul>
</td>
</tr>
</table>

<p class="searchtips">If you are interested in doing a more complex search, please
contact the <a href="mailto:beckcenter@emory.edu">Beck Center
Staff</a>.</p>


</div>
