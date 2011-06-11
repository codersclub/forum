<?php

class skin_Statistics {

function Statistics($data="") {

global $ibforums;

return <<<EOF


<table width=100%><TR><TD width=50%>

<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Top 50 Topics With Most Views

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Thread Title</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># of Views</td>



        </tr>


<tr> 

<td nowrap align=left ><font face="verdana, arial, helvetica, sans-serif" size="1" color="black">{$data['viewthread']}</td>

<td  class="row2" div align=right ><font face="verdana, arial, helvetica, sans-serif" size="1"><span style='color:#888888'>{$data['viewviews']}</td>

</tr>

</TD></TR></table>

</TD></TR></Table></div>





</TD><TD width=50%>





<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Top 50 Topics With Most Posts

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Thread Title</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># of Posts</td>



        </tr>


<tr> 

<td nowrap class="row2" width=100%><font face="verdana, arial, helvetica, sans-serif" size="1" color="black">{$data['replythread']}</td>

<td class="row2" div align=right><font face="verdana, arial, helvetica, sans-serif" size="1"><span style='color:#888888'>{$data['posts33']}</td>

</tr>

</TD></TR></table>

</TD></TR></Table>

</TD></TR></Table>





<table width=100%><TR><TD width=50%>


<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' align=center> 

      &nbsp; Top 25 Users With Most Posts

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Member Name</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># of Posts</td>



        </tr>

<tr> 

<td nowrap class="row2" width=100%>{$data['poster']}</td>

<td class="row2" align=center><span style='color:#888888'>{$data['posts23']}</td>

</tr>

</TD></TR></table>
</TD></TR></Table>




</TD><TD width=50%>



<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' align=center> 

      &nbsp; Top 25 Polls With Most Votes

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Poll Question</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># of Votes</td>



        </tr>

<tr> 

<td nowrap class="row2" width=100%>{$data['poll_question']}</td>

<td class="row2" align=center><span style='color:#888888'>{$data['votes']}</td>

</tr>

</TD></TR></table>
</TD></TR></Table>

</TD></TR></Table>







<table width="70%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' align=center> 

      &nbsp; Top 5 Forums With Most Posts

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Forum Name</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># of Posts</td>



        </tr>

<tr> 

<td nowrap class="row2" width=100%>{$data['name']}</td>

<td class="row2" align=center><span style='color:#888888'>{$data['forumposts']}</td>

</tr>




</TD></TR></table>
</TD></TR></Table>



</TD></TR></Table>









<table width=90% align=center><TR><TD width=50%>





<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' align=center> 

      &nbsp; 10 Newest Members

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Member</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># of Posts</td>



        </tr>

<tr> 

<td nowrap class="row2" width=100%>{$data['name27']}</td>

<td class="row2" align=center><span style='color:#888888'>{$data['posts']}</td>

</tr>




</TD></TR></table>
</TD></TR></Table>







</TD><TD width=50%>





<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' align=center> 

      &nbsp; Top 10 Topic Starters

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Member Name</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># Topics</td>



        </tr>

<tr> 

<td nowrap class="row2" width=100%>{$data['starter_name']}</td>

<td class="row2" align=center><span style='color:#888888'>{$data['threadstart']}</td>

</tr>













</TD></TR></table>
</TD></TR></Table>

</TD></TR></Table>











<table width="70%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' align=center> 

      &nbsp; Top 5 Forums With Most Topics

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Forum Name</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># of Topics</td>



        </tr>

<tr> 

<td nowrap class="row2" width=100%>{$data['name']}</td>

<td class="row2" align=center><span style='color:#888888'>{$data['forumtopics']}</td>

</tr>

</TD></TR></table>
</TD></TR></Table>
</TD></TR></Table>





<Table width=90% align=center><TR><TD width=50%>



<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' align=center> 

      &nbsp; Top 10 Posters In Past Week

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

		<td nowrap  class='pformstrip' align="center">Member Name</td>
		<td nowrap  class='pformstrip' align="center"># of Posts</td>
	</tr>
	{$data[week_posters]}
</table>


</TD></TR></Table>



</TD><TD width=50%>




<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Top 10 Posters In Past Month

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 


	<tr> 
		<td nowrap  class='pformstrip' align="center">Member Name</td>
		<td nowrap  class='pformstrip' align="center"># of Posts</td>
	</tr>
	{$data[month_posters]}
</table>


</TD></TR></Table>
</TD></TR></Table>
</TD></TR></Table>



<Table width=90% div align=center><TR><TD width=50%>



<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Top Used Instant Messengers

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 


	<tr> 
		<td nowrap  class='pformstrip' align="center" width="90%">&nbsp;&nbsp;Instant Messenger</td>
		<td nowrap  class='pformstrip' align="center" width="10%"># of Members</td>
	<tr>
		<td class='row2'><span style='color:#888888'>&nbsp;&nbsp;AIM</td>
		<td class="row2" align=center><span style='color:#888888'>{$data[aim]}</td>
	</tr>
	<tr>
		<td class='row2'><span style='color:#888888'>&nbsp;&nbsp;MSN</td>
		<td class="row2" align=center><span style='color:#888888'>{$data[msn]}</td>
	</tr>
	<tr>
		<td class='row2'><span style='color:#888888'>&nbsp;&nbsp;Yahoo</td>
		<td class="row2" align=center><span style='color:#888888'>{$data[yahoo]}</td>
	</tr>
	<tr>
		<td class='row2'><span style='color:#888888'>&nbsp;&nbsp;ICQ</td>
		<td class="row2" align=center><span style='color:#888888'>{$data[icq]}</td>
	</tr>
	<tr>
		<td class='row2'><span style='color:#888888'>&nbsp;&nbsp;No Messenger Info Given</td>
		<td class="row2" align=center><span style='color:#888888'>{$data[none]}</td>
	</tr>

</TD></TR></table>
</TD></TR></Table>



</TD><TD width=50%>


<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Five Most Recently Active Users

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Member Name</td>

            <td nowrap  class='pformstrip' align="center" width="30%">Date/Time</td>



        </tr>

	{$data['last_active']}

</TD></TR></table>
</TD></TR></Table>
</TD></TR></Table>





<table width=90% align=center><TR><TD width=33%>



<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Posts Per Month 

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Month</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># Posts</td>



        </tr>

	{$data['posts_by_month']}

</TD></TR></table>
</TD></TR></Table>




</TD><TD width=33%>








<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; New Topics Each Month 

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Month</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># Topics</td>



        </tr>

	{$data['topics_by_month']}

</TD></TR></table>
</TD></TR></Table>



</TD><TD width=33%>



<table width="95%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Registrations By Month 

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Month</td>

            <td nowrap  class='pformstrip' align="center" width="30%"># Registrations</td>



        </tr>

	{$data['users_by_month']}

</TD></TR></table>
</TD></TR></Table>
</TD></TR></Table>













<table width="70%" border="0" align='center' cellspacing="1" cellpadding="1"  style="border: 1px solid #eeeeee;">

  <tr> 

    <td class='maintitle' > 

      &nbsp; Users With Highest Posts Per Day Avg

    </td>

  </tr>

  <tr> 

    <td bgcolor=eeeeee cellspacing="1" cellpadding="1"> 

      <table width="100%" border="0" cellspacing="1" cellpadding="4">

        <tr> 

          <td nowrap  class='pformstrip' align="center" width="50%">Member Name</td>

            <td nowrap  class='pformstrip' align="center" width="30%">Posts Per Day</td>



        </tr>

	{$data['fastest_users']}

</TD></TR></table>
</TD></TR></Table>
</TD></TR></Table>












EOF;

	}


}

?>