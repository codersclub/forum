<?

class table_linker {

var $name = NULL;
var $base = NULL;
var $refs = NULL;

        function table_linker($name, $base_table, $ref_table) {
                $this->name = $name;
                $this->base = $base_table;
                $this->refs = $ref_table;
        }

        function get_refs_array($base_id) {
        global $DB;

                $query = "
                        SELECT
                                r.*
                        FROM
                                {$this->name} t,
                                {$this->refs} r
                        WHERE
                                t.base={$base_id} AND
                                r.id=t.refs
                ";


                $DB->query($query);

                $result = array();

                while($row = $DB->fetch_row()) {

                        $result[] = $row;
                }

                return $result;
        }

        function get_basis_array($ref_id) {
        global $DB;

                $query = "
                        SELECT
                                b.*, m.name as member_name
                        FROM
                                {$this->name} as t,
                                {$this->base} as b
                        LEFT JOIN ibf_members as m

                        ON m.id=b.user_id

                        WHERE
                                t.refs={$ref_id} AND
                                b.id=t.base
                ";

                $DB->query($query);

                $result = array();

                while($row = $DB->fetch_row()) {

                        $result[] = $row;
                }

                return $result;
        }

        function add_link($id, $ref) {
        global $DB;

                if($this->link_exist($id, $ref)) {

                        return true;
                }

                $query = "
                        INSERT INTO
                                {$this->name}
                        (base, refs)
                        VALUES (
                                {$id},
                                {$ref}
                        )
                ";

                return $DB->query($query);
        }

        function remove_link($id, $ref) {
        global $DB;

                if(!$this->link_exist($id, $ref)) {

                        return false;
                }

                $query = "
                        DELETE FROM
                                {$this->name}
                        WHERE
                                base={$id} AND
                                refs={$ref}
                ";

                return $DB->query($query);
        }

        function remove_all_refs_for_basis($id) {
        global $DB;

                $query = "
                        DELETE FROM
                                {$this->name}
                        WHERE
                                base={$id}
                ";

                return $DB->query($query);
        }

        function remove_all_refs($ref) {
        global $DB;

                $query = "
                        DELETE FROM
                                {$this->name}
                        WHERE
                                refs={$ref}
                ";

                return $DB->query($query);
        }

        function link_exist($id, $ref) {
        global $DB;

                $query = "
                        SELECT
                                *
                        FROM
                                {$this->name}
                        WHERE
                                base={$id} AND
                                refs={$ref}
                ";

                $DB->query($query);

                return $DB->fetch_row() ? true : false;
        }
}

?>