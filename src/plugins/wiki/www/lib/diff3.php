<?php
/**
 * Copyright © 2001 Geoffrey T. Dairiki <dairiki@dairiki.org>
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

// diff3.php
//
// A class for computing three way diffs

require_once 'lib/difflib.php';

class Diff3_Block
{
    public $type = 'diff3';

    function __construct($orig = false, $final1 = false, $final2 = false)
    {
        $this->orig = $orig ? $orig : array();
        $this->final1 = $final1 ? $final1 : array();
        $this->final2 = $final2 ? $final2 : array();
    }

    public function merged()
    {
        if (!isset($this->merged)) {
            if ($this->final1 === $this->final2)
                $this->merged = &$this->final1;
            elseif ($this->final1 === $this->orig)
                $this->merged = &$this->final2;
            elseif ($this->final2 === $this->orig)
                $this->merged = &$this->final1;
            else
                $this->merged = false;
        }
        return $this->merged;
    }

    public function is_conflict()
    {
        return $this->merged() === false;
    }
}

class Diff3_CopyBlock extends Diff3_Block
{
    public $type = 'copy';

    function __construct($lines = array())
    {
        $this->orig = $lines ? $lines : array();
        $this->final1 = &$this->orig;
        $this->final2 = &$this->orig;
    }

    public function merged()
    {
        return $this->orig;
    }

    public function is_conflict()
    {
        return false;
    }
}

class Diff3_BlockBuilder
{
    public $orig;
    public $final1;
    public $final2;

    function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->orig = array();
        $this->final1 = array();
        $this->final2 = array();
    }

    private function append(&$array, $lines)
    {
        array_splice($array, sizeof($array), 0, $lines);
    }

    public function input($lines)
    {
        if ($lines)
            $this->append($this->orig, $lines);
    }

    public function out1($lines)
    {
        if ($lines)
            $this->append($this->final1, $lines);
    }

    public function out2($lines)
    {
        if ($lines)
            $this->append($this->final2, $lines);
    }

    private function is_empty()
    {
        return !$this->orig && !$this->final1 && !$this->final2;
    }

    public function finish()
    {
        if ($this->is_empty())
            return false;
        else {
            $block = new Diff3_Block($this->orig, $this->final1, $this->final2);
            $this->init();
            return $block;
        }
    }
}

class Diff3
{
    function __construct($orig, $final1, $final2)
    {
        $eng = new DiffEngine();
        $this->ConflictingBlocks = 0; //Conflict counter
        $this->blocks = $this->diff3blocks($eng->diff($orig, $final1),
            $eng->diff($orig, $final2));
    }

    private function diff3blocks($edits1, $edits2)
    {
        $blocks = array();
        $bb = new Diff3_BlockBuilder();

        $e1 = current($edits1);
        $e2 = current($edits2);
        while ($e1 || $e2) {
            if ($e1 && $e2 && $e1->type == 'copy' && $e2->type == 'copy') {
                // We have copy blocks from both diffs.  This is the (only)
                // time we want to emit a diff3 copy block.
                // Flush current diff3 diff block, if any
                if ($block = $bb->finish())
                    $blocks[] = $block;

                $ncopy = min($e1->norig(), $e2->norig());
                assert($ncopy > 0);
                $blocks[] = new Diff3_CopyBlock(array_slice($e1->orig, 0, $ncopy));

                if ($e1->norig() > $ncopy) {
                    array_splice($e1->orig, 0, $ncopy);
                    array_splice($e1->final, 0, $ncopy);
                } else
                    $e1 = next($edits1);

                if ($e2->norig() > $ncopy) {
                    array_splice($e2->orig, 0, $ncopy);
                    array_splice($e2->final, 0, $ncopy);
                } else
                    $e2 = next($edits2);
            } else {
                if ($e1 && $e2) {
                    if ($e1->orig && $e2->orig) {
                        $norig = min($e1->norig(), $e2->norig());
                        $orig = array_splice($e1->orig, 0, $norig);
                        array_splice($e2->orig, 0, $norig);
                        $bb->input($orig);
                    }

                    if ($e1->type == 'copy')
                        $bb->out1(array_splice($e1->final, 0, $norig));

                    if ($e2->type == 'copy')
                        $bb->out2(array_splice($e2->final, 0, $norig));
                }
                if ($e1 && !$e1->orig) {
                    $bb->out1($e1->final);
                    $e1 = next($edits1);
                }
                if ($e2 && !$e2->orig) {
                    $bb->out2($e2->final);
                    $e2 = next($edits2);
                }
            }
        }

        if ($block = $bb->finish())
            $blocks[] = $block;

        return $blocks;
    }

    public function merged_output($label1 = '', $label2 = '')
    {
        $lines = array();
        foreach ($this->blocks as $block) {
            if ($block->is_conflict()) {
                // FIXME: this should probably be moved somewhere else...
                $lines = array_merge($lines,
                    array("<<<<<<<" . ($label1 ? " $label1" : '')),
                    $block->final1,
                    array("======="),
                    $block->final2,
                    array(">>>>>>>" . ($label2 ? " $label2" : '')));
                $this->ConflictingBlocks++;
            } else {
                $lines = array_merge($lines, $block->merged());
            }
        }
        return $lines;
    }
}
