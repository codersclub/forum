<? 

require "sources/ForumList.php";

class IPBForumList extends ForumList {

	function get_forums()
	{
	global $DB, $std;
		$cats = array();
		$forums = array();
		$children = array();
		
		$DB->query("SELECT id,state,name FROM ibf_categories ORDER BY position");
 
		while ( $c = $DB->fetch_row() ) 
		{
			if ( $c['state'] != 1 ) continue;

			$cats[ $c['id'] ] = $c;
		}
  
		$DB->query("SELECT f.id, f.name, f.parent_id, f.password, f.read_perms, f.sub_can_post as write_perms, f.category
			FROM ibf_forums f
			LEFT JOIN ibf_moderators m ON (f.id=m.forum_id)
			ORDER BY position");
 
		while ( $r = $DB->fetch_row() )
		{
			if ( $std->check_perms($r['read_perms']) != TRUE )
				continue;

			if ( $r['parent_id'] > 0 )
			{
				$children[ $r['parent_id'] ][] = $r;
			}
			else
			{
				$forums[ $r['category'] ][] = $r;
			}
		}

		$temp = array();
		foreach ( $cats as $c )
		{
			$last_cat_id = $c['id'];
  			$param = $forums[$c['id']];
			if(is_array($param))
			{
				foreach( $param as $r )
				{ 
					$temp[] = $r;
					if(is_array($children[$r['id']]))
					{
						foreach( $children[$r['id']] as $ch)
						{
							$temp[] = $ch;
						}
					}
				} 
			}
		}
		$forums = $temp;
		return $forums;
	}

	function IPBForumList() {

	global $DB, $std;

		// Fetch names of parent forums
		$query = "SELECT id, name, sub_can_post as write_perms FROM ibf_forums
				WHERE parent_id=-1";

		$DB->query( $query );

		while( $row = $DB->fetch_row() ) {

			$parents[ $row['id'] ] = $row;
		}

		// Fetch all forums data
//		$query = "SELECT f.id, f.name, f.parent_id, f.password, f.read_perms, f.sub_can_post as write_perms
//				FROM ibf_forums f
//				LEFT JOIN ibf_moderators m ON (f.id=m.forum_id)
//				ORDER BY f.parent_id";
//
//		$DB->query( $query );
		$f = $this->get_forums();

		foreach( $f as $row) {

			if(0 == $row['write_perms']) {

				continue;
			}

			if(!empty($row['password'])) {

				continue;
			}

			if( $std->check_perms($row['read_perms']) != TRUE ) {

				continue;
			}

			$this->forums[$row['id']] = $row['name'];
			if(-1 != $row['parent_id'])
			{
				$this->forums[$row['id']] = $parents[$row['parent_id']]['name'] . " (" . $this->forums[$row['id']] . ")";
			}
		}
	}
}

?>
