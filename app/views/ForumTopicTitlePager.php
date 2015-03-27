<?php
/**
 * @file
 */

namespace Views;

use Ibf;

class ForumTopicTitlePager extends BaseView
{

    protected function beforeRender()
    {
        $pages = 1;
        if ($this->data['count']) {
            if ((($this->data['count'] + 1) % Ibf::app()->vars['display_max_posts']) == 0) {
                $pages = ($this->data['count'] + 1) / Ibf::app()->vars['display_max_posts'];
            } else {
                $number = (($this->data['count'] + 1) / Ibf::app()->vars['display_max_posts']);
                $pages  = ceil($number);
            }
        }

        if ($pages == 1) {
            return false;
        }

        if ($pages > 5) {
            //first two ... last two
            $map = [
                1          => 0,
                2          => Ibf::app()->vars['display_max_posts'],
                3          => 'hellip',
                $pages - 1 => Ibf::app()->vars['display_max_posts'] * ($pages - 2),
                $pages     => Ibf::app()->vars['display_max_posts'] * ($pages - 1),
            ];
        } else {
            $map = [];
            for ($i = 0; $i < $pages; $i++) {
                $map[$i + 1] = Ibf::app()->vars['display_max_posts'] * $i;
            }
        }
        $this->data['map'] = $map;
        $this->data['show_all_btn']     = $this->data['count'] < Ibf::app()->vars['max_show_all_posts'];
        $this->data['topic_url_prefix'] = Ibf::app()->base_url . "showtopic={$this->data['tid']}";
        return true;
    }
}
