<?php

/*
    Simple diff library, using diff php implementation by nils
    Copyleft (C) 2009 BohwaZ - http://bohwaz.net/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; version 3
    of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

    http://www.gnu.org/licenses/gpl.html
*/

class simpleDiff
{
    const SAME = 0;
    const INS = 1;
    const DEL = -1;
    const CHANGED = 2;

    /**
     * Generates a normal diff (like GNU diff utility)
     *
     * @param string $old Old text to compare (could be an array of lines)
     * @param string $new New text to compare (could be an array of lines)
     * @param bool $return_as_array Returns the diff as an array
     */
    static public function diff($old, $new, $return_as_array = false)
    {
        /**
            Diff implemented in pure php, written from scratch.
            Copyright (C) 2003  Daniel Unterberger <diff.phpnet@holomind.de>
            Copyright (C) 2005  Nils Knappmeier next version

            This program is free software; you can redistribute it and/or
            modify it under the terms of the GNU General Public License
            as published by the Free Software Foundation; either version 2
            of the License, or (at your option) any later version.

            This program is distributed in the hope that it will be useful,
            but WITHOUT ANY WARRANTY; without even the implied warranty of
            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
            GNU General Public License for more details.

            You should have received a copy of the GNU General Public License
            along with this program; if not, write to the Free Software
            Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

            http://www.gnu.org/licenses/gpl.html

            About:
            I searched a function to compare arrays and the array_diff()
            was not specific enough. It ignores the order of the array-values.
            So I reimplemented the diff-function which is found on unix-systems
            but this you can use directly in your code and adopt for your needs.
            Simply adopt the formatline-function. with the third-parameter of arr_diff()
            you can hide matching lines. Hope someone has use for this.

            Contact: d.u.diff@holomind.de <daniel unterberger>
        **/

        # split the source text into arrays of lines
        if (is_array($old))
            $t1 = $old;
        else
            $t1 = explode("\n",$old);

        $x = array_pop($t1);
        if ($x>'') $t1[]="$x\n\\ No newline at end of file";

        if (is_array($new))
            $t2 = $new;
        else
            $t2 = explode("\n",$new);

        $x=array_pop($t2);
        if ($x>'') $t2[]="$x\n\\ No newline at end of file";

        # build a reverse-index array using the line as key and line number as value
        # don't store blank lines, so they won't be targets of the shortest distance
        # search
        foreach($t1 as $i=>$x)
        {
            if ($x>'') $r1[$x][]=$i;
        }
        foreach($t2 as $i=>$x) if ($x>'') $r2[$x][]=$i;

        $a1=0; $a2=0;   # start at beginning of each list
        $actions=array();

        # walk this loop until we reach the end of one of the lists
        while ($a1<count($t1) && $a2<count($t2)) {
            # if we have a common element, save it and go to the next
            if ($t1[$a1]==$t2[$a2]) { $actions[]=4; $a1++; $a2++; continue; }

            # otherwise, find the shortest move (Manhattan-distance) from the
            # current location
            $best1=count($t1); $best2=count($t2);
            $s1=$a1; $s2=$a2;
            while(($s1+$s2-$a1-$a2) < ($best1+$best2-$a1-$a2)) {
                $d=-1;
                if (isset($t2[$s2]) && isset($r1[$t2[$s2]]))
                {
                    foreach((array)@$r1[$t2[$s2]] as $n)
                    {
                        if ($n>=$s1) { $d=$n; break; }
                    }
                }
                if ($d>=$s1 && ($d+$s2-$a1-$a2)<($best1+$best2-$a1-$a2))
                { $best1=$d; $best2=$s2; }
                $d=-1;
                if (isset($t1[$s1]) && isset($r2[$t1[$s1]]))
                {
                    foreach((array)@$r2[$t1[$s1]] as $n)
                    {
                        if ($n>=$s2) { $d=$n; break; }
                    }
                }
                if ($d>=$s2 && ($s1+$d-$a1-$a2)<($best1+$best2-$a1-$a2))
                { $best1=$s1; $best2=$d; }
                $s1++; $s2++;
            }
            while ($a1<$best1) { $actions[]=1; $a1++; }  # deleted elements
            while ($a2<$best2) { $actions[]=2; $a2++; }  # added elements
        }

        # we've reached the end of one list, now walk to the end of the other
        while($a1<count($t1)) { $actions[]=1; $a1++; }  # deleted elements
        while($a2<count($t2)) { $actions[]=2; $a2++; }  # added elements

        # and this marks our ending point
        $actions[]=8;

        # now, let's follow the path we just took and report the added/deleted
        # elements into $out.
        $op = 0;
        $x0=$x1=0; $y0=$y1=0;
        $out = array();
        foreach($actions as $act) {
            if ($act==1) { $op|=$act; $x1++; continue; }
            if ($act==2) { $op|=$act; $y1++; continue; }
            if ($op>0) {
                $xstr = ($x1==($x0+1)) ? $x1 : ($x0+1).",$x1";
                $ystr = ($y1==($y0+1)) ? $y1 : ($y0+1).",$y1";
                if ($op==1) $out[] = "{$xstr}d{$y1}";
                elseif ($op==3) $out[] = "{$xstr}c{$ystr}";
                while ($x0<$x1) { $out[] = '< '.$t1[$x0]; $x0++; }   # deleted elems
                if ($op==2) $out[] = "{$x1}a{$ystr}";
                elseif ($op==3) $out[] = '---';
                while ($y0<$y1) { $out[] = '> '.$t2[$y0]; $y0++; }   # added elems
            }
            $x1++; $x0=$x1;
            $y1++; $y0=$y1;
            $op=0;
        }
        $out[] = '';

        if ($return_as_array)
            return $out;
        else
            return implode("\n",$out);
    }

