<div class='content' id='search'>

<table name="searchtable">
<tr><td>

<h2>Search</h2>

<form name="articlequery" action="search.php" method="get">
<table class="searchform" border="0">
<tr><th>Keyword</th><td class="input"><input type="text" size="40" name="keyword" value="<?php print $kw ?>"></td></tr>
<tr><th>Title</th><td class="input"><input type="text" size="40" name="doctitle" value="<?php print $doctitle ?>"></td></tr>
<tr><th>Author</th><td class="input"><input type="text" size="40" name="author" value="<?php print $auth ?>"></td></tr>
<tr><th>Article Date</th><td class="input"><input type="text" size="40" name="date" value="<?php print $date ?>"></td></tr>

<tr><td></td><td><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>
</table>
</form>


<td class="searchtips">
<ul class ="searchtips"><b>Search tips:</b>
<li>Search terms are matched against <i>whole words.</i></li>
<li>Multiple words are allowed.</li>
<li>Asterisks may be used when searching for multiple words from one root. <br/>
For example, enter <b>resign*</b> to match <b>resign</b>, <b>resigned</b>, and
<b>resignation</b>. </li>
<li> Use several categories to narrow your search. For example, use author, keyword and 
title to match a particular article.</li>
<li>When searching on a state, try the abbreviated form as well. For example, use GA and Georgia
to see search results for both.</li>
</ul>
</td>
</tr>
</table>

<p class="searchtips">If you are interested in doing a more complex search, please
contact the <script language="javascript">
<!--
var username = "beckctr";
var hostname = "emory.edu";
var linktext = "Beck Center Staff";
document.write("<a href=" + "mail" + "to:" + username +
"@" + hostname + ">" + linktext + "</a>")
//-->
</script>.</p>


</div>
