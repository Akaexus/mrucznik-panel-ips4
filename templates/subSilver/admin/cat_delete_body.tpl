
<h1>{L_CAT_DELETE}</h1>

<p>{L_CAT_DELETE_EXPLAIN}</p>

<form action="{S_CAT_ACTION}" method="post">
  <table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
	<tr> 
	  <th colspan="2" class="thHead">{L_CAT_DELETE}</th>
	  </tr>
	<tr> 
	  <td class="row1">{L_CAT_NAME}</td>
	  <td class="row1"><span class="row1">{NAME}</span></td>
	</tr>
	<tr> 
	  <td class="row1">{L_MOVE_CONTENTS}</td>
	  <td class="row1">{S_SELECT_TO}</td>
	</tr>
	<tr> 
	  <td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{S_SUBMIT_VALUE}" class="mainoption"></td>
	</tr>
  </table>
</form>

<!--

	$Log: cat_delete_body.tpl,v $
	Revision 1.1  2002/04/08 14:56:39  darkjedi
	many functions and some bugfixes regarding hierarchies added
	great changes and extensions in forum administrations
	
	Revision 1.2  2002/03/26 00:19:02  darkjedi
	Added changelog to files
	

-->
<br>