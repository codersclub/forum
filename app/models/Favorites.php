<?php
/**
 * @class Favorites Favorites model
 */
class Favorites
{
    const DEFAULT_MAX_LIMIT = 2000;
    /**
     * @var int max limit of favorite topics
     */
    protected $max_limit = self::DEFAULT_MAX_LIMIT;
    /**
     * @var Member Favorites owner
     */
    protected $owner;

    function __construct($owner)
    {
        $this->owner = &$owner;
    }

    /**
     * Wrapper for add with checking possibility of adding and raising error if any
     * @param $topic_id int
     */
    public function addTopic($topic_id)
    {
        settype($topic_id, 'int');
        if ($this->canTopicBeAdded($topic_id)) {
            $this->add($topic_id);
        } else {
            Ibf::app()->functions->Error(['LEVEL' => 1, 'MSG' => 'too_many_favs', 'EXTRA' => $this->max_limit]);
        }
    }

    /**
     * Simply adds topic to favorites
     * @param $topic_id int Topic id
     */
    protected function add($topic_id)
    {
        $mid = (int)Ibf::app()->member['id'];
        Ibf::app()->db
            ->prepare("INSERT INTO ibf_favorites (mid,tid) VALUES (:mid, :tid)")
            ->bindParam(':mid', $mid, PDO::PARAM_INT)
            ->bindParam(':tid', $topic_id, PDO::PARAM_INT)
            ->execute();
    }

    /**
     * Wrapper for delete(). It's opposite to addTopic and it's the only reason of it's existence
     * @param $topic_id int Topic id
     */
    public function deleteTopic($topic_id)
    {
        settype($topic_id, 'int');
        $this->delete($topic_id);
    }

    /**
     * Simply deletes the topic from favorites
     * @param $topic_id int Topic id
     */
    protected function delete($topic_id)
    {
        $mid = (int)Ibf::app()->member['id'];
        Ibf::app()->db
            ->prepare("DELETE FROM ibf_favorites WHERE mid= :mid AND tid = :tid")
            ->bindParam(':mid', $mid, PDO::PARAM_INT)
            ->bindParam(':tid', $topic_id, PDO::PARAM_INT)
            ->execute();
    }

    /**
     * Checks, whether can topic be added or not
     * @param $topic_id int topic_id is not used for now
     * @return bool
     */
    public function canTopicBeAdded($topic_id)
    {
        //topics count is less than max limit
        return $this->max_limit > Ibf::app()->db
            ->prepare('SELECT COUNT(*) FROM ibf_favorites WHERE mid=:mid')
            ->bindParam(':mid', $this->owner['id'], PDO::PARAM_INT)
            ->execute()
            ->fetchColumn();
    }

    /**
     * Delete topics from all memebers favorites
     * @param $topic_ids array topic's ids
     */
    public static function purgeTopics($topic_ids)
    {
        if (!empty($topic_ids)) {
            Ibf::app()->db
                ->prepare("DELETE FROM ibf_favorites WHERE tid IN(" . IBPDO::placeholders($topic_ids) . ")")
                ->execute($topic_ids);
        }
    }

    /**
     * Setter for max limit
     * @param int $value
     */
    public function setMaxLimit($value)
    {
        $this->max_limit = (int)$value;
    }

    /**
     * getter for max limit
     * @return int
     */
    public function getMaxLimit()
    {
        return $this->max_limit;
    }

    /**
     * Retrieves complete info for all favorites of the owner
     * @return array
     */
    public function findAll()
    {
        $query = "
			SELECT f.tid, t.tid AS topic_id, t.title, t.starter_id, t.last_poster_id, t.last_post,
			   t.starter_name, t.last_poster_name, tr.logTime
            FROM ibf_favorites f
            LEFT JOIN ibf_topics t ON f.tid=t.tid
            LEFT JOIN ibf_log_topics tr ON (f.mid=tr.mid AND f.tid=tr.tid)
		    WHERE
			  f.mid=:mid
		    GROUP BY f.tid
            ORDER BY t.last_post DESC";
        return Ibf::app()->db->prepare($query)
            ->bindParam(':mid', $this->owner['id'], PDO::PARAM_INT)
            ->execute()
            ->fetchAll();
    }

    /**
     * Retrieves favorite topic ids of the owner
     * @return array
     */
    public function getTopicIds()
    {
        // Get Favs from ibf_favorites
        $stmt = Ibf::app()->db
            ->prepare("SELECT tid FROM ibf_favorites WHERE mid=:mid")
            ->bindParam(':mid', $this->owner['id'])
            ->execute();
        //something like fetchAllColumn
        $f = [];
        foreach ($stmt as $row) {
            $f[] = (int)$row['tid'];
        }
        return $f;
    }
}