    /**
     * Applies a diff to a text
     *
     * @param string $original Original text to patch
     * @param string $patch Diff text
     * @param bool $return_as_array Returns the patched text as an array
     */
    static public function patch($original, $patch, $return_as_array = false)
    {
        $new = array();

        if (!is_array($patch))
            $patch = explode("\n", $patch);

        if (!is_array($original))
            $original = explode("\n", str_replace("\r", "", $original));

        $i = 0;
        foreach ($patch as $line)
        {
            if (empty($line))
                continue;

            $line = str_replace("\n\\ No newline at end of file", "", $line);

            if ($line[0] == '>')
            {
                $new[] = substr($line, 2);
            }
            elseif (preg_match('!^(?P<ob>[0-9]+)(?:,(?P<oe>[0-9]+))?(?P<mode>[acd])(?P<nb>[0-9]+)(?:,(?<ne>[0-9]+))?$!', trim($line), $match))
            {
                $sub = ($match['mode'] == 'a') ? 0 : 1;
                for ($a = $i; $a < ($match['ob'] - $sub); $a++)
                {
                    $new[] = $original[$a];
                }
                $i = $match['oe'] ? (int) $match['oe'] : (int) $match['ob'];
            }
        }
        for ($a = $i; $a < count($original); $a++)
        {
            $new[] = $original[$a];
        }

        return $return_as_array ? $new : implode("\n", $new);
    }

    /**
     * Returns an array showing differences between two arrays
     *
     * @param string $diff Diff text, set to false and the diff will be made from $old and $new
     * @param string $old Old text
     * @param string $new New text, could be set to false if the diff is supplied
     * @param bool $show_context Include context in the array? Set to false to avoid context,
        set to true to have all the context and set to an (int) to have this number of lines of
        context before and after each modified line
     */
    static public function diff_to_array($diff = false, $old, $new = false, $show_context = true)
    {
        if ($diff === false && $new === false)
        {
            throw new Exception("diff_to_array needs either the diff text or the new text file");
        }

        if ($diff === false)
        {
            $diff = self::diff($old, $new, true);
        }

        if (!is_array($diff))
            $old = explode("\n", $diff);

        if (!is_array($old))
            $old = explode("\n", $old);

        if ($new === false)
            $new = self::patch($old, $diff, true);

        if (!is_array($new))
            $new = explode("\n", $new);

        $left = $right = $context = array();
        $max_lines = max(count($new), count($old));

        // Creating an array of changed lines for left and right texts
        foreach ($diff as $line)
        {
            if (preg_match('!^(?P<ob>[0-9]+)(?:,(?P<oe>[0-9]+))?(?P<mode>[acd])(?P<nb>[0-9]+)(?:,(?<ne>[0-9]+))?$!', trim($line), $match))
            {
                if (empty($match['oe']))
                    $match['oe'] = $match['ob'];

                if (empty($match['ne']))
                    $match['ne'] = $match['nb'];

                if ($match['mode'] == 'a')
                {
                    for ($i = $match['nb']; $i <= $match['ne']; $i++)
                    {
                        $right[$i - 1] = true;
                        $max_lines++;
                    }
                }
                elseif ($match['mode'] == 'd')
                {
                    for ($i = $match['ob']; $i <= $match['oe']; $i++)
                    {
                        $left[$i - 1] = true;
                        $max_lines++;
                    }
                }
                else
                {
                    for ($i = $match['nb']; $i <= $match['ne']; $i++)
                    {
                        $right[$i - 1] = true;
                    }
                    for ($i = $match['ob']; $i <= $match['oe']; $i++)
                    {
                        $left[$i - 1] = true;
                    }
                }

                if ($show_context && $show_context !== true)
                {
                    $min = $match['ob'] - (int) $show_context;
                    if ($min < 1) $min = 1;
                    $max = $match['oe'] + (int) $show_context;
                    if ($max > count($new)) $max = count($new);

                    for ($i = $min; $i <= $max; $i++)
                    {
                        $context[$i - 1] = true;
                    }
                }
            }
        }

        $out = array();

        $left_index = 0;
        $right_index = 0;
        $i = 0;

        // Then we can compile this to an array of changed things
        while ($i < $max_lines)
        {
            $row = array();

            // Line present in left but not in right ? deleted
            if (isset($left[$left_index]) && !isset($right[$right_index]))
            {
                $row = array(self::DEL, $old[$left_index], '');
                $left_index++;
            }
            // Line present in right but not in left ? added
            elseif (isset($right[$right_index]) && !isset($left[$left_index]))
            {
                $row = array(self::INS, '', $new[$right_index]);
                $right_index++;
            }
            else
            {
                // Changed line
                if (isset($left[$left_index]) && isset($right[$right_index]))
                {
                    $row = array(self::CHANGED, $old[$left_index], $new[$right_index]);
                }
                // Or nothing happened
                else
                {
                    // We want all the context, ok
                    if ($show_context === true || isset($context[$left_index]))
                    {
                        $l = isset($old[$left_index]) ? $old[$left_index] : '';
                        $r = isset($new[$right_index]) ? $new[$right_index] : '';
                        $row = array(self::SAME, $l, $r);
                    }
                    else
                    {
                        $max--;
                    }
                }

                $right_index++;
                $left_index++;
            }

            $i++;

            if (!empty($row))
            {
                $out[($i - 1)] = $row;
            }
        }

        return $out;
    }

    /**
     * Generates a word-diff, like the GNU wdiff utility (kind of)
     *
     * @param string $old Left right to compare
     * @param string $new Right line to compare
     * @param string $union Union string to assemble words (default is whitespace)
     */
    static public function wdiff($old, $new, $union = ' ')
    {
        $diff = self::diff_to_array(false, explode(' ', $old), explode(' ', $new));
        $out = '';

        foreach ($diff as $line)
        {
            list ($change, $old, $new) = $line;

            if ($change == self::CHANGED)
            {
                $out .= '[-' . $old . '-]';
                $out .= $union;
                $out .= '{+' . $new . '+}';
            }
            elseif ($change == self::DEL)
            {
                $out .= '[-' . $old . '-]';
            }
            elseif ($change == self::INS)
            {
                $out .= '{+' . $new . '+}';
            }
            else
            {
                $out .= $old;
            }

            $out .= $union;
        }

        $out = str_replace('+}' . $union . '{+', ' ', $out);
        $out = str_replace('-]' . $union . '[-', ' ', $out);

        return $out;
    }
}

?>